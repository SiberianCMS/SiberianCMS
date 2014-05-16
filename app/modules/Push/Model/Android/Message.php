<?php

/**
 * @category Android Push Notification Service using PHP & MySQL
 * @author Peter Schmalfeldt <manifestinteractive@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link http://code.google.com/p/easyapns/
 */
class Push_Model_Android_Message extends Core_Model_Default {

    /**
     * Android GCM Key
     *
     * @var string
     * @access private
     */
    private $__key;

    /**
     * Android GCM URL
     *
     * @var string
     * @access private
     */
    private $__url = 'https://android.googleapis.com/gcm/send';

    /**
     * Message to push to user
     *
     * @var Push_Model_Message
     * @access protected
     */
    protected $_message;

    /**
     * Constructor.
     *
     * @param array $params
     * @access 	public
     */
    function __construct($params = array()) {
        parent::__construct($params);
        $this->__key = Push_Model_Certificat::getAndroidKey();
    }

    public function setMessage($message) {
        $this->_message = $message;
        return $this;
    }

    public function getMessage() {
        return $this->_message;
    }

    /**
     * Push GCm Messages
     *
     * This gets called automatically by _fetchMessages.  This is what actually deliveres the message.
     *
     * @access public
     */
    public function push() {
        $device = new Push_Model_Android_Device();
        $devices = $device->findAll();

        foreach($devices as $device) {
            try {
                $this->sendMessage($device);
            } catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }

    }

    /**
     * Send a message to a single device
     * @param type $device
     * @return \Push_Model_Android_Message
     * @throws Exception
     */
    public function sendMessage($device) {

        $error = false;

        $registration_ids = array();
        $registration_ids[] = $device->getRegistrationId();

        //TTL 604800 = 1 semaine

        try {
            $status = 0;
            $fields = array(
                'registration_ids' => $registration_ids,
                'data' => array(
                    'time_to_live' => 0,
                    'delay_while_idle' => false,
                    'message' => $this->getMessage()->getText(),
                    'latitude' => $this->getMessage()->getLatitude(),
                    'longitude' => $this->getMessage()->getLongitude(),
                    'radius' => $this->getMessage()->getRadius(),
                    'message_id' => $this->getMessage()->getMessageId(),
                ),
            );

            $headers = array(
                'Authorization: key=' . $this->__key,
                'Content-Type: application/json'
            );

            // Open connection
            $ch = curl_init();

            // Set the url, number of POST vars, POST data
            curl_setopt($ch, CURLOPT_URL, $this->__url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Disabling SSL Certificate support temporarly
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch, CURLOPT_POSTFIELDS, Zend_Json::encode($fields));

            // Execute post
            $result = curl_exec($ch);
            if ($result === FALSE) {
//                $this->getMessage()->updateStatus('failed');
                $errors = $this->getErrors();
                if(empty($errors)) $errors = array();
                $errors[$device->getId()] = $e;
                $this->setErrors($errors);
                $error = true;
            }
            else {
                $status = 1;
            }

            // Close connection
            curl_close($ch);

        }
        catch(Exception $e) {
            $errors = $this->getErrors();
            if(empty($errors)) $errors = array();
            $errors[$device->getId()] = $e;
            $this->setErrors($errors);
            $error = true;
        }

        if(!$error) {
            $this->getMessage()->createLog($device, $status, $device->getRegistrationId());
        }

    }


}
