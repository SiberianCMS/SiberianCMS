<?php

class Application_Model_Option_Value extends Application_Model_Option
{

    protected $_background_image_url;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Application_Model_Db_Table_Option_Value';
    }

    public function find($id, $field = null) {
        parent::find($id, $field);
        $this->addOptionDatas();
        $this->prepareUri();
        return $this;
    }

    public function findFolderValues($params) {
        $folderValues = $this->getTable()->getFolderValues($params['option_id']);
        return $folderValues;
    }

    public function save() {

        if(!$this->getId()) {
            $this->setNextPosition();
            $this->setLayoutId(1)->setIsActive(1);
        }
        parent::save();

        return $this;
    }

    public function isActive() {
        return $this->getIsActive();
    }

    public function getLibrary() {
        if(!$this->getLibraryId()) {
            $this->_findLibraryId();
        }
        return parent::getLibrary();
    }

    public function getLibraryId() {
        if(!$this->getData('library_id')) {
            $this->_findLibraryId();
        }
        return $this->getData('library_id');
    }

    public function getBackgroundImageUrl() {

        if(!$this->_background_image_url) {

            if($this->getBackgroundImage()) {
                $this->_background_image_url = Application_Model_Application::getImagePath().$this->getBackgroundImage();
            }

        }

        return $this->_background_image_url;

    }

    public function addOptionDatas() {
        if(is_numeric($this->getId())) {
            $datas = $this->getTable()->getOptionDatas($this->getOptionId());
            foreach($datas as $key => $value) {
                if(is_null($this->getData($key))) $this->setData($key, $value);
            }

        }
        return $this;
    }

    protected function _findLibraryId() {

        $library_id = $this->getTable()->findLibraryId($this->getOptionId());
        $this->setLibraryId($library_id);

        return $this;
    }

    protected function setNextPosition() {
        $lastPosition = $this->getTable()->getLastPosition();
        if(!$lastPosition) $lastPosition = 0;
        $this->setPosition(++$lastPosition);

        return $this;
    }

    public function getNextFolderCategoryPosition($category_id) {
        $lastPosition = $this->getTable()->getLastFolderCategoryPosition($category_id);
        if(!$lastPosition) $lastPosition = 0;

        return ++$lastPosition;
    }

}
