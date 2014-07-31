<?php

class Form_Model_Form extends Core_Model_Default {

    protected $_sections;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Form_Model_Db_Table_Form';
        return $this;
    }

    public function getSections() {

        if(!$this->_sections) {
            $section = new Form_Model_Section();
            $this->_sections = $section->findAll(array('value_id' => $this->getValueId()));
        }

        return $this->_sections;

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
