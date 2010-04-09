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
        foreach ($this->getMigrationBuckets('migrations') as $file) {
            $this->runMigration($file);
        }
    }

    /**
     * Find all migration files and sort them in time order
     *
     * @return Array of SplFileInfo
     */
    protected function getMigrationBuckets($dirPath) {
        $dir    = new RecursiveDirectoryIterator($dirPath);
        $iter   = new RecursiveIteratorIterator($dir);
        $files  = new UpgradeBucketFilter($iter);
        $toSort = array();
        foreach ($files as $file) {
            $toSort[basename($file->getPathname())] = $file;
        }
        ksort($toSort, SORT_STRING);
        return $toSort;
    }

    /**
     * Load one migration and execute it
     *
     * @param String $scriptPath Path to the script to execute
     *
     * @return void
     */
    protected function runMigration(SplFileInfo $scriptPath) {
        include $scriptPath->getPathname();

        $class = $this->getClassName($scriptPath->getPathname());
        if ($class != '' && class_exists($class)) {
            $upg = new $class($this->db);
            echo $upg->description();
            $upg->up();
            var_dump($upg->getLogs());
        }
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
