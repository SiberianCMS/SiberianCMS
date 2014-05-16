<?php
class Weblink_Model_Type_Mono extends Weblink_Model_Weblink {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_type_id = 1;
        return $this;
    }

    public function save() {

        if(!$this->getId()) $this->setTypeId($this->_type_id);
        parent::save();
        if(!$this->getIsDeleted()) {
            if(!$this->getLink()->getId()) $this->getLink()->setWeblinkId($this->getId());
            $this->getLink()->save();
        }
        return $this;
    }

    public function addLinks() {
        $link = new Weblink_Model_Weblink_Link();
        if($this->getId()) {
            $link->find($this->getId(), 'weblink_id');
        }
        $this->setLink($link);
        return $this;
    }

//    public function getLinkDatas() {
//        return array(
//            'link' => $this->getLink()
//        );
//    }

}
