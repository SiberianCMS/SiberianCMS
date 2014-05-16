<?php

class Push_Model_Db_Table_Message extends Core_Model_Db_Table {

    protected $_name = "push_messages";
    protected $_primary = "message_id";

    public function getMessages() {

        $select = $this->select()
            ->from(array('am' => $this->_name))
            ->where('am.status = ?', 'queued')
//            ->where('am.delivery = NOW()')
            ->order('am.created_at')
            ->where('pdm.is_displayed = ?', '1')
            ->limit(100)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }

    public function createLog($datas) {
        $select = $this->_db->select()
            ->from('push_delivered_message', array('deliver_id'))
            ->where('message_id = ?', $datas['message_id'])
            ->where('device_uid = ?', $datas['device_uid'])
        ;
        $deliver_id = $this->_db->fetchOne($select);
        if(!$deliver_id) $this->_db->insert('push_delivered_message', $datas);
    }

    public function markAsRead($device_uid) {

        $deliver_ids = array();

        $select = $this->_db->select()
            ->from(array('pdm' => 'push_delivered_message'), array('deliver_id'))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id")
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.is_read = 0')
            ->where('pdm.status = 1')
        ;
        $deliver_ids = $this->_db->fetchCol($select);

        if(!empty($deliver_ids)) {
            $this->_db->update('push_delivered_message', array('is_read' => 1), array('deliver_id IN (?)' => $deliver_ids));
        }

        return $this;
    }

    public function findByDeviceId($device_uid) {

        $cols = array_keys($this->_db->describeTable($this->_name));
        $cols = array_combine($cols, $cols);
        unset($cols['delivered_at']);
        $select = $this->select()
            ->from(array('pdm' => 'push_delivered_message'), array('is_read', 'delivered_at'))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id", $cols)
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.status = 1')
            ->where('pdm.is_displayed = ?', '1')
            ->limit(10)
            ->order('pdm.delivered_at DESC')
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);

    }

    public function markAsDisplayed($device_uid, $message_id) {

        $deliver_ids = array();

        $select = $this->_db->select()
            ->from(array('pdm' => 'push_delivered_message'))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id")
            ->where('pdm.device_id = ?', $device_uid)
            ->where('pdm.message_id = ?', $message_id)
        ;
        $deliver_ids = $this->_db->fetchCol($select);

        if(!empty($deliver_ids)) {
            $this->_db->update('push_delivered_message', array('is_displayed' => 1), array('deliver_id IN (?)' => $deliver_ids));
        }

        return $this;
    }

    public function countByDeviceId($device_uid) {

        $select = $this->_db->select()
            ->from(array('pdm' => 'push_delivered_message'), array('count' => new Zend_Db_Expr('COUNT(pdm.message_id)')))
            ->join(array('pm' => $this->_name), "pm.message_id = pdm.message_id")
            ->where('pdm.device_uid = ?', $device_uid)
            ->where('pdm.status = 1')
            ->where('pdm.is_displayed = ?', '1')
            ->where('pdm.is_read = 0')
        ;
        
        return (int) $this->_db->fetchOne($select);

    }

}
