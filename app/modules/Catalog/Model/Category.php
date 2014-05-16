<?php

class Catalog_Model_Category extends Core_Model_Default
{

    protected $_products;
    protected $_active_products;
    protected $_parent;
    protected $_children;
    protected $_active_children;

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Catalog_Model_Db_Table_Category';
    }

    public function findByValueId($value_id, $pos_id = null, $only_active = false, $only_first_level = false) {
        return $this->getTable()->findByValueId($value_id, $pos_id, $only_active, $only_first_level);
    }

    public function findLastPosition($value_id, $parent_id = null) {
        return $this->getTable()->findLastPosition($value_id, $parent_id) + 1;
    }

    public function updatePosition($rows) {
    	$this->getTable()->updatePosition($rows);
    	return $this;
    }

    public function getParent() {

        if(!$this->_parent) {
            $category = new Catalog_Model_Category();
            if($this->getParentId()) {
                $category->find($this->getParentId());
            }
            $this->_parent = $category;
        }

        return $this->_parent;
    }

    public function getChildren() {

        if(!$this->_children) {
            $category = new Catalog_Model_Category();
            $this->_children = $category->findAll(array('parent_id' => $this->getId()), 'position ASC');
        }

        return $this->_children;
    }

    public function getActiveChildren() {

        if(!$this->_active_children) {
            $category = new Catalog_Model_Category();
            $this->_active_children = $category->findAll(array('parent_id' => $this->getId(), 'is_active' => 1));
        }

        return $this->_active_children;
    }

    public function addProduct($product) {

        $id = $product->getId();
        if(!$id) $id = 'new_'.count($this->_products);

        $this->_products[$id] = $product;
        return $this;
    }

    public function setProducts($products) {
        $this->_products = $products;
        return $this;
    }

    public function getProducts() {
        if(is_null($this->_products)) {
            if($this->getId() AND $this->getCategoryId()) {
                $this->loadProducts();
                if(count($this->_products) == 0) $this->_products = array();
            }
        }

        return !is_null($this->_products) ? $this->_products :  array();
    }

    public function getActiveProducts() {
        if(is_null($this->_active_products)) {

            $products = array();
            foreach($this->getProducts() as $product) {
                if($product->getIsActive()) $products[] = $product;
            }
            $this->_active_products = $products;
        }
        return $this->_active_products;
    }

    public function loadProducts() {
        $product = new Catalog_Model_Product();
        $products = $product->findByCategory($this->getId());
        foreach($products as $product) {
            $this->_products[$product->getId()] = $product;
        }
        return $this;
    }

    public function resetProducts() {
        $this->_products = null;
        return $this;
    }

    public function save() {

        if(!$this->getIsDeleted()) {
            if(!$this->getPosition()) {
                $this->setPosition($this->findLastPosition($this->getValueId(), $this->getParentId()));
            }
        }
        else {
            foreach($this->getChildren() as $child) $child->setIsDeleted(1)->save();
        }

        parent::save();

        if(!$this->getData('is_deleted')) {

            if(!empty($this->_products)) {
                foreach($this->_products as $product) {
                    $product->setCategoryId($this->getId())
                        ->setValueId($this->getValueId())
                        ->save()
                    ;
                }
            }
        }
        else if(!$this->getParentId()) {
            foreach($this->getChildren() as $child) {
                foreach($child->getProducts() as $product) $product->delete();
            }
            foreach($this->getProducts() as $product) $product->delete();
        }

    }

}