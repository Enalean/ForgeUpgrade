<?php

require_once dirname(__FILE__).'/../src/ForgeUpgradeBucket.php';


/**
 *
 */
class AddPluginIndex extends ForgeUpgradeBucket {

    public function description() {
        return <<<EOT
Add indexes for plugin and priority_plugin_hook
EOT;
    }
    
    public function preUp() {
        return ($this->db->indexExists('idx_plugin_id', 'priority_plugin_hook'));
    }


    public function up() {
        $sql = 'ALTER TABLE priority_plugin_hook ADD INDEX idx_plugin_id (plugin_id)'; 
        $this->db->addIndex('idx_plugin_id', 'priority_plugin_hook', $sql);

        $sql = 'ALTER TABLE plugin ADD INDEX idx_available (available)';
        $this->db->addIndex('idx_available', 'plugin', $sql);
    }
    
}

?>
