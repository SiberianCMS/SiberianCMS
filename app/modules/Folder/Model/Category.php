<?php
class Folder_Model_Category extends Core_Model_Default {

    protected $_root_category_id;
    protected $_children;
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

    public function deleteChildren($cat_id) {
        $categories_options = array();
        $categories_deleted = $this->resursiveDelete($cat_id, $categories_options);
        return $categories_deleted;
    }

    private function resursiveDelete($cat_id, $categories_options) {
        $category_children = new Folder_Model_Category();
        $category_childrens = $category_children->findAll(array('parent_id' => $cat_id));
        foreach($category_childrens as $children) {
            $categories_options = $this->resursiveDelete($children->getId(), $categories_options);
            $category_option = new Application_Model_Option_Value();
            $option_values = $category_option->findAll(array('folder_category_id' => $children->getCategoryId()));
            if($option_values->count()) {
                $categories_options[$children->getCategoryId()] = array();
                foreach($option_values as $option_value) {
                    $categories_options[$children->getCategoryId()][] = $option_value->getValueId();
                    $option_value
                        ->setFolderCategoryId(null)
                        ->setFolderCategoryPosition(null)
                        ->save();
                }
            }
            $children->delete();
        }
        return $categories_options;
    }

}
