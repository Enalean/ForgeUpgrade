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

require getenv('CODENDI_LOCAL_INC') ? getenv('CODENDI_LOCAL_INC') : '/etc/codendi/conf/local.inc' ;
require $GLOBALS['db_config_file'];
require 'src/ForgeUpgrade.php';
require 'lib/log4php/Logger.php';

// Parameters
$func = 'help';
for ($i = 1; $i < $argc; $i++) {
    switch ($argv[$i]) {
        default:
            $func = substr($argv[$i], 2, strlen($argv[$i]));
            break;
    }
}

// Go
try {
    if (strpos($GLOBALS['sys_dbhost'], ':') !== false) {
        list($host, $socket) = explode(':', $GLOBALS['sys_dbhost']);
        $socket = ';unix_socket='.$socket;
    } else {
        $host   = $GLOBALS['sys_dbhost'];
        $socket = '';
    }

    $dbh = new PDO('mysql:host='.$host.$socket.';dbname='.$GLOBALS['sys_dbname'],
                   $GLOBALS['sys_dbuser'],
                   $GLOBALS['sys_dbpasswd'],
                   array(PDO::MYSQL_ATTR_INIT_COMMAND =>  'SET NAMES \'UTF8\''));

    $upg = new ForgeUpgrade($dbh);
    $upg->run($func);
} catch (PDOException $e) {
    echo 'Connection faild: '.$e->getMessage().PHP_EOL;
}

?>
