<?php

class Core_Model_Default
{
    protected $_db_table;
    protected static $_current_app;
    protected static $_base_url;

    protected $_data = array();

    public function __construct($datas = array()) {
        foreach($datas as $key => $data) {
            if(!is_numeric($key)) {
                $this->setData($key, $data);
            }
        }
        return $this;
    }

    public function __call($method, $args)
    {
        $accessor = substr($method, 0, 3);
        $magicKeys = array('set', 'get', 'uns', 'has');

        if(substr($method, 0, 12) == 'getFormatted') {
            $key = Core_Model_Lib_String::camelize(substr($method,12));
            $data = $this->getData($key);

            if(preg_match('/^\s*([0-9]+(\.[0-9]+)?)\s*$/', $data)) {
                return $this->formatPrice($data, !empty($args[0]) ? $args[0] : null);
            }
//            elseif(preg_match('/(\d){2,4}\-(\d){2}\-(\d){2} (\d{2}:\d{2}:\d{2})/', $data)) {
            elseif(preg_match('/(\d){2,4}\-(\d){2}\-(\d){2}/', $data)) {
                return $this->formatDate($data, !empty($args[0]) ? $args[0] : null);
            }
        }
        if(in_array($accessor, $magicKeys)) {
            $key = Core_Model_Lib_String::camelize(substr($method,3));
            $method = $accessor.'Data';
            $value = isset($args[0]) ? $args[0] : null;
            return call_user_func(array($this, $method), $key, $value);
        }

        throw new Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
    }

    public function getTable() {
        if(!is_null($this->_db_table)) {
            if(is_string($this->_db_table))
                return new $this->_db_table(array('modelClass' => get_class($this)));
            else
                return $this->_db_table;
        }

        return null;
    }

    public function getFields() {
        return $this->getTable()->getFields();
    }

    public function hasTable() {
        return !is_null($this->_db_table);
    }

    public function find($id, $field = null) {
        if(!$this->hasTable()) return null;

        if(is_array($id)) {
            $row = $this->getTable()->findByArray($id);
        }
        elseif(is_null($field))
            $row = $this->getTable()->findById($id);
        else
            $row = $this->getTable()->findByField($id, $field);

        $this->_prepareDatas($row);

        return $this;
    }

    public function findLast($id, $field = null) {
        if(!$this->hasTable()) return null;

        if(is_array($id)) {
            $row = $this->getTable()->findLastByArray($id);
        }
        elseif(is_null($field))
            $row = $this->getTable()->findById($id);
        else
            $row = $this->getTable()->findLastByField($id, $field);

        $this->_prepareDatas($row);

        return $this;
    }


    public function addData($key, $value=null)
    {
        if(is_array($key)) {
            $values = $key;
            foreach($values as $key => $value) {
                $this->setData($key, $value);
            }
        }
        else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    public function setData($key, $value=null) {
        if(is_array($key)) {
            if(isset($this->_data['id'])) {
                $key['id'] = $this->_data['id'];
            }
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    public function unsData($key=null)
    {
        if (is_null($key)) {
            $this->_data = array();
        } else {
            unset($this->_data[$key]);
        }
        return $this;
    }

    public function getData($key='')
    {
        if ($key==='') {
            return $this->_data;
        }
        elseif(isset($this->_data[$key]) AND !is_null($this->_data[$key])) {
            return is_string($this->_data[$key]) ? stripslashes($this->_data[$key]) : $this->_data[$key];
        }
        else {
            return null;
        }
    }

    public function hasData($key) {
        return isset($this->_data[$key]);
    }

    public function isActive() {
        return true;
    }

    public function isEmpty() {
        return empty($this->_data);
    }

    public function getApplication() {
        return Application_Model_Application::getInstance();
    }

    public function prepareFeature($option_value) {
        return $this;
    }

    public function setId($id) {
        if($this->hasTable()) {
            $this->setData($this->getTable()->getPrimaryKey(), $id)
                ->setData('id', $id)
            ;
        } else {
            $this->addData('id', $id);
        }

        return $this;
    }

    public function findAll($values = array(), $order = null, $params = array()) {
        return $this->getTable()->findAll($values, $order, $params);
    }

    public function countAll($values = array()) {
        return $this->getTable()->countAll($values);
    }

    public function save() {
        if($this->_canSave()) {

            if($this->getData('is_deleted') == 1) {
                $this->delete();
            }
            else {
                $row = $this->_createRow();
                $row->save();

                $this->addData($row->getData())->setId($row->getId());

            }
        }

        return $this;
    }

    public function reload() {
        $id = $this->getId();
        $this->unsData();
        if($id) {
            $this->find($id);
        }

        return $this;
    }

    public function delete() {
        if($row = $this->_createRow() AND $row->getId()) {
            $row->delete();
            $this->unsData();
        }
        return $this;
    }

    public function isProduction() {
        return APPLICATION_ENV == 'production';
    }

    public function _($text) {
        $args = func_get_args();
        return Core_Model_Translator::translate($text, $args);
    }

    public function getUrl($url = '', array $params = array(), $locale = null) {
        return Core_Model_Url::create($url, $params, $locale);
    }

    public function getPath($uri = '', array $params = array()) {
        return Core_Model_Url::createPath($uri, $params);
    }

    public function getCurrentUrl($withParams = true, $locale = null) {
        return Core_Model_Url::current($withParams, $locale);
    }

    public static function setCurrentApp($application) {
        self::$_current_app = $application;
    }
//
//    public function getCurrentApplication() {
//        return self::$_current_app;
//    }

    public static function setBaseUrl($url) {
        self::$_base_url = $url;
    }

    public function getBaseUrl() {
        return self::$_base_url;
    }

    public function toJSON() {

        $datas = $this->getData();
        if(isset($datas['password'])) unset($datas['password']);
        if(isset($datas['created_at'])) unset($datas['created_at']);
        if(isset($datas['updated_at'])) unset($datas['updated_at']);

        return Zend_Json::encode($datas);
    }

    protected function _canSave() {
        if($this->getTable()) {
            return true;
        }
        return false;
    }

    protected function _createRow() {
        $row = $this->getTable()->createRow(); //new $this->_row(array('table' => new $this->_db_table()));
        $row->setData($this->getData());
        return $row;
    }

    public function __toString() {
        return $this->getData();
    }

    public function formatDate($date = null, $format = 'y-MM-dd') {
        $date = new Zend_Date($date, 'y-MM-dd HH:mm:ss');
        return $date->toString($format);
    }

    public function formatPrice($price, $currency = null) {
        $price = preg_replace(array('/(,)/', '/[^0-9.]/'), array('.', ''), $price);

        if($currency) $currency = new Zend_Currency($currency);
        else $currency = Core_Model_Language::getCurrentCurrency();

        return $currency->toCurrency($price);
    }

    public function getMediaUrl($params = null) {
        return $this->getBaseUrl() . '/images/'.$params;
    }

    protected function _prepareDatas($row) {

        $this->uns();

        if($row) {
            $this->setData($row->getData());
            $this->setId($row->getId());
        }
    }

}
