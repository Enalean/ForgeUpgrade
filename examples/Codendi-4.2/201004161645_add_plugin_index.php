<?php

/**
 *
 */
class b201004161645_add_plugin_index extends ForgeUpgrade_Bucket {

    public function description() {
        return <<<EOT
Add indexes for plugin and priority_plugin_hook
EOT;
    }
    
    
    public function up() {
        $sql = 'ALTER TABLE priority_plugin_hook ADD INDEX idx_plugin_id (plugin_id)'; 
        $this->db->addIndex('priority_plugin_hook', 'idx_plugin_id', $sql);

        $sql = 'ALTER TABLE plugin ADD INDEX idx_available (available)';
        $this->db->addIndex('plugin', 'idx_available', $sql);
    }
    
    public function postUp() {
        if (!$this->db->indexNameExists('priority_plugin_hook', 'idx_plugin_id')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('idx_plugin_id has not been created on priority_plugin_hook');
        }
        if (!$this->db->indexNameExists('plugin', 'idx_available')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('idx_available has not been created on plugin');
        }
        
    }
    
}

?>
