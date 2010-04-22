<?php

require_once dirname(__FILE__).'/../src/ForgeUpgradeBucket.php';


/**
 *
 */
class RemoveLegacyTables extends ForgeUpgradeBucket {

    
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
            throw new ForgeUpgradeBucketUpgradeNotCompleteException('support_messages still exist');
        }
        if ($this->db->tableNameExists('bug_cc')) {
            throw new ForgeUpgradeBucketUpgradeNotCompleteException('bug_cc still exist');
        }
        if ($this->db->tableNameExists('project_cc')) {
            throw new ForgeUpgradeBucketUpgradeNotCompleteException('project_cc still exist');
        }
        if ($this->db->tableNameExists('frs_status')) {
            throw new ForgeUpgradeBucketUpgradeNotCompleteException('bug_cc still exist');
        }
    }

}

?>

