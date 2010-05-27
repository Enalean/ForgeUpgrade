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

require 'lib/log4php/Logger.php';
require 'src/ForgeUpgrade.php';
require 'src/LoggerAppenderConsoleColor.php';

// Parameters
$func         = 'help';
$options['core']['path']         = array();
$options['core']['include_path'] = array();
$options['core']['exclude_path'] = array();
$options['core']['dbdriver']     = null;
$options['core']['ignore_preup'] = false;
$options['core']['force']        = false;

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

    // --config
    if (preg_match('/--config=(.*)/',$argv[$i], $matches)) {
        if (is_file($matches[1])) {
            $options = parse_ini_file($matches[1], true);
        }
    }
    
    // --path
    if (preg_match('/--path=(.*)/',$argv[$i], $matches)) {
        if (is_dir($matches[1]) || is_file($matches[1])) {
            $options['core']['path'][] = $matches[1];
        } else {
            echo 'Error "'.$matches[1].'" is not a valid directory'.PHP_EOL;
        }
    }

    // --include
    if (preg_match('/--include=(.*)/',$argv[$i], $matches)) {
        $options['core']['include_path'][] = surroundBy($matches[1], '/');
    }

    // --exclude
    if (preg_match('/--exclude=(.*)/',$argv[$i], $matches)) {
        $options['core']['exclude_path'][] = surroundBy($matches[1], '/');
    }
    
    // --driver
    if (preg_match('/--dbdriver=(.*)/',$argv[$i], $matches)) {
        $options['core']['dbdriver'] = $matches[1];
    }
    
    //--ignore-preup
    if (preg_match('/--ignore-preup/',$argv[$i], $matches)) {
        $options['core']['ignore_preup'] = true;
    }
    
    //--force
    if (preg_match('/--force/',$argv[$i], $matches)) {
        $options['core']['force'] = true;
    }
}

if ($func == 'help') {
    usage();
    exit;
}

// Get the DB connexion
// First try the file
if (is_file($options['core']['dbdriver'])) {
    require $options['core']['dbdriver'];
    //$className = $options['core']['dbdriver'];
    $dbDriverName = basename($options['core']['dbdriver'], '.php');
} else {
    $dbDriverName = ucfirst(strtolower($options['core']['dbdriver']));
    $filePath = 'src/db/driver/'.$dbDriverName.'.php';
    if (is_file($filePath)) {
        require $filePath;
        $dbDriverName = 'ForgeUpgrade_Db_Driver_'.$dbDriverName;
    } else {
        echo "Error: invalid --dbdriver".PHP_EOL;
    }
}
try {
    $dbDriver = new $dbDriverName();
} catch (PDOException $e) {
    echo 'Connection faild: '.$e->getMessage().PHP_EOL;
    return -1;
}

// Special logger to display nice colors according to levels
$logger = Logger::getRootLogger();
$logger->removeAllAppenders();
$appender = new LoggerAppenderConsoleColor('LoggerAppenderConsoleColor');
$appender->setLayout( new LoggerLayoutTTCC() );
$appender->activateOptions();
$logger->addAppender($appender);

// Go
$upg = new ForgeUpgrade($dbDriver);
if (isset($options['core']['include_path'])) {
    $upg->setIncludePaths($options['core']['include_path']);
}
if (isset($options['core']['exclude_path'])) {
    $upg->setExcludePaths($options['core']['exclude_path']);
}
if (isset($options['core']['ignore_preup'])) {
    $upg->setIgnorePreUpOption($options['core']['ignore_preup']);
}
if (isset($options['core']['force'])) {
    $upg->setForceOption($options['core']['force']);
}
$upg->run($func, $options['core']['path']);

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
  --config=[/path]         Path to ForgeUpgrade config file (you can define all options in a config.ini file)
  --path=[/path]           Path where to find migration buckets [default: current dir]
  --include=[/path]        Only consider paths that contains given pattern
  --exclude=[/path]        Don't consider paths that contains given pattern

  --dbdriver=[name|/path]  The database driver to use (either a name or a path
                           to the driver file for custom ones).
  --ignore-preup     Execute migration buckets whithout running "pre" checks
  --force            Execute migration buckets even there are errors  
                           

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
