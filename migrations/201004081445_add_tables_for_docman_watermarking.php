<?php

require_once dirname(__FILE__).'/../lib/ForgeUpgradeBucket.php';

class AddTablesForDocmanWatermarking extends ForgeUpgradeBucket {

    public function description() {
        return <<<EOT
Add tables to docman pdf watermarking plugin in order to
allow admins to disable watermarking on selected documents.

EOT;
    }

    public function up() {
        $sql = 'CREATE TABLE plugin_docmanwatermark_item_excluded ('.
               '  item_id INT(11) UNSIGNED NOT NULL,'.
               '  PRIMARY KEY(item_id)'.
               ')';
        $this->db->createTable($this, 'plugin_docmanwatermark_item_excluded', $sql);

        $sql = 'CREATE TABLE plugin_docmanwatermark_item_excluded_log ('.
               '  item_id INT(11) UNSIGNED NOT NULL,'.
               '  time INT(11) UNSIGNED NOT NULL DEFAULT 0,'.
               '  who INT(11) UNSIGNED NOT NULL DEFAULT 0,'.
               '  watermarked TINYINT(4) UNSIGNED NOT NULL DEFAULT 0,'.
               '  INDEX idx_show_log(item_id, time)'.
               ')';
        $this->db->createTable($this, 'plugin_docmanwatermark_item_excluded_log', $sql);
    }

    public function postUp() {
        // This 2 checks could be automated with $this->db->createTable
        if (!$this->db->tableExists('plugin_docmanwatermark_item_excluded')) {
            throw new ForgeUpgradeBucketUpgradeNotCompleteException('plugin_docmanwatermark_item_excluded table is missing');
        }
        if (!$this->db->tableExists('plugin_docmanwatermark_item_excluded_log')) {
            throw new ForgeUpgradeBucketUpgradeNotCompleteException('plugin_docmanwatermark_item_excluded_log table is missing');
        }
    }

}

?>