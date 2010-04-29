<?php
/**
 * Copyright (c) STMicroelectronics, 2010. All Rights Reserved.
 *
 * Originally written by Manuel Vacelet
 *
 * ForgeUpgrade is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * ForgeUpgrade is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with ForgeUpgrade. If not, see <http://www.gnu.org/licenses/>.
 */

class ForgeUpgrade_Db {

    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 2;
    const STATUS_SKIP    = 3;

    public function __construct(PDO $dbh) {
        $this->dbh = $dbh;
        $this->t['bucket'] = 'forge_upgrade_bucket';
    }

    public static function statusLabel($status) {
        $labels = array(self::STATUS_SUCCESS => 'success',
                        self::STATUS_FAILURE => 'failure',
                        self::STATUS_SKIP    => 'skipped');
        return $labels[$status];
    }
    
    public function logStart(ForgeUpgrade_Bucket $bucket) {
        $sth = $this->dbh->prepare('INSERT INTO '.$this->t['bucket'].' (script, start_date) VALUES (?, NOW())');
        if ($sth) {
            $sth->execute(array($bucket->getPath()));
            $bucket->setId($this->dbh->lastInsertId());
        }
    }

    public function logEnd(ForgeUpgrade_Bucket $bucket, $status) {
        $sth = $this->dbh->prepare('UPDATE '.$this->t['bucket'].' SET status = ?, end_date = NOW() WHERE id = ?');
        if ($sth) {
            return $sth->execute(array($status, $bucket->getId()));
        }
        return false;
    }
    
    public function getAllBuckets() {
        $sth = $this->dbh->prepare('SELECT * FROM '.$this->t['bucket'].' ORDER BY date ASC');
        if ($sth) {
            $sth->execute();
            return $sth;
        }
        return array();
    }

}

?>
