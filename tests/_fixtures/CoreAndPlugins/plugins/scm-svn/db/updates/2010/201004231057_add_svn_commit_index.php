<?php

/**
 *
 */
class AddSvnCommitIndex extends ForgeUpgrade_Bucket {
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