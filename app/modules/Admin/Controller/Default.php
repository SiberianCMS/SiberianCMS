<?php

class Admin_Controller_Default extends Core_Controller_Default {

    public function init() {
        parent::init();

        if(!$this->getSession()->isLoggedIn('admin')
            AND !preg_match('/(login)|(forgotpassword)|(change)|(map)|(signuppost)|(check)/', $this->getRequest()->getActionName())
            AND !$this->getRequest()->isInstalling()
            ) {
            $this->_forward('login', 'account', 'admin');
            return $this;
        }

    }

    protected function _sendHtml($html) {
        if(!empty($html['error'])) {
            $this->getResponse()->setHttpResponseCode(400);
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}
