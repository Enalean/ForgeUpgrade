<?php

/**
 *
 */
class b201004231100_add_date_column_to_ugroup_table extends ForgeUpgrade_Bucket {

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
