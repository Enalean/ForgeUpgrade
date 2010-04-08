<?php

class ForgeUpgradeDb {
    public $dbh;

    public function __construct(PDO $dbh) {
        $this->dbh = $dbh;
    }

    public function tableExists($tableName) {
        $sql = 'SHOW TABLES LIKE '.$this->dbh->quote($tableName);
        $res = $this->dbh->query($sql);
        if ($res && $res->fetch() !== false) {
            return true;
        } else {
            return false;
        }

    }

    public function createTable(ForgeUpgradeBucket $bucket, $tableName, $sql) {
        $bucket->addInfo('Add table '.$tableName);
        if (!$this->tableExists($tableName)) {
            $res = $this->dbh->exec($sql);
            if ($res === false) {
                $info = $this->dbh->errorInfo();
                $msg  = 'An error occured adding table '.$tableName.': '.$info[2].' ('.$info[1].' - '.$info[0].')';
                $bucket->addError($msg);
                return false;
            }
            $bucket->addInfo($tableName.' successfully added');
        } else {
            $bucket->addInfo($tableName.' already exists');
        }
        return true;
    }

}

?>