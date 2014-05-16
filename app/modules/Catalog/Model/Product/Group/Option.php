<?php

class Catalog_Model_Product_Group_Option extends Core_Model_Default {

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Product_Group_Option';
    }

}