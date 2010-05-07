<?php

class b201004081445_add_tables_for_docman_watermarking extends ForgeUpgrade_Bucket {

    public function description() {
        return <<<EOT
Add tables to docman pdf watermarking plugin in order to
allow admins to disable watermarking on selected documents.

EOT;
    }

    public function preUp() {
        $this->db = $this->getApi('ForgeUpgrade_Bucket_Db');
    }

    public function up() {
        $sql = 'CREATE TABLE plugin_docmanwatermark_item_excluded ('.
               '  item_id INT(11) UNSIGNED NOT NULL,'.
               '  PRIMARY KEY(item_id)'.
               ')';
        $this->db->createTable('plugin_docmanwatermark_item_excluded', $sql);

        $sql = 'CREATE TABLE plugin_docmanwatermark_item_excluded_log ('.
               '  item_id INT(11) UNSIGNED NOT NULL,'.
               '  time INT(11) UNSIGNED NOT NULL DEFAULT 0,'.
               '  who INT(11) UNSIGNED NOT NULL DEFAULT 0,'.
               '  watermarked TINYINT(4) UNSIGNED NOT NULL DEFAULT 0,'.
               '  INDEX idx_show_log(item_id, time)'.
               ')';
        $this->db->createTable('plugin_docmanwatermark_item_excluded_log', $sql);
    }

    public function postUp() {
        // This 2 checks could be automated with $this->db->createTable
        if (!$this->db->tableNameExists('plugin_docmanwatermark_item_excluded')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('plugin_docmanwatermark_item_excluded table is missing');
        }
        if (!$this->db->tableNameExists('plugin_docmanwatermark_item_excluded_log')) {
            throw new ForgeUpgrade_Bucket_UpgradeNotCompleteException('plugin_docmanwatermark_item_excluded_log table is missing');
        }
    }

}

?>