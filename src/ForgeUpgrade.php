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

require 'ForgeUpgradeDb.php';
require 'ForgeUpgradeBucketFilter.php';

/**
 * Centralize upgrade of the Forge
 */
class ForgeUpgrade {
    /**
     * @var ForgeUpgradeDb
     */
    protected $db;

    protected $log;

    protected $buckets = null;

    /**
     * Constructor
     */
    public function __construct(PDO $dbh) {
        $this->db  = new ForgeUpgradeDb($dbh);
        $this->log = Logger::getLogger('ForgeUpgrade');
    }

    /**
     * Run all available migrations
     */
    public function run() {
        if ($this->runPreUp()) {
            $this->runUp();
        }
    }

    /**
     * Run all preUp methods
     *
     * Run all possible preUp, if a dependency is defined between 2 scripts,
     * preUp of the script that depends on another is skipped.
     *
     * @todo: Add info on the number of buckets Success, Faild, Skipped
     */
    public function runPreUp() {
        $this->log->info("[Pre Up] Run pre up checks");
        $result = true;
        foreach ($this->getMigrationBuckets('migrations') as $file) {
            try {
                $bucket = $this->getMigrationClass($file);
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
    protected function runUp() {
        try {
            $this->log->info('[Up] Start running migrations...');
            foreach ($this->getMigrationBuckets('migrations') as $file) {
                $bucket = $this->getMigrationClass($file);
                if ($bucket) {
                    $className = get_class($bucket);
                    $this->log->info("[Up] $className");
                    echo $bucket->description();
                    $bucket->preUp();
                    $this->log->info("[Up] $className PreUp OK");
                    $bucket->up();
                    $this->log->info("[Up] $className Up OK");
                    $bucket->postUp();
                    $this->log->info("[Up] $className Done");
                }
            }
        } catch (Exception $e) {
            $this->log->error("[Up] ".$e->getMessage());
        }
    }

    /**
     * Find all migration files and sort them in time order
     *
     * @return Array of SplFileInfo
     */
    protected function getMigrationBuckets($dirPath) {
        if (!isset($this->buckets)) {
            $dir    = new RecursiveDirectoryIterator($dirPath);
            $iter   = new RecursiveIteratorIterator($dir);
            $files  = new UpgradeBucketFilter($iter);
            $this->buckets = array();
            foreach ($files as $file) {
                $this->buckets[basename($file->getPathname())] = $file;
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
            $bucket = new $class($this->db);
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
}


?>
