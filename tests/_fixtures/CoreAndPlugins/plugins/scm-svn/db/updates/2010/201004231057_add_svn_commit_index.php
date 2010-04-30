<?php

/**
 *
 */
class b201004231057_add_svn_commit_index extends ForgeUpgrade_Bucket {
    public function description() {
        return <<<EOT
Replace svn_commit index by a new one more efficient for "SVN Commit" like
queries.
EOT;
    }
    
    public function up() {
        
    }
}

?>
