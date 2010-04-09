<?php

require_once dirname(__FILE__).'/../../lib/ForgeUpgradeBucket.php';

/**
 *
 */
class AddDateColumnToItem extends ForgeUpgradeBucket {

    public function dependsOn() {
        return array('AddTablesForDocmanWatermarking');
    }

    public function description() {
        return <<<EOT
Add column to DocmanWatermarking table.

EOT;
    }

    public function preUp() {
        return $this->db->tableExists('plugin_docmanwatermark_item_excluded');
    }

    public function up() {
        //
    }

}

?>
