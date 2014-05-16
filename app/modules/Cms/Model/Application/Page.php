<?php

class Cms_Model_Application_Page extends Core_Model_Default
{

    protected $_blocks;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page';
        return $this;
    }

    public function findByUrl($url) {
        $this->find($url, 'url');
        return $this;
    }

    public function getBlocks() {

        if(is_null($this->_blocks) AND $this->getId()) {
            $block = new Cms_Model_Application_Block();
            $this->_blocks = $block->findByPage($this->getId());
        }
        else {
            $this->_blocks = array();
        }

        return $this->_blocks;
    }

    public function save() {
        parent::save();

        $blocks = $this->getData('block') ? $this->getData('block') : array();
//        if($this->getData('block')) {
        $this->getTable()->saveBlock($this->getId(), $blocks);
//        }

    }
    
}
