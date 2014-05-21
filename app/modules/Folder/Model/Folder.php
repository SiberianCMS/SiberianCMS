<?php
class Folder_Model_Folder extends Core_Model_Default {

    protected $_root_category;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Folder_Model_Db_Table_Folder';
        return $this;
    }

    public function delete() {

        if(!$this->getId()) {
            return $this;
        }

        $this->getRootCategory()->delete();

        return parent::delete();
    }

    public function getRootCategory() {

        if(!$this->_root_category) {
            $this->_root_category = new Folder_Model_Category();
            $this->_root_category->find($this->getRootCategoryId());
        }

        return $this->_root_category;

    }

}
