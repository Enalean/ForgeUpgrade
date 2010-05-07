<?php
/**
 * Copyright (c) STMicroelectronics, 2010. All Rights Reserved.
 *
 * Originally written by Manuel Vacelet
 *
 * ForgeUpgrade is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ForgeUpgrade is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ForgeUpgrade. If not, see <http://www.gnu.org/licenses/>.
 */

require 'bucket/Bucket.php';
require 'BucketFilter.php';
require 'db/Db.php';

/**
 * Centralize upgrade of the Forge
 */
class ForgeUpgrade {
    /**
     * @var ForgeUpgrade_Db_Driver_Abstract
     */
    protected $dbDriver;

    /**
     * @var ForgeUpgradeDb
     */
    protected $db;

    /**
     * @var ForgeUpgrade_BucketDb
     */
    protected $bucketDb;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var array
     */
    protected $buckets = null;

    protected $includePaths = array();
    protected $excludePaths = array();

    /**
     * Constructor
     */
    public function __construct(ForgeUpgrade_Db_Driver_Abstract $dbDriver) {
        $this->dbDriver = $dbDriver;
        $this->db       = new ForgeUpgrade_Db($dbDriver->getPdo());
        $this->bucketDb = new ForgeUpgrade_Bucket_Db($dbDriver->getPdo());
    }

    function setIncludePaths($paths) {
        $this->includePaths = $paths;
    }

    function setExcludePaths($paths) {
        $this->excludePaths = $paths;
    }
    
    /**
     * Run all available migrations
     */
    public function run($func, $paths) {
        // Commands without path
        switch ($func) {
            case 'already-applied':
                $this->doAlreadyApplied();
                return;
        }
        
        // Commands that rely on path
        if (count($paths) == 0) {
            $this->log()->info('No migration path');
            return false;
        }
        $buckets = $this->getMigrationBuckets($paths[0]);
        if (count($buckets) > 0) {
            switch ($func) {
                case 'record-only':
                    $this->doRecordOnly($buckets);
                    break;

                case 'update':
                    $this->doUpdate($buckets);
                    break;

                case 'check-update':
                    $this->doCheckUpdate($buckets);
                    break;

                case 'run-pre':
                    $this->runPreUp($buckets);
                    break;
            }
        } else {
            $this->log()->info('System up-to-date');
        }
    }

    protected function doAlreadyApplied() {
        foreach ($this->db->getAllBuckets() as $row) {
            echo $row['start_date']."  ".ucfirst($this->db->statusLabel($row['status']))."  ".$row['script'].PHP_EOL;
        }
    }
    
    protected function doRecordOnly($buckets) {
        foreach ($buckets as $bucket) {
            $this->log()->info("[doRecordOnly] ".get_class($bucket));
            $this->db->logStart($bucket);
            $this->db->logEnd($bucket, ForgeUpgrade_Db::STATUS_SKIP);
        }
    }
    
    protected function doUpdate($buckets) {
        if ($this->runPreUp($buckets)) {
            $this->runUp($buckets);
        }
    }

    protected function doCheckUpdate($buckets) {
        foreach ($buckets as $bucket) {
            echo $bucket->getPath().PHP_EOL;
            $lines = explode("\n", $bucket->description());
            foreach ($lines as $line) {
                echo "\t$line\n";
            }
        }
        echo count($buckets)." migrations pending\n";
    }

    /**
     * Run all preUp methods
     *
     * Run all possible preUp, if a dependency is defined between 2 scripts,
     * preUp of the script that depends on another is skipped.
     *
     * @todo: Add info on the number of buckets Success, Faild, Skipped
     */
    public function runPreUp($buckets) {
        $this->log()->info("Process all pre up checks");
        $result = true;
        foreach ($buckets as $bucket) {
            $className = get_class($bucket);
            try {
                if (!$bucket->dependsOn()) {
                    $bucket->preUp();
                    $this->log()->info("OK: $className");
                } else {
                    $this->log()->info("SKIP: ".$className." (depends on a migration not already applied)");
                }
            } catch (Exception $e) {
                $this->log()->error($className.': '.$e->getMessage());
                $result = false;
            }
        }
        if ($result) {
            $this->log()->info("PreUp checks OK");
        } else {
            $this->log()->error("PreUp checks FAILD");
        }

        return $result;
    }

    /**
     * Load all migrations and execute them
     *
     * @param String $scriptPath Path to the script to execute
     *
     * @return void
     */
    protected function runUp($buckets) {
        try {
            $log = $this->log();
            $log->info('Start running migrations...');
            foreach ($buckets as $bucket) {
                $this->db->logStart($bucket);

                // Prepare a specific logger that will be used to store all
                // Bucket traces into the database so the buckets and it's logs
                // will be linked
                $log = Logger::getLogger(get_class());
                $log->addAppender($this->dbDriver->getBucketLoggerAppender($bucket));
                $bucket->setLoggerParent($log);

                $log->info("Processing ".get_class($bucket));

                $bucket->preUp();
                $log->info("PreUp OK");

                $bucket->up();
                $log->info("Up OK");

                $bucket->postUp();
                $log->info("PostUp OK");

                $this->db->logEnd($bucket, ForgeUpgrade_Db::STATUS_SUCCESS);
            }
        } catch (Exception $e) {
            // Use the last defined $log (so error messages are attached to the
            // right bucket in DB)
            $log->error($e->getMessage());
            $this->db->logEnd($bucket, ForgeUpgrade_Db::STATUS_FAILURE);
        }
    }

    protected function getMigrationBuckets($dirPath) {
        $this->log()->debug("Look for buckets in $dirPath");
        $buckets = $this->getAllMigrationBuckets($dirPath);
        $sth = $this->db->getAllBuckets(array(ForgeUpgrade_Db::STATUS_SUCCESS, ForgeUpgrade_Db::STATUS_SKIP));
        foreach($sth as $row) {
            $key = basename($row['script']);
            if (isset($buckets[$key])) {
                $this->log()->debug("Remove (already applied): $key");
                unset($buckets[$key]);
            }
        }
        return $buckets;
    }

    /**
     * Find all migration files and sort them in time order
     *
     * @return Array of SplFileInfo
     */
    public function getAllMigrationBuckets($dirPath) {
        if (!isset($this->buckets)) {
            $iter = new RecursiveDirectoryIterator($dirPath);
            $iter = new RecursiveIteratorIterator($iter, RecursiveIteratorIterator::SELF_FIRST);
            $iter = new ForgeUpgrade_BucketFilter($iter);
            $iter->setIncludePaths($this->includePaths);
            $iter->setExcludePaths($this->excludePaths);
            
            $this->buckets = array();
            foreach ($iter as $file) {
                if ($file->isFile()) {
                    $object = $this->getMigrationClass($file);
                    if ($object) {
                        $this->log()->debug("Valid bucket: $file");
                        $this->buckets[basename($file->getPathname())] = $object;
                    } else {
                        $this->log()->debug("Invalid bucket: $file");
                    }
                }
            }
            ksort($this->buckets, SORT_STRING);
        }
        return $this->buckets;
    }

    protected function getMigrationClass(SplFileInfo $scriptPath) {
        $bucket = null;
        $class  = $this->getClassName($scriptPath->getPathname());
        if (!class_exists($class)) {
            include $scriptPath->getPathname();
        }
        if ($class != '' && class_exists($class)) {
            $bucket = new $class();
            $bucket->setPath($scriptPath->getPathname());
            $this->addBucketApi($bucket);
        }
        return $bucket;
    }

    protected function addBucketApi(ForgeUpgrade_Bucket $bucket) {
        $bucket->setApi($this->bucketDb);
    }

    /**
     * Deduce the class name from the script name
     *
     * migrations/201004081445_add_tables_for_docman_watermarking.php -> b201004081445_add_tables_for_docman_watermarking
     *
     * @param String $scriptPath Path to the script to execute
     *
     * @return String
     */
    protected function getClassName($scriptPath) {
        return 'b'.basename($scriptPath, '.php');
    }

    /**
     * Wrapper for Logger
     *
     * @return Logger
     */
    protected function log() {
        if (!$this->log) {
            $this->log = Logger::getLogger(get_class());
        }
        return $this->log;
    }
    
    public function setLogger(Logger $log) {
        $this->log = $log;
    }
}

?>
