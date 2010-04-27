<?php

/**
 *
 */
class AddDateColumnToUgroupTable extends ForgeUpgrade_Bucket {

    public function description() {
        return <<<EOT
Add column to plugin_ldap_ugroup table to track when a ugroup and a ldap group
are bound.
EOT;
    }
    
    public function up() {
        
    }
}

?>
