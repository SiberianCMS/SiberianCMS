<?php

/**
 * @category Apple Push Notification Service using PHP & MySQL
 * @package APNS
 * @author Peter Schmalfeldt <manifestinteractive@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link http://code.google.com/p/easyapns/
 */
class Push_Model_Iphone_Message extends Core_Model_Default {

    /**
     * Apples Production APNS Feedback Service
     *
     * @var string
     * @access private
     */
    private $__feedback_url = 'ssl://feedback.push.apple.com:2196';
//    private $__feedback_url = 'ssl://feedback.sandbox.push.apple.com:2196';

    /**
     * Production Certificate Path
     *
     * @var string
     * @access private
     */
    private $__certificate = '';

    /**
     * Apples APNS Gateway
     *
     * @var string
     * @access private
     */
    private $__ssl_url = 'ssl://gateway.push.apple.com:2195';
//    private $__ssl_url = 'ssl://gateway.sandbox.push.apple.com:2195';

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->__certificate = Core_Model_Directory::getBasePathTo(Push_Model_Certificat::getiOSCertificat());
    }

    /**
     * Message to push to user
     *
     * @var Push_Model_Message
     * @access protected
     */
    protected $_message;

    public function setMessage($message) {
        $this->_message = $message;
        return $this;
    }

    public function getMessage() {
        return $this->_message;
    }

    public function push() {

        if($this->getErrors()) return $this;
        $device = new Push_Model_Iphone_Device();
        $devices = $device->findAll();
//        Zend_Debug::dump($devices);
//        die;
//        $device_type = Push_Model_Iphone_Device::DEVICE_TYPE;
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
        $errors = array();

        $is_geolocated = $this->getMessage()->getLatitude() && $this->getMessage()->getLongitude() && $this->getMessage()->getRadius();

        $error = false;
        foreach($devices as $device) {

            try {

                $canSendMessage = false;
                if($is_geolocated) {
                    if($device->getLastKnownLatitude() AND $device->getLastKnownLongitude()) {
                        $canSendMessage = $this->isInsideRadius($device->getLastKnownLatitude(), $device->getLastKnownLongitude());
                    }
                }
                else {
                    $canSendMessage = true;
                }

                if($canSendMessage) {
                    $this->sendMessage($device);
                }
            }
            catch(Exception $e) {
                $errors[$device->getId()] = $e;
            }

        }

        $this->setErrors($errors);

        return $this;

    }

    public function sendMessage($device) {

        $message = $this->_formatMessage($device, $this->getMessage()->getText());

        $token = $device->getDeviceToken();
        $error = false;
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->__certificate);
        $fp = stream_socket_client($this->__ssl_url, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);

        if (!$fp) {
            throw new Exception('');
        } else {

            $msg = chr(0) . pack("n", 32) . pack('H*', $token) . pack("n", strlen($message)) . $message;
            $fwrite = fwrite($fp, $msg);
            if (!$fwrite) {
                throw new Exception('');
            } else {
                $status = 1;
            }

            $this->getMessage()->createLog($device, $status);
        }
        fclose($fp);

        $this->_checkFeedback($device);

        return $this;
    }

    public function isInsideRadius($lat_a, $lon_a) {

        $radius = $this->getMessage()->getRadius() * 1000;
        $rad = pi() / 180;
        $lat_a = $lat_a * $rad;
        $lat_b = $this->getMessage()->getLatitude() * $rad;
        $lon_a = $lon_a * $rad;
        $lon_b = $this->getMessage()->getLongitude() * $rad;
        $distance = 2 * asin(sqrt(pow(sin(($lat_a-$lat_b)/2) , 2) + cos($lat_a)*cos($lat_b)* pow( sin(($lon_a-$lon_b)/2) , 2)));
        $distance *= 6371000;

        return $distance <= $radius;

    }

    /**
     * Fetch APNS Messages
     *
     * This gets called automatically by _pushMessage.  This will check with APNS for any invalid tokens and disable them from receiving further notifications.
     *
     * @param sting $development Which SSL to connect to, Sandbox or Production
     * @access private
     */
    private function _checkFeedback($device) {

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $this->__certificate);
        stream_context_set_option($ctx, 'ssl', 'verify_peer', false);
        $fp = stream_socket_client($this->__feedback_url, $error, $errorString, 60, STREAM_CLIENT_CONNECT, $ctx);

        if (!$fp) {
            throw new Exception('');
        }
        while ($devcon = fread($fp, 38)) {

            $arr = unpack("H*", $devcon);
            $rawhex = trim(implode("", $arr));
            $token = substr($rawhex, 12, 64);

            if (!empty($token)) {

                $device = new Push_Model_Iphone_Device();
                $device->findByToken($token);
                if($device->getId()) {
                    $device->unregister();
                }
            }

        }
        fclose($fp);
    }

    protected function _formatMessage($device, $message) {

        $aps = array('aps' => array());
        if($device->getPushAlert() == 'enabled') {
            $aps['aps']['alert'] =  array(
                'body' => $message,
                'action-loc-key' => 'Voir'
            );
        }
        if($device->getPushBadge() == 'enabled') {
            $aps['aps']['badge'] = $device->getNotRead() + 1;
        }
        if($device->getPushSound() == 'enabled') {
            $aps['aps']['sound'] = 'Submarine.aiff';
        }

        return Zend_Json::encode($aps);

    }

}
