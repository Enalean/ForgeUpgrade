<?php

/**
 *
 */
class b201004231055_add_system_event_table extends ForgeUpgrade_Bucket {

    public function description() {
        return <<<EOT
Add table for system event logging
EOT;
    }

    public function up() {
        /*DROP TABLE IF EXISTS system_event;
CREATE TABLE IF NOT EXISTS system_event (
  id INT(11) unsigned NOT NULL AUTO_INCREMENT, 
  type VARCHAR(255) NOT NULL default '',
  parameters TEXT,
  priority TINYINT(1) NOT NULL default '0',
  status  ENUM( 'NEW', 'RUNNING', 'DONE', 'ERROR', 'WARNING' ) NOT NULL DEFAULT 'NEW',
  create_date DATETIME NOT NULL,
  process_date DATETIME NULL,
  end_date DATETIME NULL,
  log TEXT,
  PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE system_events_followers (
  id INT(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY, 
  emails TEXT NOT NULL ,
  types VARCHAR( 31 ) NOT NULL
);

INSERT INTO system_events_followers (emails, types) VALUES
('admin', 'WARNING,ERROR');*/
    }
}

?>
