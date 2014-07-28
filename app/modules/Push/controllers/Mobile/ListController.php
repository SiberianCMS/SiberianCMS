<?php

class Push_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $data = array("notifs" => array());
        $option = $this->getCurrentOptionValue();
        $color = $this->getApplication()->getBlock('background')->getColor();

        if($device_uid = $this->getRequest()->getParam('device_uid')) {

            $message = new Push_Model_Message();
            $messages = $message->findByDeviceId($device_uid);
            $icon_new = $option->getImage()->getCanBeColorized() ? $this->_getColorizedImage($option->getIconId(), $color) : $option->getIconUrl();
            $icon_pencil = $this->_getColorizedImage($this->_getImage("pictos/pencil.png"), $color);

            foreach($messages as $message) {

                $meta = array(
                    "area1" => array(
                        "picto" => $icon_pencil,
                        "text" => $message->getCreatedAt()
                    )
                );

                if(!$message->getIsRead()) {
                    $meta["area3"] = array(
                        "picto" => $icon_new,
                        "text" => $this->_("New")
                    );
                }

                $data["notifs"][] = array(
                    "id" => $message->getId(),
                    "author" => $message->getTitle(),
                    "message" => $message->getText(),
                    "meta" => $meta
                );
            }

        }

        $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();

        $message->markAsRead($device_uid);

        $this->_sendHtml($data);

    }

    public function countAction() {

        $nbr = 0;
        if($device_uid = $this->getRequest()->getParam('device_uid')) {
            $message = new Push_Model_Message();
            $nbr = $message->countByDeviceId($device_uid);
        }

        $data = array('count' => $nbr);
        $this->_sendHtml($data);

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