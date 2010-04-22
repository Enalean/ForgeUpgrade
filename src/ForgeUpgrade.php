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

require 'ForgeUpgradeBucketDb.php';
require 'ForgeUpgradeBucketFilter.php';
require 'ForgeUpgradeDb.php';

/**
 * Centralize upgrade of the Forge
 */
class ForgeUpgrade {
    /**
     * @var ForgeUpgradeDb
     */
    protected $db;

    /**
     * @var ForgeUpgradeBucketDb
     */
    protected $bucketDb;

    /**
     * @var Logger
     */
    protected $log;

    protected $buckets = null;


    /**
     * Constructor
     */
    public function __construct(PDO $dbh) {
        $this->db       = new ForgeUpgradeDb($dbh);
        $this->bucketDb = new ForgeUpgradeBucketDb($dbh);
        $this->log      = $this->getLogger('ForgeUpgrade');
    }

    /**
     * Run all available migrations
     */
    public function run($func) {
        $buckets = $this->getMigrationBuckets('migrations');
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
            $this->log->info('System up-to-date');
        }
    }

    protected function doRecordOnly($buckets) {
        foreach ($buckets as $bucket) {
            $this->log->info("[doRecordOnly] ".get_class($bucket));
            $this->db->logUpgrade($bucket, ForgeUpgradeDb::STATUS_SKIP);
        }
    }
    
    protected function doUpdate($buckets) {
        if ($this->runPreUp($buckets)) {
            $this->runUp($buckets);
        }
    }

    protected function doCheckUpdate($buckets) {
        foreach ($buckets as $bucket) {
            echo get_class($bucket).' ('.$bucket->getPath().')'.PHP_EOL;
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
        $this->log->info("[Pre Up] Run pre up checks");
        $result = true;
        foreach ($buckets as $bucket) {
            try {
                if (!$bucket->dependsOn()) {
                    $bucket->preUp();
                    $this->log->info("[Pre Up] OK : ".get_class($bucket));
                } else {
                    $this->log->info("[Pre Up] SKIP: ".get_class($bucket)." depends on a migration not already applied");
                }
            } catch (Exception $e) {
                $this->log->error("[Pre Up] ERROR : ".get_class($bucket));
                $result = false;
            }
        }
        if ($result) {
            $this->log->info("[Pre Up] Global: OK");
        } else {
            $this->log->error("[Pre Up] Global: FAILD");
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
            $this->log->info('[Up] Start running migrations...');
            foreach ($buckets as $bucket) {
                $className = get_class($bucket);

                $this->log->info("[Up] $className");
                echo $bucket->description();

                $bucket->preUp();
                $this->log->info("[Up] $className PreUp OK");

                $bucket->up();
                $this->db->logUpgrade($bucket, ForgeUpgradeDb::STATUS_SUCCESS);
                $this->log->info("[Up] $className Up OK");

                $bucket->postUp();
                $this->log->info("[Up] $className Done");
            }
        } catch (Exception $e) {
            $this->log->error("[Up] ".$e->getMessage());
            $this->logUpgrade($bucket, ForgeUpgradeDb::STATUS_FAILURE);
        }
    }

    protected function getMigrationBuckets($dirPath) {
        $buckets = $this->getAllMigrationBuckets($dirPath);
        $sth = $this->db->getAllBuckets();
        foreach($sth as $row) {
            $key = basename($row['script']);
            if (isset($buckets[$key])) {
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
    protected function getAllMigrationBuckets($dirPath) {
        if (!isset($this->buckets)) {
            $dir    = new RecursiveDirectoryIterator($dirPath);
            $iter   = new RecursiveIteratorIterator($dir);
            $files  = new UpgradeBucketFilter($iter);
            $this->buckets = array();
            foreach ($files as $file) {
                $object = $this->getMigrationClass($file);
                if ($object) {
                    $this->buckets[basename($file->getPathname())] = $object;
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
            $bucket = new $class($this->bucketDb);
            $bucket->setPath($scriptPath->getPathname());
        }
        return $bucket;
    }

    /**
     * Deduce the class name from the script name
     *
     * migrations/201004081445_add_tables_for_docman_watermarking.php -> AddTablesForDocmanWatermarking
     *
     * @param String $scriptPath Path to the script to execute
     *
     * @return String
     */
    protected function getClassName($scriptPath) {
        if(preg_match('%^[0-9]+_(.*)\.php$%', basename($scriptPath), $matches)) {
            $words    = explode('_', $matches[1]);
            $capWords = array_map('ucfirst', $words);
            return implode('', $capWords);
        }
        return '';
    }

    /**
     * Wrapper for Logger
     *
     * @return Logger
     */
    protected function getLogger() {
        return Logger::getLogger('ForgeUpgrade');
    }
}


?>
