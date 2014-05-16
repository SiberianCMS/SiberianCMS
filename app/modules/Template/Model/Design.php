<?php

class Template_Model_Design extends Core_Model_Default {

    const PATH_IMAGE = '/images/templates';

    protected $_blocks;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Template_Model_Db_Table_Design';
        return $this;
    }

    public function getBlocks() {

        if(!$this->_blocks) {
            $block = new Template_Model_Block();
            $this->_blocks = $block->findByDesign($this->getId());
        }

        return $this->_blocks;

    }

    public function getBlock($name) {

        foreach($this->getBlocks() as $block) {
            if($block->getCode() == $name) return $block;
        }
        return new Template_Model_Block();

    }

    public function getOverview() {
        return Core_Model_Directory::getPathTo(self::PATH_IMAGE.$this->getData('overview'));
    }

    public function getBackgroundImage($base = false) {
        return $base ? Core_Model_Directory::getBasePathTo(self::PATH_IMAGE.$this->getData('background_image')) : Core_Model_Directory::getPathTo(self::PATH_IMAGE.$this->getData('background_image'));
    }

    public function getBackgroundImageRetina($base = false) {
        return $base ? Core_Model_Directory::getBasePathTo(self::PATH_IMAGE.$this->getData('background_image_retina')) : Core_Model_Directory::getPathTo(self::PATH_IMAGE.$this->getData('background_image_retina'));
    }

}
