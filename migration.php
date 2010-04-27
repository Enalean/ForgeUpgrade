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

// An upgrade process shouldn't end because it takes too much time ot too
// memory.
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

ini_set('include_path', dirname(__FILE__).PATH_SEPARATOR.ini_get('include_path'));

require 'src/ForgeUpgrade.php';
require 'lib/log4php/Logger.php';

// Parameters
$func         = 'help';
$paths        = array();
$includePaths = array();
$excludePaths = array();
for ($i = 1; $i < $argc; $i++) {
    //
    // Commands
    switch ($argv[$i]) {
        case 'help':
        case 'record-only':
        case 'update':
        case 'check-update':
        case 'run-pre':
        case 'already-applied':
            $func = $argv[$i];
            break;
    }

    //
    // Options

    // --path
    if (preg_match('/--path=(.*)/',$argv[$i], $matches)) {
        if (is_dir($matches[1])) {
            $paths[] = $matches[1];
        } else {
            echo 'Error "'.$matches[1].'" is not a valid directory'.PHP_EOL;
        }
    }

    // --include
    if (preg_match('/--include=(.*)/',$argv[$i], $matches)) {
        $includePaths[] = surroundBy($matches[1], '/');
    }

    // --exclude
    if (preg_match('/--exclude=(.*)/',$argv[$i], $matches)) {
        $excludePaths[] = surroundBy($matches[1], '/');
    }
    
    // --driver
    if (preg_match('/--driver=(.*)/',$argv[$i], $matches)) {
        // First try the file
        if (is_file($matches[1])) {
            require $matches[1];
            $className = $matches[1];
            $dbDriver = basename($matches[1], '.php');
        } else {
            $dbDriver = ucfirst(strtolower($matches[1]));
            $filePath = 'driver/'.$dbDriver.'.php';
            if (is_file($filePath)) {
                require $filePath;
                $dbDriver = 'ForgeUpgrade_Driver_'.$dbDriver;
            } else {
                echo "Error: invalid --driver".PHP_EOL;
            }
        }
    }
}

if ($func == 'help') {
    usage();
    exit;
}

// Get the DB connexion
try {
    $driver = new $dbDriver();
} catch (PDOException $e) {
    echo 'Connection faild: '.$e->getMessage().PHP_EOL;
    return -1;
}

// Go
$upg = new ForgeUpgrade($driver->getPdo());
$upg->setIncludePaths($includePaths);
$upg->setExcludePaths($excludePaths);
$upg->run($func, $paths);

//
// Function definitions
//

/**
 * Print Help
 */
function usage() {
    echo <<<EOT
Usage: migration.php [options] command

Commands:
already-applied  List all applied buckets
check-update     List all available migration buckets not already applied (pending)
run-pre          Run pending migration buckets "pre" checks 
update           Execute pending migration buckets
record-only      Record all available buckets as executed in the database without
                 actually executing them

Options:
  --path=[/path]    Path where to find migration buckets [default: current dir]
  --include=[/path] Only consider paths that contains given pattern
  --exclude=[/path] Don't consider paths that contains given pattern

EOT;
}

/**
 * Surround a string by a char if not present
 * 
 * @param String $str  String to surround
 * @param String $char Char to add
 * 
 * @return String
 */
function surroundBy($str, $char) {
    if (strpos($str, $char) === false) {
        $str = $char.$str.$char;
    } else {
        if (strpos($str, $char) !== 0) {
            $str = $char.$str;
        }
        if (strrpos($str, $char) !== (strlen($str) - 1)) {
            $str = $str.$char;
        }
    }
    return $str;
}

?>
