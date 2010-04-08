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


            $this->runMigrations();

        } catch (PDOException $e) {
            echo 'Connection faild: '.$e->getMessage().PHP_EOL;
        }
    }

    public function runMigrations() {
        $this->runMigration('migrations/201004081445_add_tables_for_docman_watermarking.php');
    }


    /**
     * Load one migration and execute it
     */
    public function runMigration($scriptPath) {
        include $scriptPath;

        $class = $this->getClassName($scriptPath);
        if ($class !== null) {
            $upg = new $class($this->db);
            echo $upg->description();
            $upg->up();
            var_dump($upg->getMessages());
        }
    }

    /**
     * Deduce the class name from the script name
     *
     * migrations/201004081445_add_tables_for_docman_watermarking.php -> AddTablesForDocmanWatermarking
     */
    protected function getClassName($script) {
        if(preg_match('%^[0-9]+_(.*)\.php$%', basename($script), $matches)) {
            $words    = explode('_', $matches[1]);
            $capWords = array_map('ucfirst', $words);
            return implode('', $capWords);
        }
        return null;
    }


}


?>
