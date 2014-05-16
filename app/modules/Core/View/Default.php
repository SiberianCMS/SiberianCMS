<?php

class Core_View_Default extends Siberian_View
{

    protected static $_session = array();
    protected static $_device;

    public function __construct($config = array()) {
//        $this->_session = new Core_Model_Session('front');
        parent::__construct($config);
    }

    public function isProduction() {
        return APPLICATION_ENV == 'production';
    }

    public function getSession($type = 'front') {
        return isset(self::$_session[$type]) ? self::$_session[$type] : null;
    }

    public static function setSession($session, $type = 'front') {
        self::$_session[$type] = $session;
    }

    public function getApplication() {
        return Application_Model_Application::getInstance();
    }

    public function getDevice() {
        return self::$_device;
    }

    public static function setDevice($device) {
        self::$_device = $device;
    }

    public function _($text) {
        $args = func_get_args();
        return Core_Model_Translator::translate($text, $args);
    }

    public function isHomePage() {
        return $this->getRequest()->getParam('module') == 'Front' &&
            $this->getRequest()->getParam('controller') == 'index' &&
            $this->getRequest()->getParam('action') == 'index'
        ;
    }

    public function isMobileDevice() {
        return DEVICE_TYPE == 'mobile';
    }

    public function getJs($name) {
        return $this->getRequest()->getMediaUrl().'/app/design/' . APPLICATION_TYPE . '/' . DESIGN_CODE . '/js/' . $name;
    }

    public function getImage($name, $base = false) {

        if(file_exists(Core_Model_Directory::getDesignPath(true) . '/images/' . $name)) {
            return Core_Model_Directory::getDesignPath($base).'/images/'.$name;
        }
        else if(file_exists(Media_Model_Library_Image::getBaseImagePathTo($name))) {
            return $base ? Media_Model_Library_Image::getBaseImagePathTo($name) : Media_Model_Library_Image::getImagePathTo($name);
        }

        return "";

    }

    public function getColorizedImage($image_id, $color) {

        $color = str_replace('#', '', $color);
        $id = md5(implode('+', array($image_id, $color)));
        $url = '';

        $image = new Media_Model_Library_Image();
        if(is_numeric($image_id)) {
            $image->find($image_id);
            if(!$image->getId()) return $url;
            if(!$image->getCanBeColorized()) $color = null;
            $path = $image->getLink();
            $path = Media_Model_Library_Image::getBaseImagePathTo($path);
        } else if(!Zend_Uri::check($image_id) AND stripos($image_id, Core_Model_Directory::getBasePathTo()) === false) {
            $path = Core_Model_Directory::getBasePathTo($image_id);
        } else {
            $path = $image_id;
        }

        try {
            $image = new Core_Model_Lib_Image();
            $image->setId($id)
                ->setPath($path)
                ->setColor($color)
                ->colorize()
            ;
            $url = $image->getUrl();
        } catch(Exception $e) {
            $url = '';
        }

        return $url;
    }

    public function getUrl($url = '', array $params = array(), $locale = null) {
        return Core_Model_Url::create($url, $params, $locale);
    }

    public function getCurrentUrl($withParams = true, $locale = null) {
        return Core_Model_Url::current($withParams, $locale);
    }

    protected function _renderZendMenu($xml) {
        $config = new Zend_Config_Xml($xml);
        $this->navigation(new Zend_Navigation($config));
        if(!$this->getPluginLoader('helper')->getPaths(Zend_View_Helper_Navigation::NS)) {
            $this->addHelperPath('Zend/View/Helper/Navigation', 'Zend_View_Helper_Navigation');
        }

        if(!$this->getPluginLoader('helper')->getPaths('Siberian_View_Helper_Navigation')) {
            $this->addHelperPath('Siberian/View/Helper/Navigation', 'Siberian_View_Helper_Navigation');
        }

        $nav = $this->navigation();
        return $nav->menu();
    }

}
