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

require 'ForgeUpgradeDbException.php';

/**
 * Wrap accesss to the DB and provide a set of convenient tools to write
 * DB upgrades
 */
class ForgeUpgradeDb {
    public $dbh;

    protected $log;

    /**
     * Constructor
     *
     * @param PDO $dbh PDO database handler
     */
    public function __construct(PDO $dbh, Logger $log) {
        $this->dbh = $dbh;
        $this->log = $log;
    }

    /**
     * Return true if the given table already exists into the database
     *
     * @param String $tableName Table name
     *
     * @return Boolean
     */
    public function tableExists($tableName) {
        $sql = 'SHOW TABLES LIKE '.$this->dbh->quote($tableName);
        $res = $this->dbh->query($sql);
        if ($res && $res->fetch() !== false) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Create new table
     *
     * Create new table if not already exists and report errors.
     *
     * @param String             $tableName Table name
     * @param String             $sql       The create table statement
     */
    public function createTable($tableName, $sql) {
        $this->log->info('Add table '.$tableName);
        if (!$this->tableExists($tableName)) {
            $res = $this->dbh->exec($sql);
            if ($res === false) {
                $info = $this->dbh->errorInfo();
                $this->log->error('An error occured adding table '.$tableName.': '.$info[2].' ('.$info[1].' - '.$info[0].')');
                throw new ForgeUpgradeDbException($msg);
            }
            $this->log->info($tableName.' successfully added');
        } else {
            $this->log->info($tableName.' already exists');
        }
    }

}

?>