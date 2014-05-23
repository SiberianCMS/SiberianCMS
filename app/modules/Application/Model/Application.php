<?php

class Application_Model_Application extends Core_Model_Default {

    const PATH_IMAGE = '/images/application';
    const OVERVIEW_PATH = 'overview';

    protected static $_instance;

    protected $_startup_image;
    protected $_options;
    protected $_pages;
    protected $_uses_user_account;
    protected $_layout;
    protected $_devices;
    protected $_design;
    protected $_design_blocks;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Application';
    }

    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new self;
            self::$_instance->find(1);
        }
        return self::$_instance;
    }

    public function findByHost($host, $path = null) {

        if(!empty($path)) {
            $uri = explode('/', ltrim($path, '/'));
            $i = 0;
            while($i <= 1) {
                if(!empty($uri[$i])) {
                    $value = $uri[$i];
                    $this->find($value, 'tmp_key');
                    if($this->getId()) {
                        $this->setUseTmpKey('1');
                        break;
                    }
                }
                $i++;
            }
        }

        if(!$this->getId()) {

            if(!in_array($host[0], array('www'))) {
                $this->find($host, 'domain');
            }
        }

        return $this;

    }

    public function findAllWaitingForPublishing() {
        return $this->getTable()->findAllWaitingForPublishing();
    }

    public function save() {

        if(!$this->getId()) {
            $this->setLayoutId(1)->setTmpKey(uniqid())->setSubdomainIsValidated(1)->setIsActive(1)->setIsLocked(0);
        }

        return parent::save();

    }

    public function getDesign() {

        if(!$this->_design) {
            $this->_design = new Template_Model_Design();
            if($this->getDesignId()) {
                $this->_design->find($this->getDesignId());
            }
        }

        return $this->_design;

    }

    public function setDesign($design) {

        $image_name = uniqid().'.png';
        $relative_path = '/homepage_image/bg/';
        $lowres_relative_path = '/homepage_image/bg_lowres/';

        if(!is_dir(self::getBaseImagePath().$lowres_relative_path)) {
            mkdir(self::getBaseImagePath().$lowres_relative_path, 0777, true);
        }
        if(!@copy($design->getBackgroundImage(true), self::getBaseImagePath().$lowres_relative_path.$image_name)) {
            throw new Exception($this->_('An error occurred while saving'));
        }

        if(!is_dir(self::getBaseImagePath().$relative_path)) {
            mkdir(self::getBaseImagePath().$relative_path, 0777, true);
        }
        if(!@copy($design->getBackgroundImageRetina(true), self::getBaseImagePath().$relative_path.$image_name)) {
            throw new Exception($this->_('An error occurred while saving'));
        }

        foreach($design->getBlocks() as $block) {
            $block->save();
        }

        $this->setDesignId($design->getId())
            ->setLayoutId($design->getLayoutId())
            ->setHomepageBackgroundImageRetinaLink($relative_path.$image_name)
            ->setHomepageBackgroundImageLink($lowres_relative_path.$image_name)
        ;

        return $this;

    }

    public function getBlocks() {

        $block = new Template_Model_Block();
        if(empty($this->_design_blocks)) {
            $this->_design_blocks = $block->findAll(null, 'position ASC');

            if(!empty($this->_design_blocks)) {
                foreach($this->_design_blocks as $block) {
                    $block->setApplication($this);
                }
            }
        }

        return $this->_design_blocks;
    }

    public function getBlock($code) {

        $blocks = $this->getBlocks();

        foreach($blocks as $block) {
            if($block->getCode() == $code) return $block;
        }

        return;
    }

    public function setBlocks($blocks) {
        $this->_design_blocks = $blocks;
        return $this;
    }

    public function getLayout() {

        if(!$this->_layout) {
            $this->_layout = new Application_Model_Layout_Homepage();
            $this->_layout->find($this->getLayoutId());
        }

        return $this->_layout;

    }

    public function getOptions() {

        if(empty($this->_options)) {
            $option = new Application_Model_Option_Value();
            $this->_options = $option->findAll();
        }

        return $this->_options;

    }

    public function getOptionIds() {

        $option_ids = array();
        $options = $this->getOptions();
        foreach($options as $option) {
            $option_ids[] = $option->getOptionId();
        }

        return $option_ids;

    }

    public function getOption($code) {

        $option_sought = new Application_Model_Option();
        $dummy = new Application_Model_Option();
        $dummy->find($code, 'code');
        foreach($this->getOptions() as $page) {
            if($page->getOptionId() == $dummy->getId()) $option_sought = $page;
        }

        return $option_sought;

    }

    public function getPages($samples = 0) {

        if(empty($this->_pages)) {
            $option = new Application_Model_Option_Value();
            $this->_pages = $option->findAll(array('remove_folder' => new Zend_Db_Expr('folder_category_id IS NULL'), 'is_visible' => 1/*, '`aov`.`is_active`' => 1*/));
        }
        if($this->_pages->count() == 0 AND $samples > 0) {
            $color = str_replace('#', '', $this->getBlock('tabbar')->getImageColor());
            $dummy = new Application_Model_Option();
            $dummy->find('newswall', 'code');
            $dummy->setTabbarName('Sample')
                ->setIsDummy(1)
                ->setIconUrl(parent::getUrl('template/block/colorize', array('id' => $dummy->getIconId(), 'color' => $color)))
            ;
            for($i = 0; $i < $samples; $i++) {
                $this->_pages->addRow($this->_pages->count(), $dummy);
            }
        }

        return $this->_pages;

    }

    public function getPage($code) {

        $dummy = new Application_Model_Option();
        $dummy->find($code, 'code');

        $page_sought = new Application_Model_Option_Value();
        return $page_sought->find(array('option_id' => $dummy->getId()));

    }

    public function getTabbarAccountName() {
        if($this->hasTabbarAccountName()) return $this->getData('tabbar_account_name');
        else return $this->_('My account');
    }

    public function getShortTabbarAccountName() {
        return Core_Model_Lib_String::formatShortName($this->getTabbarAccountName());
    }

    public function getTabbarMoreName() {
        if($this->hasTabbarMoreName()) return $this->getData('tabbar_more_name');
        else return $this->_('More');
    }

    public function getShortTabbarMoreName() {
        return Core_Model_Lib_String::formatShortName($this->getTabbarMoreName());
    }

    public function usesUserAccount() {

        if(is_null($this->_uses_user_account)) {
            $this->_uses_user_account = false;
            $codes = array('newswall', 'discount', 'loyalty');
            foreach($codes as $code) {
                if($this->getOption($code)->getId()) $this->_uses_user_account = true;
            }
        }

        return $this->_uses_user_account;
    }

    public function getCountryCode() {
        $code = $this->getData('country_code');
        if(is_null($code)) {
            $code = Core_Model_Language::getCurrentLocaleCode();
        }
        return $code;
    }

    public function getQrcode($uri = null, $params = array()) {
        $qrcode = new Core_Model_Lib_Qrcode();
        $url = "";
        if(filter_var($uri, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $url = $uri;
        }
        else {
            $url = $this->getUrl($uri);
        }

        return $qrcode->getImage($this->getName(), $url, $params);
    }

    public static function getImagePath() {
        return Core_Model_Directory::getPathTo(self::PATH_IMAGE);
    }
    public static function getBaseImagePath() {
        return Core_Model_Directory::getBasePathTo(self::PATH_IMAGE);
    }

    public function getLogo() {
        $logo = self::getImagePath().$this->getData('logo');
        $base_logo = self::getBaseImagePath().$this->getData('logo');
        if(is_file($base_logo) AND file_exists($base_logo)) return $logo;
        else return self::getImagePath().'/placeholder/no-image.png';
    }

    public function getIcon($size = null, $name = null, $base = false) {

        if(!$size) $size = 114;

        $icon = self::getBaseImagePath().$this->getData('icon');
        if(!is_file($icon) OR !file_exists($icon)) $icon = self::getBaseImagePath().'/placeholder/no-image.png';

        if(empty($name)) $name = sha1($icon.$size);
        $name .= '_'.filesize($icon);

        $newIcon = new Core_Model_Lib_Image();
        $newIcon->setId($name)
            ->setPath($icon)
            ->setWidth($size)
            ->crop()
        ;
        return $newIcon->getUrl($base);
    }

    public function getAppStoreIcon($base = false) {
        return $this->getIcon(1024, 'touch_icon_'.$this->getId(). '_1024', $base);
    }

    public function getGooglePlayIcon($base = false) {
        return $this->getIcon(512, 'touch_icon_'.$this->getId(). '_512', $base);
    }

    public function getStartupImageUrl($type = 'normal', $base = false) {

        try {
            $image = '';
            $image_name = $type == 'normal' ? $this->getData('startup_image') : $this->getData('startup_image_retina');
            if(!empty($image_name) AND file_exists(self::getBaseImagePath().$image_name)) {
                $image = $base ? self::getBaseImagePath().$image_name : self::getImagePath().$image_name;
            }
        }
        catch(Exception $e) {
            $image = '';
        }

        if(empty($image)) {
            $image = $this->getNoStartupImageUrl($type, $base);
        }

        return $image;
    }

    public function getNoStartupImageUrl($type = 'normal', $base = false) {
        $path = $base ? self::getBaseImagePath() : self::getImagePath();
        return $type == 'normal' ? $path.'/placeholder/no-startupimage.png' : $path.'/placeholder/no-startupimage_retina.png';
    }

    public function getShortName() {

        if($name = $this->getName()) {
            if(strlen($name) > 12) $name = trim(substr($name, 0, 5)) . '...' . trim(substr($name, strlen($name)-5, 5));
        }

        return $name;

    }

    public function getFacebookId() {
        return Api_Model_Key::findKeysFor('facebook')->getAppId();
    }

    public function getFacebookKey() {
        return Api_Model_Key::findKeysFor('facebook')->getSecretKey();
    }

    public function updateOptionValuesPosition($positions) {
        $this->getTable()->updateOptionValuesPosition($positions);
        return $this;
    }

    public function isAvailableForPublishing() {
        $errors = array();
        if($this->getPages()->count() < 3) $errors[] = $this->_("At least, 4 pages in your application");
        if(!$this->getData('homepage_background_image_link')) $errors[] = $this->_("The  homepage image");
        if(!$this->getStartupImage()) $errors[] = $this->_("The startup image");
        if(!$this->getIcon()) $errors[] = $this->_("The desktop icon");
        if(!$this->getName()) $errors[] = $this->_("The application name");
        if(!$this->getBundleId()) $errors[] = $this->_("The bundle id");

        return $errors;
    }

    public function getBackgroundImageUrl($type = 'normal') {

        try {
            $backgroundImage = '';
            if($background_image = $this->getData('background_image')) {
                if($type == 'normal') $background_image .= '.jpg';
                else if($type == 'retina') $background_image .= '@2x.jpg';
                else if($type == 'retina4') $background_image .= '-568h@2x.jpg';
                if(file_exists(self::getBaseImagePath().$background_image)) {
                    $backgroundImage = self::getImagePath().$background_image;
                }
            }
        }
        catch(Exception $e) {
            $backgroundImage = '';
        }

        if(empty($backgroundImage)) {
            $backgroundImage = $this->getNoBackgroundImageUrl($type);
        }

        return $backgroundImage;
    }

    public function getHomepageBackgroundImageUrl($type = 'normal') {

        try {

            $image = '';
            $image_name = $type == 'normal' ? $this->getData('homepage_background_image_link') : $this->getData('homepage_background_image_retina_link');
            if(!empty($image_name) AND file_exists(self::getBaseImagePath().$image_name)) {
                $image = self::getImagePath().$image_name;
            }
        }
        catch(Exception $e) {
            $image = '';
        }

        if(empty($image)) {
            $image = $this->getNoBackgroundImageUrl($type);
        }

        return $image;
    }

    public function getNoBackgroundImageUrl($type = 'normal') {
        return $type == 'normal' ? self::getImagePath().'/placeholder/no-background.jpg' : self::getImagePath().'/placeholder/no-background_retina.jpg';
    }

    public function getUrl($uri = null, $params = array(), $forceKey = false, $locale = null) {

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $useKey = (bool) $request->useApplicationKey();
        if(!$this->getDomain()) $forceKey = true;

        if($forceKey) {
            $request->useApplicationKey(true);
            $url = Core_Model_Url::create($uri, $params, $locale);
            $request->useApplicationKey($useKey);
        } else {
            $url = Core_Model_Url::createCustom('http://'.$this->getDomain(), $uri, $params, $locale);
        }

        return $url;

    }
}
