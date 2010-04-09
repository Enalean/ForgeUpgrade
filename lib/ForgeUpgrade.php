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

    protected $buckets = null;

    /**
     * Constructor
     */
    public function __construct(PDO $dbh) {
        $this->db = new ForgeUpgradeDb($dbh);
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
        echo "[Pre Up] Run pre up checks".PHP_EOL;
        $result = true;
        foreach ($this->getMigrationBuckets('migrations') as $file) {
            $bucket = $this->getMigrationClass($file);
            if (!$bucket->dependsOn()) {
                $br     = $bucket->preUp();
                $result = $result & $br;
                $strRes = $result ? 'OK' : 'FAILD';
                echo "[Pre Up] ".$strRes.': '.get_class($bucket).PHP_EOL;
            } else {
                echo "[Pre Up] SKIP: ".get_class($bucket)." depends on a migration not already applied".PHP_EOL;
            }
        }
        $strRes = $result ? 'OK' : 'FAILD';
        echo "[Pre Up] Global: ".$strRes.PHP_EOL;
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
        echo '[Up] Start running migrations...'.PHP_EOL;
        foreach ($this->getMigrationBuckets('migrations') as $file) {
            $bucket = $this->getMigrationClass($file);
            if ($bucket) {
                $className = get_class($bucket);
                echo "[Up] $className".PHP_EOL;
                echo $bucket->description();
                if($bucket->preUp()) {
                    echo "[Up] $className PreUp OK".PHP_EOL;
                    $bucket->up();
                    echo "[Up] $className Done".PHP_EOL;
                    $bucket->postUp();
                }
                var_dump($bucket->getLogs());
            }
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
