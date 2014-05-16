<?php

class Application_Model_Option extends Core_Model_Default
{

    protected $_category_ids = array(
        1 => ''
    );
    protected $_object;
    protected $_library;
    protected $_layout;
    protected $_previews;
    protected $_image;
    protected $_icon_url;
    protected $_xml_is_loaded = false;
    protected $_xml = null;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Application_Model_Db_Table_Option';
    }

    public function prepareUri() {
        $this->setUri($this->getData(APPLICATION_TYPE.'_uri'));
        return $this;
    }

    public function find($id, $field = null) {
        if($id == 'customer_account') {
            $this->findTabbarAccount();
        }
        else if($id == 'more_items') {
            $this->findTabbarMore();
        }
        else {
            parent::find($id, $field);
        }

        $this->prepareUri();
        return $this;
    }

    public function findTabbarAccount() {
        $datas = array('option_id' => 'customer_account', 'value_id' => 'customer_account', 'code' => 'tabbar_account', 'name' => $this->getApplication()->getTabbarAccountName(), 'tabbar_name' => $this->getApplication()->getTabbarAccountName(), 'is_ajax' => 0, 'price' => 0.00, 'is_active' => 1);
        $datas['desktop_uri'] = 'application/customization_features_tabbar_account/';
        $this->setData($datas)->setId('customer_account');
        $this->setIconUrl(Media_Model_Library_Image::getImagePathTo('/tabbar/user_account.png'));
        $this->setBaseIconUrl(Media_Model_Library_Image::getBaseImagePathTo('/tabbar/user_account.png'));
        return $this;
    }

    public function findTabbarMore() {
        $datas = array('option_id' => 'more_items', 'value_id' => 'more_items', 'code' => 'tabbar_more', 'name' => $this->getApplication()->getTabbarMoreName(), 'tabbar_name' => $this->getApplication()->getTabbarMoreName(), 'is_ajax' => 0, 'price' => 0.00, 'is_active' => 1);
        $datas['desktop_uri'] = 'application/customization_features_tabbar_more/';
        $this->setData($datas)->setId('more_items');
        $this->setIconUrl(Media_Model_Library_Image::getImagePathTo('/tabbar/more_items.png'));
        $this->setBaseIconUrl(Media_Model_Library_Image::getBaseImagePathTo('/tabbar/more_items.png'));
        return $this;
    }


    public function delete() {
        if($this->getObject()->getId()) {
            $this->getObject()->delete();
        }
        return parent::delete();
    }

    public function getObject() {
        if(!$this->_object) {
            if($class = $this->getModel()) {
                $this->_object = new $class();
                $this->_object->find($this->getValueId(), 'value_id');
            }
            else {
                $this->_object = new Core_Model_Default();
            }
        }

        return $this->_object;
    }

    public function getName() {
        return $this->_($this->getData('name'));
    }

    public function getTabbarName() {
//        return $this->getData('tabbar_name') ? $this->_($this->getData('tabbar_name')) : null; // May have troubles translating certain languages
        return $this->getData('tabbar_name') ? $this->_(mb_convert_encoding($this->getData('tabbar_name'), 'UTF-8', 'UTF-8')) : null;
    }

    public function getShortTabbarName() {
        $name = $this->getTabbarName();
        return Core_Model_Lib_String::formatShortName($name);
    }

    public function getLayout() {

        if(empty($this->_layout)) {
            $this->_layout = new Template_Model_Layout();
            if($this->getLayoutId()) {
                $this->_layout->find($this->getLayoutId());
            }
        }

        return $this->_layout;

    }

    public function getLibrary() {

        if(empty($this->_library)) {
            $this->_library = new Media_Model_Library();
            if($this->getLibraryId()) {
                $this->_library->find($this->getLibraryId());
            }
        }

        return $this->_library;

    }

    public function getIconUrl($base = false) {

        if(empty($this->_icon_url) AND $this->getIconId()) {
            if($this->getIcon() AND !$base) {
                $this->_icon_url = Media_Model_Library_Image::getImagePathTo($this->getIcon());
            }
            else {
                $icon = new Media_Model_Library_Image();
                $icon->find($this->getIconId());
                $this->_icon_url = $icon->getUrl();
            }
        }

        return $this->_icon_url;
    }

    public function setIconUrl($url) {
        $this->_icon_url = $url;
        return $this;
    }

    public function resetIconUrl() {
        $this->_icon_url = null;
        return $this;
    }

    public function getImage() {

        if(empty($this->_image)) {
            $this->_image = new Media_Model_Library_Image();
            if($this->getIconId()) $this->_image->find($this->getIconId());
        }

        return $this->_image;
    }

    public function resetImage() {
        $this->_image = null;
        return $this;
    }

    public function onlyOnce() {
        return $this->getData('only_once');
    }

    public function isLink() {
        return (bool) $this->getObject() && $this->getObject()->getLink();
    }

    public function getUrl($action, $params = array(), $tiger_url = true, $env = null) {

        $url = null;
        if($this->getIsDummy()) {
            $url = 'javascript:void(0);';
        }
        else if($this->getUri()) {
            $uri = $this->getUri();
            if(!is_null($env) AND isset($this->_xml->$env) AND isset($this->_xml->$env->uri)) {
                $uri = $this->_xml->$env->uri;
            }
            if(!$tiger_url && !$this->getIsAjax() && $this->getObject()->getLink()) $url = $this->getObject()->getLink();
            else $url = parent::getUrl($uri.$action, $params);
        }
        else {
            $url = '/front/index/noroute';
        }

        return $url;
    }

}