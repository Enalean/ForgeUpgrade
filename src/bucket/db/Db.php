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

require 'Exception.php';

/**
 * Wrap accesss to the DB and provide a set of convenient tools to write
 * DB upgrades
 */
class ForgeUpgrade_Bucket_Db {
    public $dbh;

    protected $log;

    /**
     * Constructor
     *
     * @param PDO $dbh PDO database handler
     */
    public function __construct(PDO $dbh) {
        $this->dbh = $dbh;
        $this->log = Logger::getLogger(get_class());
    }

    /**
     * Return true if the given table name already exists into the database
     *
     * @param String $tableName Table name
     *
     * @return Boolean
     */
    public function tableNameExists($tableName) {
        $sql = 'SHOW TABLES LIKE '.$this->dbh->quote($tableName);
        $res = $this->dbh->query($sql);
        if ($res && $res->fetch() !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return true if the given column name already exists into the database
     *
     * @param String $tableName  Table name
     * @param String $columnName Column name
     *
     * @return Boolean
     */
    public function columnNameExists($tableName, $columnName) {
        $sql = 'SHOW COLUMNS FROM `'.$tableName.'` LIKE '.$this->dbh->quote($columnName);
        $res = $this->dbh->query($sql);
        if ($res && $res->fetch() !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return true if the given index name on the table already exists into the database
     *
     * @param String $tableName Table name 
     * @param String $index     Index
     *
     * @return Boolean
     */
    public function indexNameExists($tableName, $index) {
        $sql = 'SHOW INDEX FROM '.$this->dbh->quote($tableName).' WHERE Key_name LIKE '.$this->dbh->quote($index);
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
        if (!$this->tableNameExists($tableName)) {
            $res = $this->dbh->exec($sql);
            if ($res === false) {
                $info = $this->dbh->errorInfo();
                $this->log->error('An error occured adding table '.$tableName.': '.$info[2].' ('.$info[1].' - '.$info[0].')');
                throw new ForgeUpgrade_Bucket_Db_Exception($msg);
            }
            $this->log->info($tableName.' successfully added');
        } else {
            $this->log->info($tableName.' already exists');
        }
    }
    
    /**
     * Delete table
     *
     * Delete table if exists and report errors.
     *
     * @param String             $tableName Table name
     * @param String             $sql       The delete table statement (optionnal)
     */
    public function dropTable($tableName, $sql='') {
        $this->log->info('Delete table '.$tableName);
        if ($this->tableNameExists($tableName)) {
            if (!$sql) {
                $sql = 'DROP TABLE `'.$tableName.'`';
            }
            $res = $this->dbh->exec($sql);
            if ($res === false) {
                $info = $this->dbh->errorInfo();
                $this->log->error('An error occured deleting table '.$tableName.': '.$info[2].' ('.$info[1].' - '.$info[0].')');
                throw new ForgeUpgrade_Bucket_Db_Exception($msg);
            }
            $this->log->info($tableName.' successfully deleted');
        } else {
            $this->log->info($tableName.' not exists');
        }
    }

    /**
     * Add index
     *
     * Alter table to add index and report errors.
     *
     * @param String             $tableName Table name
     * @param String             $sql       The add index statement
     */
    public function addIndex($tableName, $index, $sql) {
        $this->log->info('Add index '.$tableName);
        if (!$this->indexNameExists($tableName, $index)) {
            $res = $this->dbh->exec($sql);
            if ($res === false) {
                $info = $this->dbh->errorInfo();
                $this->log->error('An error occured adding index to '.$tableName.': '.$info[2].' ('.$info[1].' - '.$info[0].')');
                throw new ForgeUpgrade_Bucket_Db_Exception($msg);
            }
            $this->log->info($index.' successfully added index');
        } else {
            $this->log->info($index.' already exists');
        }
    }

    public function setLoggerParent(Logger $log) {
        $this->log->setParent($log);
    }
}

?>