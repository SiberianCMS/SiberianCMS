<?php
class Folder_Model_Category extends Core_Model_Default {

    protected $_root_category_id;
    protected $_children;
    protected $_pages;
    protected $_products;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Folder_Model_Db_Table_Category';
        return $this;
    }

    public function getRootCategoryId() {

        if(!$this->_root_category_id) {
            if($this->getParentId()) {
                $this->_root_category_id = $this->getTable()->findRootCategoryId($this->getParentId());
            }
            else {
                $this->_root_category_id = $this->getId();
            }
        }

        return $this->_root_category_id;

    }

    public function getChildren() {

        if(!$this->_children) {
            $category = new self();
            $this->_children = $category->findAll(array('parent_id' => $this->getId()), 'pos ASC');
        }

        return $this->_children;
    }

    public function getPages() {
        if(!$this->_pages) {
            $page = new Application_Model_Option_Value();
            $this->_pages = $page->findAll(array('folder_category_id' => $this->getId(), "is_active" => 1), "folder_category_position ASC");
        }

        return $this->_pages;
    }
    public function getProducts() {
        if(!$this->_products) {
            $product = new Catalog_Model_Product();
            $this->_products = $product->findByCategory($this->getId(), true);
        }

        return $this->_products;
    }

    public function getPictureUrl() {
        $path_picture = Application_Model_Application::getImagePath().$this->getPicture();
        $base_path_picture = Application_Model_Application::getBaseImagePath().$this->getPicture();
        if($this->getPicture() AND file_exists($base_path_picture)) {
            return $path_picture;
        }
        return '';
    }

    public function getNextCategoryPosition($parent_id) {
        $lastPosition = $this->getTable()->getLastCategoryPosition($parent_id);
        if(!$lastPosition) $lastPosition = 0;

        return ++$lastPosition;
    }

    public function delete() {

        $category_option = new Application_Model_Option_Value();
        $option_values = $category_option->findAll(array('folder_category_id' => $this->getId()));
        if($option_values->count()) {
            foreach($option_values as $option_value) {
                $option_value->setFolderId(null)
                    ->setFolderCategoryId(null)
                    ->setFolderCategoryPosition(null)
                    ->save();
            }
        }

        foreach($this->getChildren() as $child) {
            $child->delete();
        }

        return parent::delete();

    }

}
