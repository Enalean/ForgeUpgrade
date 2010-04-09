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

    protected $dryRun;

    /**
     * Constructor
     *
     * @param ForgeUpgradeDb Database access
     */
    public function __construct(ForgeUpgradeDb $db) {
        $this->logs   = array();
        $this->db     = $db;
        $this->dryRun = true;
    }

    /**
     * Return a string with the description of the upgrade
     *
     * @return String
     */
    abstract public function description();

    /**
     * Allow to define a dependency list
     *
     * @return Array
     */
    public function dependsOn() {
    }

    /**
     * Ensure the package is OK before running Up method
     *
     * Use this method add your own pre-conditions.
     * This method aims to verify stuff needed by the up method it doesn't
     * target a global validation of the application.
     *
     * This method MUST be safe (doesn't modify the system and runnable several
     * time)
     *
     * @return Boolean True if up could be run.
     */
    public function preUp() {
        return true;
    }

    /**
     * Perform the upgrade
     */
    abstract public function up();

    /**
     * Ensure the package is OK after running Up method
     *
     * Use this method add your own post-conditions.
     * This method aims to verify that what the migration should bring is here.
     *
     * This method MUST be safe (doesn't modify the system and runnable several
     * time)
     *
     * @return Boolean True if up did it's job.
     */
    public function postUp() {
        return true;
    }






    /**
     *
     */
    public function setDryRun($mode) {
        $this->dryRun = ($mode === true);
    }

    public function getDryRun() {
        return $this->dryRun();
    }

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
