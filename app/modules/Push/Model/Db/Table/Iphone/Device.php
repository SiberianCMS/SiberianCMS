<?php

class Push_Model_Db_Table_Iphone_Device extends Core_Model_Db_Table {

    protected $_name = "push_apns_devices";
    protected $_primary = "device_id";

//    public function findByAppId($app_id) {
//        $device_type = Push_Model_Iphone_Device::DEVICE_TYPE;
//
//        $select = $this->select()
//            ->from(array('pad' => $this->_name))
//            ->joinLeft(array('_table_is_read' => 'push_delivered_message'), '_table_is_read.device_id = pad.device_id AND _table_is_read.status = 1 AND _table_is_read.is_read = 0', array('not_read' => new Zend_Db_Expr('COUNT(_table_is_read.deliver_id)')))
//            ->where('pad.app_id = ?', $app_id)
//            ->where('pad.status = ?', 'active')
//            ->where('(pad.push_badge = "enabled" OR pad.push_alert = "enabled" OR pad.push_sound = "enabled")')
//            ->group('pad.device_id')
//            ->order('')
//            ->setIntegrityCheck(false)
//        ;
//        return $this->fetchAll($select);
//    }

    public function countUnreadMessages($device_uid) {

        $device_type = Push_Model_Iphone_Device::DEVICE_TYPE;

        $select = $this->_db->select()
            ->from(array('pm' => 'push_messages'), array('not_read' => new Zend_Db_Expr('COUNT(pad.deliver_id)')))
            ->join(array('pad' => 'push_delivered_message'), 'pad.message_id = pm.message_id AND pad.is_read = 0', array())
            ->where('pad.device_uid = ?', $device_uid)
            ->group('pad.device_uid')
        ;

        return $this->_db->fetchOne($select);
    }

    public function findNotReceivedMessages($device_uid, $geolocated) {

        $created_at = $this->_db->fetchOne($this->_db->select()->from('push_apns_devices', array('created_at'))->where('device_uid = ?', $device_uid));

        $join = join(' AND ', array(
            'pdm.message_id = pm.message_id',
            $this->_db->quoteInto('pdm.device_uid = ?', $device_uid)
        ));

        $select = $this->_db->select()
            ->from(array('pm' => 'push_messages'), array('pm.message_id'))
            ->joinLeft(array('pdm' => 'push_delivered_message'), $join, array())
            ->where('pm.created_at >= ?', $created_at)
            ->where('pdm.message_id IS NULL')
            ->where('pm.status = ?', 'delivered')
        ;

        if($geolocated === true) $select->where('pm.latitude IS NOT NULL')->where('pm.longitude IS NOT NULL')->where('pm.radius IS NOT NULL');
        else if($geolocated === false) $select->where('pm.latitude IS NULL')->where('pm.longitude IS NULL')->where('pm.radius IS NULL');

        return $this->_db->fetchCol($select);

    }

    public function hasReceivedThisMessage($device_id, $message_id) {

        $select = $this->_db->select()
            ->from(array('pm' => 'push_messages'), array())
            ->join(array('pdm' => 'push_delivered_message'), 'pdm.app_id = pm.app_id', array('pm.message_id'))
            ->where('pm.message_id = ?', $message_id)
            ->where('pdm.device_type = ?', Push_Model_Iphone_Device::DEVICE_TYPE)
        ;
        Zend_Debug::dump($this->_db->fetchOne($select));
        die;
        return $this->_db->fetchOne($select);

    }

}
