<?php

class Place_Model_Place extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Place_Model_Db_Table_Place';
        return $this;
    }

}
