<?php

class Cms_Application_PageController extends Application_Controller_Default {

    public function editpostAction() {

        if ($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {
                // Test s'il y a un value_id
                if (empty($datas['value_id']))
                    throw new Exception($this->_('An error occurred while saving your page.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $page = new Cms_Model_Application_Page();
                $page->find($option_value->getId(), 'value_id');

                // Traitement des images des blocks
                $blocks = !empty($datas['block']) && is_array($datas['block']) ? $datas['block'] : array();
                $image_path = '/cms/block/';
                $base_image_path = $this->getApplication()->getBaseImagePath() . $image_path;
                if (!is_dir($base_image_path))
                    mkdir($base_image_path, 0777, true);

                foreach ($blocks as $k => $block) {
                    if ($block["type"] == "image" && !empty($block['image_url'])) {
                        foreach ($block['image_url'] as $index => $image_url) {
                            //déjà enregistrée
                            if (substr($image_url, 0, 1) != '/') {
                                if (!empty($image_url) AND file_exists(Core_Model_Directory::getTmpDirectory(true).'/'.$image_url)) {
                                    rename(Core_Model_Directory::getTmpDirectory(true).'/'.$image_url, $base_image_path . $image_url);
                                    $blocks[$k]['image_url'][$index] = $image_path . $image_url;
                                }
                            } else {
//                                $img = explode('/', $image_url);
//                                $img = $img[count($img) - 1];
//                                $blocks[$k]['image_url'][$index] = $image_path . $img;
                                $blocks[$k]['image_url'][$index] = $image_url;
                            }
                        }
                        foreach ($block['image_fullsize_url'] as $index => $image_url) {
                            //déjà enregistrée
                            if (substr($image_url, 0, 1) != '/') {
                                if (!empty($image_url) AND file_exists(Core_Model_Directory::getTmpDirectory(true).'/' . $image_url)) {
                                    rename(Core_Model_Directory::getTmpDirectory(true).'/'.$image_url, $base_image_path . $image_url);
                                    $blocks[$k]['image_fullsize_url'][$index] = $image_path . $image_url;
                                }
                            } else {
//                                $img = explode('/', $image_url);
//                                $img = $img[count($img) - 1];
//                                $blocks[$k]['image_fullsize_url'][$index] = $image_path . $img;
                                $blocks[$k]['image_fullsize_url'][$index] = $image_url;
                            }
                        }
                    }
                    if (($block["type"] == "text" || $block["type"] == "video") && !empty($block['image'])) {
                        //déjà enregistrée
                        if (substr($block['image'], 0, 1) != '/') {
                            if (!empty($block['image']) AND file_exists(Core_Model_Directory::getTmpDirectory(true).'/'.$block['image'])) {
                                rename(Core_Model_Directory::getTmpDirectory(true).'/'.$block['image'], $base_image_path . $block['image']);
                                $blocks[$k]['image'] = $image_path . $block['image'];
                            }
                        } else {
//                            $img = explode('/', $block['image']);
//                            $img = $img[count($img) - 1];
//                            $blocks[$k]['image'] = $image_path . $img;
                            $blocks[$k]['image'] = $block['image'];
                        }
                    }
                }

                $datas['block'] = $blocks;

                // Sauvegarde
                $page->setData($datas)->save();

                $html = array(
                    'success' => 1,
                    'success_message' => $this->_('Page successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function addblockAction() {

        if ($datas = $this->getRequest()->getPost()) {

            try {

                $position = $this->getRequest()->getParam('position');

                if (empty($datas['block_id']))
                    throw new Exception($this->_('An error occurred during process. Please try again later.'));
                if (empty($position))
                    throw new Exception($this->_('An error occurred during process. Please try again later.'));

                $block = new Cms_Model_Application_Block();
                $block->find($datas['block_id']);

                if (!$block->getId())
                    throw new Exception($this->_('An error occurred during process. Please try again later.'));

                $html = array(
                    'success' => 1,
                );

                $html['layout'] = $this->getLayout()
                        ->addPartial('row', 'admin_view_default', $block->getTemplate())
                        ->setCurrentBlock($block)
                        ->setCurrentOptionValue($this->getCurrentOptionValue())
                        ->setPosition($position)
                        ->toHtml()
                ;
            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function resizeAction() {
        try {

            $folder = Core_Model_Directory::getTmpDirectory(true).'/';

            $current_file = $this->getRequest()->getParam('file');

            $image_sizes = getimagesize($folder . $current_file);
            $src_width = $image_sizes[0];
            $src_height = $image_sizes[1];

            $params = array(
                'file' => $current_file,
                'source_width' => $src_width,
                'source_height' => $src_height,
                'crop_width' => $src_width,
                'crop_height' => $src_height,
                'output_width' => 400,
                'output_height' => 200,
                'w' => 400,
                'h' => 200
            );

            if ($src_width < $params['output_width'] || $src_height < $params['output_height']) {
                $source = imagecreatefromstring(file_get_contents($folder . $current_file));
                $dest_ratio = $params['output_width'] / $src_width;
                $dest_width = $params['output_width'];
                $dest_height = $src_height * $dest_ratio;

                $dest = ImageCreateTrueColor($dest_width, $dest_height);
                $trans_colour = imagecolorallocatealpha($dest, 0, 0, 0, 127);

                imagefill($dest, 0, 0, $trans_colour);
                imagecopyresized($dest, $source, 0, 0, 0, 0, $dest_width, $dest_height, $src_width, $src_height);
                imagesavealpha($dest, true);
                imagepng($dest, $folder . $current_file, 0);
                $params["source_width"] = $dest_width;
                $params["source_height"] = $dest_height;
                $params["crop_width"] = $dest_width;
                $params["crop_height"] = $dest_height;
            }

            $x1 = ($params["source_width"] / 2) - ($params["output_width"] / 2);
            $y1 = ($params["source_height"] / 2) - ($params["output_height"] / 2);

            $params['x1'] = $x1;
            $params['y1'] = $y1;

            $uploader = new Core_Model_Lib_Uploader();
            $new_file = $uploader->savecrop($params);

            $datas = array(
                'success' => 1,
                'fullsize_file' => $current_file,
                'file' => $new_file,
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

    public function cropvideoAction() {
        if ($datas = $this->getRequest()->getPost()) {
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
