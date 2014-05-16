<?php

class Push_IphoneController extends Core_Controller_Default
{

    /**
     * Register Device
     *
     */
    public function registerdeviceAction() {

        if($params = $this->getRequest()->getParams()) {

            $fields = array(
                'app_name',
                'app_version',
                'device_uid',
                'device_token',
                'device_name',
                'device_model',
                'device_version',
                'push_badge',
                'push_alert',
                'push_sound',
            );

            foreach($params as $key => $value) {
                if(!in_array($key, $fields)) unset($params[$key]);
            }

            $params['status'] = 'active';

            $device = new Push_Model_Iphone_Device();
            $device->find($params['device_uid'], 'device_uid');
            $device->addData($params)->save();

            $message = new Push_Model_Message();
            
            $this->getLayout()->setHtml($message->countByDeviceId($device->getDeviceUid()));
        }

    }

    public function updatepositionAction() {

        if($params = $this->getRequest()->getPost()) {

            if(empty($params['latitude']) OR empty($params['longitude']) OR empty($params['device_uid'])) return;

            $device = new Push_Model_Iphone_Device();
            $device->find($params['device_uid'], 'device_uid');
            if(!$device->getId()) {
                $device->setDeviceUid($params['device_uid']);
            }

            $device->setLastKnownLatitude($params['latitude'])
                ->setLastKnownLongitude($params['longitude'])
                ->save()
            ;

            $messages = $device->findNotReceivedMessages();

            if($messages->count() > 0) {
                foreach($messages as $message) {
                    $instance = $message->getInstance('iphone');
                    $instance->setMessage($message);
                    if($instance->isInsideRadius($device->getLastKnownLatitude(), $device->getLastKnownLongitude())) {
                        $instance->isDev($isDev)
                            ->sendMessage($device)
                        ;
                    }
                }
            }

            die('done');

        }

    }

}