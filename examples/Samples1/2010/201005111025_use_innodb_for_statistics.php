<?php

//require dirname(__FILE__).'/../lib/ForgeUpgrade_Bucket.php';

/**
 *
 */
class UseInnoDBForStatistics extends ForgeUpgrade_Bucket {
    public function description() {
        return <<<EOT
Change the storage engine from MyIsam to InnoDb for statistics tables 
EOT;
    }
    
    public function up() {
        $sql = ' ALTER TABLE plugin_statistics_user_session TYPE= INNODB';
        $this->db->alterTable('plugin_statistics_user_session', 'INFORMATION_SCHEMA.TABLES', 'engine = "INNODB"', $sql);

        $sql = ' ALTER TABLE  plugin_statistics_diskusage_group TYPE= INNODB';
        $this->db->alterTable('plugin_statistics_diskusage_group', 'INFORMATION_SCHEMA.TABLES', 'engine = "INNODB"', $sql);
                
        $sql = ' ALTER TABLE plugin_statistics_diskusage_user TYPE= INNODB';
        $this->db->alterTable('plugin_statistics_diskusage_user', 'INFORMATION_SCHEMA.TABLES', 'engine = "INNODB"', $sql);
        

        $sql = ' ALTER TABLE plugin_statistics_diskusage_site TYPE= INNODB';
        $this->db->alterTable('plugin_statistics_diskusage_site', 'INFORMATION_SCHEMA.TABLES', 'engine = "INNODB"', $sql);
       
     }
    
    public function postUp() {
       if (!$this->db->propertieExists('plugin_statistics_user_session', 'INFORMATION_SCHEMA.TABLES', 'engine = "INNODB"')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('The engine has not been changed for plugin_statistics_user_session');
        }
        if (!$this->db->propertieExists('plugin_statistics_diskusage_group', 'INFORMATION_SCHEMA.TABLES', 'engine = "INNODB"')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('The engine has not been changed for plugin_statistics_diskusage_group');
        }
        if (!$this->db->propertieExists('plugin_statistics_diskusage_user', 'INFORMATION_SCHEMA.TABLES', 'engine = "INNODB"')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('The engine has not been changed for plugin_statistics_diskusage_user');
        }
        if (!$this->db->propertieExists('plugin_statistics_diskusage_site', 'INFORMATION_SCHEMA.TABLES', 'engine = "INNODB"')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('The engine has not been changed for plugin_statistics_diskusage_site');
        }
       
    }
 
}
?>