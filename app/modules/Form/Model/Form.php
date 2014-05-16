<?php

class Form_Model_Form extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Form_Model_Db_Table_Form';
        return $this;
    }
    
    /**
     * Recherche par value_id
     * 
     * @param int $value_id
     * @return object
     */
    public function findByValueId($value_id) {
        return $this->getTable()->findByValueId($value_id);
    }

}
