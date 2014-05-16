<?php

require_once 'Connect/facebook.php';

class Core_Model_Lib_Facebook extends Core_Model_Default {

    protected $_facebook;

    public function __construct($params = array()) {
        parent::__construct($params);

        $config = array(
            'appId' => self::getAppId(),
            'secret' => self::getSecretKey(),
        );

        $this->_facebook = new Facebook($config);

        return $this;
    }

    public static function getAppId() {
        return Api_Model_Key::findKeysFor('facebook')->getAppId();
    }

    public static function getSecretKey() {
        return Api_Model_Key::findKeysFor('facebook')->getSecretKey();
    }

}