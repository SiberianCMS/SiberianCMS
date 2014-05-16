<?php

class Media_Model_Library extends Core_Model_Default {

    protected $_images;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Library';
        return $this;
    }

    public function getImages() {

        if(empty($this->_images)) {
            $this->_images = array();
            $image = new Media_Model_Library_Image();
            if($this->getId()) {
                $this->_images = $image->findAll(array('library_id = ?' => $this->getId()), array('image_id ASC', 'can_be_colorized DESC'));
            }
        }

        return $this->_images;

    }

    /**
     * @alias $this->getImages();
     */
    public function getIcons() {
        return $this->getImages();
    }

}
