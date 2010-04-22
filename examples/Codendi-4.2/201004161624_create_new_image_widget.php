<?php

require_once dirname(__FILE__).'/../src/ForgeUpgradeBucket.php';

/**
 *
 */
class CreateNewImageWidget extends ForgeUpgradeBucket {

    
    public function description() {
        return <<<EOT
Creating table for the new widget image.

EOT;
    }

    public function preUp() {
        
    }

    public function up() {
        $sql = 'CREATE TABLE IF NOT EXISTS widget_image ('.
                ' id int(11) unsigned NOT NULL auto_increment PRIMARY KEY,'.
                ' owner_id int(11) unsigned NOT NULL default 0,'.
                ' owner_type varchar(1) NOT NULL default "u",'.
                ' title varchar(255) NOT NULL,'.
                ' url TEXT NOT NULL,'.
                ' KEY (owner_id, owner_type)'.
                ')';

        $this->db->createTable('widget_image', $sql);
        
    }
    
    public function postUp() {
        if (!$this->db->tableNameExists('widget_image')) {
            throw new ForgeUpgradeBucketUpgradeNotCompleteException('widget_image not existing');
        }
        
    }

}

?>
