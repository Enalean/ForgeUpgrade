<?php

/**
 *
 */
class b201004091318_add_date_column_to_item extends ForgeUpgrade_Bucket {

    public function dependsOn() {
        return array('AddTablesForDocmanWatermarking');
    }

    public function description() {
        return <<<EOT
Add column to DocmanWatermarking table.

EOT;
    }

    public function preUp() {
        $this->db = $this->getApi('ForgeUpgrade_Bucket_Db');
        $this->log->info("Check table exists");
        return $this->db->tableNameExists('plugin_docmanwatermark_item_excluded');
    }

    public function up() {
        throw new Exception('Coin');
    }

}

?>
