<?php

class Core_Controller_Webservice_Default extends Zend_Controller_Action
{

    protected $_current_app;
    protected $_current_customer;
    protected $_current_device_id;

    public function init() {

        parent::init();

        $this->getResponse()->setHeader('Content-Type', 'application/xml; charset="UTF-8"');

        if(preg_match('/(uploadimage)/', $this->getRequest()->getActionName())) {
            return $this;
        }

        $this->_current_device_id = $this->getRequest()->getParam('device_id');
        $this->_current_app = Application_Model_Application::getInstance();

        if($customer_id = $this->getRequest()->getParam('customer_id')) {
            $customer->find($customer_id);
            if(!$customer->getId()) $error = true;
            else $this->_current_customer = $customer;
        }
        else {
            $this->_current_customer = $customer;
        }

        if(!$customer->getId()) {
            $error = true;
        }

        if($error) {
            $this->_sendError();
        }

    }

    public function _($text) {
        $args = func_get_args();
        return Core_Model_Translator::translate($text, $args);
    }

    protected function _sendError($message = '') {

        if(empty($message)) $message = $this->_('An error occurred while loading. Please try again later.');

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>
            <error>
                <message>'.$message.'</message>
            </error>
        ';

        $this->getLayout()->setHtml($xml);
    }

}