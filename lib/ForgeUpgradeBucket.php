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

/**
 * A bucket is a migration scenario
 */
abstract class ForgeUpgradeBucket {
    const LOG_INFO    = 'info';
    const LOG_WARNING = 'warning';
    const LOG_ERROR   = 'error';

    protected $logs;
    protected $db;

    /**
     * Constructor
     *
     * @param ForgeUpgradeDb Database access
     */
    public function __construct(ForgeUpgradeDb $db) {
        $this->logs = array();
        $this->db   = $db;
    }

    /**
     * Return a string with the description of the upgrade
     *
     * @return String
     */
    abstract public function description();

    /**
     * Perform the upgrade
     */
    abstract public function up();

    /**
     * Return all collected messages
     */
    public function getLogs() {
        return $this->logs;
    }

    /**
     * Happend a new log message
     *
     * @todo Define what each level is.
     *
     * @param String $logs Message to report
     */
    public function log($type, $msg) {
        $this->logs[] = array('type' => $type, 'date' => microtime(true), 'msg' => $msg);
    }
}

?>
