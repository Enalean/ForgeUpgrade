CREATE TABLE `forge_upgrade_bucket` (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    script VARCHAR(255) NOT NULL default '',
    date DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    status TINYINT(4) NOT NULL default '0',
    log TEXT NOT NULL,
  PRIMARY KEY(id)
) Engine=InnoDb;

CREATE TABLE `forge_upgrade_log` (
    timestamp DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    logger VARCHAR(64) NOT NULL default '',
    level VARCHAR(32) NOT NULL default '',
    message TEXT NOT NULL,
    thread VARCHAR(32) NOT NULL default '',
    file VARCHAR(255) NOT NULL default '',
    line INT(11) NOT NULL default 0
) Engine=InnoDb;
