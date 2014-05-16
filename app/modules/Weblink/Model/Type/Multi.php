<?php
class Weblink_Model_Type_Multi extends Weblink_Model_Weblink {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_type_id = 2;
        return $this;
    }

    public function addLinks() {
        $link = new Weblink_Model_Weblink_Link();
        $links = $link->findAll(array('weblink_id' => $this->getId()));
        $this->setLinks($links);
        return $this;
    }

    public function getCoverUrl() {
        $cover_path = Application_Model_Application::getImagePath().$this->getCover();
        $cover_base_path = Application_Model_Application::getBaseImagePath().$this->getCover();
        if($this->getCover() AND file_exists($cover_base_path)) {
            return $cover_path;
        }
        return null;
    }
}
