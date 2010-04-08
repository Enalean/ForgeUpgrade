<?php

require 'ForgeUpgradeDb.php';

class ForgeUpgrade {

    public function run() {
        try {
            if (strpos($GLOBALS['sys_dbhost'], ':') !== false) {
                list($host, $socket) = explode(':', $GLOBALS['sys_dbhost']);
                $socket = ';unix_socket='.$socket;
            } else {
                $host   = $GLOBALS['sys_dbhost'];
                $socket = '';
            }

            $dbh = new PDO('mysql:host='.$host.$socket.';dbname='.$GLOBALS['sys_dbname'], $GLOBALS['sys_dbuser'], $GLOBALS['sys_dbpasswd'], array(PDO::MYSQL_ATTR_INIT_COMMAND =>  'SET NAMES \'UTF8\''));
            $this->db = new ForgeUpgradeDb($dbh);


            $this->runScripts();

        } catch (PDOException $e) {
            echo 'Connection faild: '.$e->getMessage().PHP_EOL;
        }
    }

    public function runScripts() {
        include 'migrations/201004081445_add_tables_for_docman_watermarking.php';
        $upg = new AddTablesForDocmanWatermarking($this->db);
        echo $upg->description();
        $upg->up();
        var_dump($upg->getMessages());
    }


}


?>
