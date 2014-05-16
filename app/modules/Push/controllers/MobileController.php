<?php

class Push_MobileController extends Application_Controller_Mobile_Default {

    public function listAction() {

        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array();
        $device_uid = $this->_getDeviceUid();
        $messages = array();

        if($device_uid) {
            $message = new Push_Model_Message();
            $messages = $message->findByDeviceId($device_uid);
            $message->markAsRead($device_uid);
        }

        $this->getLayout()->getPartial('content')->setNotifs($messages);
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function countAction() {

        $html = array();
        $device_uid = $this->_getDeviceUid();
        $nbr = 0;
        if($device_uid) {
            $message = new Push_Model_Message();
            $nbr = $message->countByDeviceId($device_uid);
        }

        $html = array('count' => $nbr);
        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

    protected function _getDeviceUid() {

        $id = null;
        if($device_uid = $this->getRequest()->getParam('device_uid')) {
            if(!empty($device_uid)) {
                if(strlen($device_uid) == 36) {
                    $device = new Push_Model_Iphone_Device();
                    $device->find($device_uid, 'device_uid');
                    $id = $device->getDeviceUid();
                }
                else {
                    $device = new Push_Model_Android_Device();
                    $device->find($device_uid, 'registration_id');
                    $id = $device->getRegistrationId();
                }
            }

        }

        return $id;

    }

}