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

require_once 'Abstract.php';

class ForgeUpgrade_Db_Driver_Codendi extends ForgeUpgrade_Db_Driver_Abstract {

    /**
     * Setup the PDO object to be used for DB connexion
     *
     * The DB connexion will be used to store buckets execution log.
     *
     * @return PDO
     */
    public function getPdo() {
        $localInc = getenv('CODENDI_LOCAL_INC') ? getenv('CODENDI_LOCAL_INC') : '/etc/codendi/conf/local.inc';
        if (is_file($localInc)) {
            include $localInc;
            include $db_config_file;

            if (strpos($sys_dbhost, ':') !== false) {
                list($host, $socket) = explode(':', $sys_dbhost);
                $socket = ';unix_socket='.$socket;
            } else {
                $host   = $sys_dbhost;
                $socket = '';
            }

            $dbh = new PDO('mysql:host='.$host.$socket.';dbname='.$sys_dbname,
                           $sys_dbuser,
                           $sys_dbpasswd,
                           array(PDO::MYSQL_ATTR_INIT_COMMAND =>  "SET NAMES 'UTF8'"));
            return $dbh;
        }
        throw new Exception('Cannot setup PDO connexion to Codendi database');
    }
}

?>