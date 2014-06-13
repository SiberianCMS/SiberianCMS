<?php

class Push_Model_Message extends Core_Model_Default {

    protected $_types = array(
        'iphone' => 'Push_Model_Iphone_Message',
        'android' => 'Push_Model_Android_Message'
    );

    protected $_instances;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Push_Model_Db_Table_Message';

        $this->_initInstances();
    }

    public function getInstance($type = null) {
        if(!empty($this->_instances[$type])) return $this->_instances[$type];
        else return null;
    }

    public function getInstances() {
        return $this->_instances;
    }

    public function getMessages() {
        return $this->getTable()->getMessages();
    }

    public function getTitle() {
        return mb_convert_encoding($this->getData('title'), 'UTF-8', 'UTF-8');
    }

    public function getText() {
        return mb_convert_encoding($this->getData('text'), 'UTF-8', 'UTF-8');
    }

    public function markAsRead($device_uid) {
        return $this->getTable()->markAsRead($device_uid);
    }

    public function markAsDisplayed($device_id, $message_id) {
        return $this->getTable()->markAsDisplayed($device_id, $message_id);
    }

    public function findByDeviceId($device_id) {
        return $this->getTable()->findByDeviceId($device_id);
    }

    public function countByDeviceId($device_id) {
        return $this->getTable()->countByDeviceId($device_id);
    }

    public function push() {
        $errors = array();
        $this->updateStatus('sending');
        foreach($this->_instances as $type => $instance) {
            $instance->setMessage($this)
                ->push()
            ;
            if($instance->getErrors()) {
                $errors[$instance->getId()] = $instance->getErrors();
            }
        }
        $this->updateStatus('delivered');

        $this->setErrors($errors);

    }

    public function createLog($device, $status, $id = null) {

        if(!$id) $id = $device->getDeviceUid();
        $is_displayed = $device->getTypeId() == 1 ? 1 : 0;
        if(!$is_displayed) {
            $is_displayed = !$this->getLatitude() && !$this->getLongitude();
        }
        $datas = array(
            'device_id' => $device->getId(),
            'device_uid' => $id,
            'device_type' => $device->getTypeId(),
            'is_displayed' => $is_displayed,
            'message_id' => $this->getId(),
            'status' => $status,
            'delivered_at' => $this->formatDate(null, 'y-MM-dd HH:mm:ss')
        );

        $this->getTable()->createLog($datas);

        return $this;
    }

    public function updateStatus($status) {

        $this->setStatus($status);
        if($status == 'delivered') {
            $this->setDeliveredAt($this->formatDate(null, 'y-MM-dd HH:mm:ss'));
        }

        $this->save();

    }

    protected function _initInstances() {

        if(is_null($this->_instances)) {

            $this->_instances = array();
            foreach($this->_types as $device => $type) {
                $this->_instances[$device] = new $type();
            }
        }

        return $this->_instances;
    }

}