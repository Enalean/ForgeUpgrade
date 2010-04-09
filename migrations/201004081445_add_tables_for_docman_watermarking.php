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
        if (!$this->db->createTable($this, 'plugin_docmanwatermark_item_excluded', $sql)) {
            return false;
        }

        $sql = 'CREATE TABLE plugin_docmanwatermark_item_excluded_log ('.
               '  item_id INT(11) UNSIGNED NOT NULL,'.
               '  time INT(11) UNSIGNED NOT NULL DEFAULT 0,'.
               '  who INT(11) UNSIGNED NOT NULL DEFAULT 0,'.
               '  watermarked TINYINT(4) UNSIGNED NOT NULL DEFAULT 0,'.
               '  INDEX idx_show_log(item_id, time)'.
               ')';
        if (!$this->db->createTable($this, 'plugin_docmanwatermark_item_excluded_log', $sql)) {
            return false;
        }
    }
}

?>