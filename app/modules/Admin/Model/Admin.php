<?php

class Admin_Model_Admin extends Core_Model_Default
{

    const LOGO_PATH = '/images/admin';

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Admin_Model_Db_Table_Admin';
    }

    public function findByEmail($email) {
        return $this->find($email, 'email');
    }

    public function setPassword($password) {
        if(strlen($password) < 6) throw new Exception($this->_('The password must be at least 6 characters'));
        $this->setData('password', $this->_encrypt($password));
        return $this;
    }

    public function isSamePassword($password) {
        return $this->getPassword() == $this->_encrypt($password);
    }

    public function authenticate($password) {
        return $this->_checkPassword($password);
    }

    public static function getLogoPathTo($path = '') {
        return Core_Model_Directory::getPathTo(self::LOGO_PATH.$path);
    }

    public static function getBaseLogoPathTo($path = '') {
        return Core_Model_Directory::getBasePathTo(self::LOGO_PATH.$path);
    }

    public static function getNoLogo($base = false) {
        return $base ? self::getBaseLogoPathTo('placeholder/no-logo.png') : self::getLogoPathTo('placeholder/no-logo.png');
    }

    public function getLogoLink() {
        if($this->getData('logo') AND is_file(self::getBaseLogoPathTo($this->getData('logo')))) {
            return self::getLogoPath($this->getData('logo'));
        }
        else {
            return self::getNoLogo();
        }

    }

    public function getLogoUrl() {
        return $this->getBaseUrl().$this->getLogoLink();
    }

    public function getBaseLogoLink() {
        if($this->getData('logo') AND is_file(self::getBaseLogoPathTo($this->getLogo()))) return self::getBaseLogoPathTo($this->getLogo());
        else return self::getNoLogo(true);
    }

    private function _encrypt($password) {
        return sha1($password);
    }

    private function _checkPassword($password) {
        return $this->getPassword() == $this->_encrypt($password);
    }

}