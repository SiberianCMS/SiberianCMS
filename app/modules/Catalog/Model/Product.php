<?php

class Catalog_Model_Product extends Core_Model_Default
{

    protected $_instanceSingleton;
    protected $_outlets;
    protected $_category;
    protected $_category_ids;
    protected $_groups;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Product';
    }

    public function findByCategory($category_id, $use_folder = false) {
        return $this->getTable()->findByCategory($category_id, $use_folder);
    }

    public function findByValueId($value_id, $pos_id = null, $only_active = false, $with_menus = false) {
        return $this->getTable()->findByValueId($value_id, $pos_id, $only_active, $with_menus);
    }

    public function findByPosId($product_id) {
        $this->uns();
        $row = $this->getTable()->findByPosId($product_id);
        if($row) {
            $this->setData($row->getData());
            $this->setId($row->getId());
        }

        return $this;
    }

    public function findMenus($value_id) {
        return $this->getTable()->findMenus($value_id);
    }

    public function findLastPosition($value_id) {
        return $this->getTable()->findLastPosition($value_id) + 1;
    }

    public function updatePosition($rows) {
    	$this->getTable()->updatePosition($rows);
    	return $this;
    }

    public function getCategory() {

        if(is_null($this->_category)) {
            $this->_category = new Catalog_Model_Category();
            $this->_category->find($this->getCategoryId());
        }

        return $this->_category;
    }

    public function setCategory($category) {
        $this->_category = $category;
        return $this;
    }

    public function getCategoryIds() {
        if(!$this->_category_ids) {
            $this->_category_ids = array();
            if($this->getId()) {
                $this->_category_ids = $this->getTable()->findCategoryIds($this->getId());
            }
        }

        return $this->_category_ids;
    }

    public function getGroups() {

        if(!$this->_groups) {
            $group = new Catalog_Model_Product_Group_Value();
            $this->_groups = $group->findAll(array('product_id' => $this->getId()));
        }

        return $this->_groups;

    }

    public function getType() {
        if(is_null($this->_instanceSingleton)) {
            if(!is_null($this->getData('type'))) {
            $class = 'Catalog_Model_Product_';
            $class .= implode('_', array_map('ucwords', explode('_', $this->getData('type'))));
                $this->_instanceSingleton = new $class();
                $this->_instanceSingleton->setProduct($this);
            }
        }

        return $this->_instanceSingleton;
    }

//    public function getPrice() {
//        if($this->getData('price')) {
//            return $this->formatPrice($this->getData('price'));
//        }
//    }

    public function getMinPrice() {

        if(!$this->getData('min_price')) {
            $min_price = 0;
            if($this->getData('type') == 'format') {
                $formats = $this->getType()->getOptions();
                foreach($formats as $format) {
                    if(is_null($min_price)) $min_price = $format->getPrice();
                    else $min_price = min($min_price, $format->getPrice());
                }
            }
            else {
                $min_price = $this->getPrice();
            }

            $groups = $this->getGroups();
            foreach($groups as $group) {

                if(!$group->isRequired()) continue;
                $min_option_price = null;
                foreach($group->getOptions() as $option) {
                    if($option->getPrice()) {
                        if(!$min_option_price) $min_option_price = $option->getPrice();
                        else $min_option_price = min($min_option_price, $option->getPrice());
                    }
                }
                if($min_option_price) $min_price += $min_option_price;
            }

            $this->setMinPrice($min_price);
        }

        return $this->getData('min_price');
    }

    public function checkType() {
        $options = $this->getData('option');
        if(!empty($options)) {
            $this->setType('format');
            $this->getType()->setOptions($options);
        }
    }

    public function getDescription() {
        return stripslashes($this->getData('description'));
    }

    public function getPictureUrl() {
        if($this->getData('picture')) {
            $image_path = Application_Model_Application::getImagePath().$this->getData('picture');
            $base_image_path = Application_Model_Application::getBaseImagePath().$this->getData('picture');
            if(file_exists($base_image_path)) {
                return $image_path;
            }
        }
        return null;
    }

    public function save() {

        $this->checkType();

        if(!$this->getPosition()) $this->setPosition($this->findLastPosition($this->getValueId()));

        if(!$this->getData('type')) $this->setData('type', 'simple');
        if($this->getData('type') == 'simple') {
            $price = Core_Model_Language::normalizePrice($this->getData('price'));
            $this->setData('price', $price);
        }

        parent::save();

        if($this->getNewCategoryIds()) {
            $this->getTable()->saveCategoryIds($this->getId(), $this->getNewCategoryIds());
        }

        $this->getType()->setProduct($this)->save();

        return $this;
    }

}