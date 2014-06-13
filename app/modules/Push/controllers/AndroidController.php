<?php

class Push_AndroidController extends Core_Controller_Default
{

    /**
     * Register Device
     *
     */
    public function registerdeviceAction() {

        if($params = $this->getRequest()->getParams()) {

            if(!empty($params['registration_id'])) {
                $fields = array(
                    'app_name',
                    'registration_id',
                );

                foreach($params as $key => $value) {
                    if(!in_array($key, $fields)) unset($params[$key]);
                }

                $params['development'] = 'production';
                $params['status'] = 'active';

                $device = new Push_Model_Android_Device();
                $device->find(array('registration_id' => $params['registration_id']));
                $device->addData($params)->save();

                die;
            }

        }

    }


    /**
     * Set this message as displayed
     */
    public function markdisplayedAction() {

        if($params = $this->getRequest()->getParams()) {

            if(empty($params['registration_id']) OR empty($params['message_id'])) return;
            die('ok');
            $device = new Push_Model_Android_Device();
            $device->findByRegistrationId($params['registration_id']);
            $message = new Push_Model_Message();
            $message->markAsDisplayed($device->getId(), $params['message_id']);

        }

        die;

    }

    /**
     * Update position of device and send pending messages for this zone
     * @return type
     */
    public function updatepositionAction() {

        if($params = $this->getRequest()->getParams()) {

            if(empty($params['latitude']) OR empty($params['longitude']) OR empty($params['registration_id'])) return;

            $device = new Push_Model_Android_Device();
            $device->findByRegistrationId($params['registration_id']);

            $messages = $device->findNotReceivedMessages();

            if($messages->count() > 0) {
                foreach($messages as $message) {
                    $instance = $message->getInstance('android');
                    $instance->setMessage($message);
                    $instance->sendMessage($device);
                }
            }

            die;

        }

    }
}