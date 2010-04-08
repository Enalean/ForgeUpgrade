<?php

abstract class ForgeUpgradeBucket {
    protected $msg;
    protected $db;

    public function __construct(ForgeUpgradeDb $db) {
        $this->msg = array();
        $this->db  = $db;
    }

    abstract public function description();

    abstract public function up();

    public function getMessages() {
        return $this->msg;
    }

    public function addError($msg) {
        $this->msg[] = array('type' => 'error', 'msg' => $msg);
    }

    public function addInfo($msg) {
        $this->msg[] = array('type' => 'info', 'msg' => $msg);
    }

    public function addWarning($msg) {
        $this->msg[] = array('type' => 'warning', 'msg' => $msg);
    }


}

?>
