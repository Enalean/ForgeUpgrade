<?php

//require_once dirname(__FILE__).'/../src/ForgeUpgrade_Bucket.php';


/**
 *
 */
class RemoveLegacyTables extends ForgeUpgrade_Bucket {

    
    public function description() {
        return <<<EOT
Remove legacy tables, no used more.

EOT;
    }

    
    public function up() {
        $this->db->dropTable('support_messages');
        
        $this->db->dropTable('bug_cc');

        $this->db->dropTable('project_cc');

        $sql = 'DROP TABLE frs_status';
        $this->db->dropTable('frs_status', $sql);

    }
    
    public function postUp() {
        if ($this->db->tableNameExists('support_messages')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('support_messages still exist');
        }
        if ($this->db->tableNameExists('bug_cc')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('bug_cc still exist');
        }
        if ($this->db->tableNameExists('project_cc')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('project_cc still exist');
        }
        if ($this->db->tableNameExists('frs_status')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('bug_cc still exist');
        }
    }

}

?>

