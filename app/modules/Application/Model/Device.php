<?php

class Application_Model_Device extends Core_Model_Default {

    protected $_type;
    protected $_status;

    protected static $_device_ids = array(
        1 => 'Iphone',
        2 => 'Android'
    );

    public static function getAllIds() {
        return self::$_device_ids;
    }

    public function getType() {
        if(is_null($this->_type)) {
            $class = get_class() . '_' . self::$_device_ids[$this->getDeviceId()];
            $this->_type = new $class();
            $this->_type->setDevice($this);
        }

        return $this->_type;
    }

    public function getName() {

        $name = '';
        if($this->getDeviceId()) {
            $name = !empty(self::$_device_ids[$this->getDeviceId()]) ? self::$_device_ids[$this->getDeviceId()] : '';
        }

        return $name;
    }

    public function getStoreName() {
        $name = '';
        if($this->getDeviceId()) {
            $name = $this->getType()->getStoreName();
        }

        return $name;
    }

    public function getResources() {
        return $this->getType()->getResources();
    }

    public function unsetStatus() {
        $this->_status = null;
    }

    public function getStatus(){

        if(is_null($this->_status)) {
            $status = new Application_Model_Status();
            $this->_status = $status->find($this->getStatusId());
        }

        return $this->_status;

    }

    public function setStatusId($status_id) {
        $this->setData('status_id', $status_id);
        $this->_status = null;
    }

    public function getAdmin() {

        if(!$this->_admin) {
            $this->_admin = new Admin_Model_Admin();
            $this->_admin->find($this->getAdminId());
        }

        return $this->_admin;
    }

    public function setVersion($version = null) {
        if(!$version) $version = $this->getType()->getCurrentVersion();
        $this->setData('version', $version);
        return $this;
    }
}

?>
