<?php

class Core_Model_Session extends Siberian_Session_Namespace
{

    const TYPE_ADMIN = 'admin';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_MCOMMERCE = 'mcommerce';

    protected $_types = array();
    protected $_instanceSingleton = array();
    protected $_store = null;
    protected $_cart = null;

    public function addType($type, $class) {
        $this->_types[$type] = $class;
    }

    public function getTypes() {
        return array(
            self::TYPE_ADMIN,
            self::TYPE_CUSTOMER,
            self::TYPE_MCOMMERCE,
        );
    }

    public function resetInstance() {
        $this->_instanceSingleton = array();
        $this->current_type = null;
        $this->object_id = null;
        return $this;
    }

    public function prepare($types) {
        foreach($types as $type => $class) {
            $this->addType($type, $class);
        }

        return $this;
    }

    public function getInstance() {

//        if(!$this->current_type) return false;
        if(!in_array($this->current_type, $this->getTypes())) return false;

        if(!isset($this->_instanceSingleton[$this->current_type])) {
            $params['id'] = $this->object_id;
            $this->_instanceSingleton[$this->current_type] = new $this->_types[$this->current_type]($params);
        }

        return $this->_instanceSingleton[$this->current_type];
    }

    public function setCurrentType($currentType) {
        $this->current_type = $currentType;
        return $this;
    }

    public function getAccountUri() {
        if($this->getInstance()) {
            return $this->getInstance()->getAccountUri();
        }
    }

    public function getLogoutUri() {
        if($this->getInstance()) {
            return $this->getInstance()->getLogoutUri();
        }
    }

    public function isLoggedIn($type = null) {
        if((is_null($type) OR $type == $this->loggedAs()) AND $this->getInstance()) {
            return $this->getInstance()->isLoggedIn();
        }

        return false;
    }

    public function loggedAs() {
        return $this->current_type;
    }

    public function addSuccess($msg, $key = '') {
        $messages = $this->getMessages(false);
        $messages->addSuccess($msg, $key);
        $this->messages = $messages;
        return $this;
    }

    public function addWarning($msg, $key = '') {
        $messages = $this->getMessages(false);
        $messages->addWarning($msg, $key);
        $this->messages = $messages;
        return $this;
    }

    public function removeWarning($key) {
        $messages = $this->getMessages(false);
        $messages->removeWarning($key);
        $this->messages = $messages;
        return $this;
    }

    public function addError($msg, $key = '') {
        $messages = $this->getMessages(false);
        $messages->addError($msg, $key);
        $this->messages = $messages;
        return $this;
    }

    public function getMessages($reset = true) {
        if(!$this->messages instanceof Core_Model_Session_Messages) $this->messages = new Core_Model_Session_Messages();
        $messages = $this->messages;
        if($reset) {
            $this->messages = null;
        }
        return $messages;
    }

    public function getStore() {
        if(!$this->_store) {
            $this->_store = new Mcommerce_Model_Store();
            if($this->store_id) {
                $this->_store->find($this->store_id);
            }
        }
        return $this->_store;
    }

    public function setStore($store) {
        $this->store_id = $store->getId();
        return $this;
    }

    public function getCart() {
        if(!$this->_cart) {
            $this->_cart = new Mcommerce_Model_Cart();
            if($this->cart_id) {
                $this->_cart->find($this->cart_id);
            }
        }
        return $this->_cart;
    }

    public function setCart($cart) {
        $this->cart_id = $cart->getId();
        return $this;
    }

    public function unsetCart() {
        $this->cart_id = null;
        return $this;
    }

    public function getCustomer() {
        if($this->getInstance() AND $this->loggedAs() == self::TYPE_CUSTOMER) {
            return $this->getInstance()->getObject();
        }
        return new Customer_Model_Customer();
    }

    public function setCustomer($customer) {
        $this->setCurrentType(self::TYPE_CUSTOMER)
            ->setCustomerId($customer->getId())
        ;
        return $this->getInstance()->setObject($customer);
    }

    public function getCustomerId() {
        $id = null;
        if($this->loggedAs() == self::TYPE_CUSTOMER) $id = $this->object_id;
        return $id;
    }

    public function setCustomerId($id) {
        $this->object_id = $id;
    }

    public function getAdmin() {
        if($this->getInstance() AND $this->loggedAs() == self::TYPE_ADMIN) {
            return $this->getInstance()->getObject();
        }
        return new Admin_Model_Admin();
    }

    public function setAdmin($admin) {
        $this->resetInstance()
            ->setCurrentType(self::TYPE_ADMIN)
            ->setAdminId($admin->getId())
        ;
        $this->getInstance()->setObject($admin);
        return $this;
    }

    public function getAdminId() {
        $id = null;
        if($this->loggedAs() == self::TYPE_ADMIN) $id = $this->object_id;
        return $id;
    }

    public function setAdminId($id) {
        $this->object_id = $id;
    }

}
