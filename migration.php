<?php

require getenv('CODENDI_LOCAL_INC') ? getenv('CODENDI_LOCAL_INC') : '/etc/codendi/conf/local.inc' ;
require $GLOBALS['db_config_file'];
require 'ForgeUpgrade.php';

$upg = new ForgeUpgrade();
$upg->run();

?>
