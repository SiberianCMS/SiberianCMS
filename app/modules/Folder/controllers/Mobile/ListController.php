<?php

class Folder_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {

                $category_id = $this->getRequest()->getParam('category_id');
                $current_category = new Folder_Model_Category();

                if($category_id) {
                    $current_category->find($category_id, 'category_id');
                }

                $object = $this->getCurrentOptionValue()->getObject();

                if(!$object->getId() OR ($current_category->getId() AND $current_category->getRootCategoryId() != $object->getRootCategoryId())) {
                    throw new Exception($this->_('An error occurred during process. Please try again later.'));
                }

                if(!$current_category->getId()) {
                    $current_category = $object->getRootCategory();
                }

                $data = array("folders" => array());

                $subcategories = $current_category->getChildren();

                foreach($subcategories as $subcategory) {
                    $data["folders"][] = array(
                        "title" => $subcategory->getTitle(),
                        "subtitle" => $subcategory->getSubtitle(),
                        "picture" => $subcategory->getPictureUrl(),
                        "url" => $this->getPath("folder/mobile_list", array("value_id" => $value_id, "category_id" => $subcategory->getId()))
                    );
                }

                $pages = $current_category->getPages();
                $color = $this->getApplication()->getBlock('background')->getImageColor();
                foreach($pages as $page) {
                    $data["folders"][] = array(
                        "title" => $page->getTabbarName(),
                        "subtitle" => "",
                        "picture" => $this->_getColorizedImage($page->getIconId(), $color),
                        "url" => $page->getPath(null, array('value_id' => $page->getId()), false),
                    );
                }

                $data["cover"] = array(
                    "title" => $current_category->getTitle(),
                    "subtitle" => $current_category->getSubtitle(),
                    "picture" => $current_category->getPictureUrl()
                );

                $data["page_title"] = $current_category->getTitle();

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}