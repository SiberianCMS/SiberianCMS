<?php

class Catalog_Application_ProductController extends Application_Controller_Default
{

    public function editAction() {
        if($this->getCurrentOptionValue()) {
            $product = new Catalog_Model_Product();
            if($product_id = $this->getRequest()->getParam('id')) {
                $product->find($product_id);
                if($product->getId() AND $product->getValueId() != $this->getCurrentOptionValue()->getId()) {
                    throw new Exception($this->_('An error occurred while loading your product.'));
                }
            }
            else if($category_id = $this->getRequest()->getParam('category_id')) {
                $category = new Catalog_Model_Category();
                $category->find($category_id);
                if($category->getValueId() != $this->getCurrentOptionValue()->getId()) {
                    $category = null;
                    $category_id = Catalog_Model_Category();
                }
                $product->setCategory($category)->setCategoryId($category_id);
            }
            $this->loadPartials(null, false);
            $this->getLayout()->getPartial('content')->setOptionValue($this->getCurrentOptionValue())->setProduct($product);

            $html = $this->getLayout()->render();
            $this->getLayout()->setHtml($html);
        }
    }

    public function createAction() {
        $this->loadPartials(null, false);
    }

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving the product. Please try again later.'));

                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $html = array();
                $product = new Catalog_Model_Product();
                if(!empty($datas['product_id'])) $product->find($datas['product_id']);
                $isNew = (bool) !$product->getId();
                $isDeleted = !empty($datas['is_deleted']);

                if($product->getId() AND $product->getValueId() != $option_value->getId()) {
                    throw new Exception($this->_('An error occurred while saving the product. Please try again later.'));
                }

                if(!$isDeleted) {
                    if(!isset($datas['is_active'])) $datas['is_active'] = 1;

                    $datas['value_id'] = $option_value->getValueId();

                    $parent_id = $datas['category_id'];
                    if(!empty($datas['subcategory_id'])) $datas['category_id'] = $datas['subcategory_id'];

                    if(!empty($datas['picture'])) {
                        if(substr($datas['picture'],0,1) == '/') {
                            unset($datas['picture']);
                        } else {
                            $illus_relative_path = '/feature/'.$option_value->getValueId().'/';
                            $folder = Application_Model_Application::getBaseImagePath().$illus_relative_path;
                            $file = Core_Model_Directory::getTmpDirectory(true).'/'.$datas['picture'];
                            if (!is_dir($folder))
                                mkdir($folder, 0777, true);
                            if(!copy($file, $folder.$datas['picture'])) {
                                throw new exception($this->_('An error occurred while saving your picture. Please try againg later.'));
                            } else {
                                $datas['picture'] = $illus_relative_path.$datas['picture'];
                            }
                        }
                    }
//                    $pos_datas = array();
//                    if(!empty($datas['pos'])) {
//                        foreach($datas['pos'] as $key => $pos_data) {
//                            $pos_datas[$key] = $pos_data;
//                        }
//                    }
//                    $product->setPosDatas($pos_datas);
                }

                if((!$product->getId() AND empty($datas['is_multiple'])) OR ($product->getId() AND $product->getData('type') != 'format' AND isset($datas['option']))) unset($datas['option']);
                $product->addData($datas);
                $product->save();
                $html = array('success' => 1);

                if(!$isDeleted) {

                    $product_id = $product->getId();
                    $product = new Catalog_Model_Product();
                    $product->find($product_id);

                    $html = array(
                        'success' => 1,
                        'product_id' => $product->getId(),
                        'parent_id' => $parent_id,
                        'category_id' => $datas['category_id']
                    );

                    $html['product_html'] = $this->getLayout()
                        ->addPartial('row', 'admin_view_default', 'catalog/application/edit/category/product.phtml')
                        ->setProduct($product)
                        ->setOptionValue($option_value)
                        ->toHtml()
                    ;
                }

            }
            catch(Exception $e) {
                $html['message'] = $e->getMessage();
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function sortproductsAction() {

        if ($rows = $this->getRequest()->getParam('product')) {

            $html = array();
            try {

                if(!$this->getCurrentOptionValue()) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $product = new Catalog_Model_Product();

                $products = $product->findByValueId($this->getCurrentOptionValue()->getId());
                $product_ids = array();

                foreach ($products as $product) {
                    $product_ids[] = $product->getId();
                }

                foreach ($rows as $key => $row) {
                    if (!in_array($row, $product_ids)) {
                        throw new Exception($this->_('An error occurred while saving. One of your products could not be identified.'));
                    }
                }
                $product->updatePosition($rows);

                $html = array(
                    'success' => 1
                );
            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function validatecropAction() {
        if($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => 'Enregistrement réussi',
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
         }
    }

}
