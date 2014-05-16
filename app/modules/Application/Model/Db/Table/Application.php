<?php

class Application_Model_Db_Table_Application extends Core_Model_Db_Table
{
    protected $_name    =   "application";
    protected $_primary =   "app_id";

    public function findByHost($domain) {
        return $this->fetchRow($this->_db->quoteInto('domain = ?', $domain));
    }

    public function updateOptionValuesPosition($positions) {

        foreach($positions as $pos => $option_value_id) {
            $this->_db->update($this->_name.'_option_value', array('position' => $pos), array('value_id = ?' => $option_value_id));
        }

    }

}
