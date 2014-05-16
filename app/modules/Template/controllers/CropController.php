<?php

class Template_CropController extends Core_Controller_Default
{

    public function uploadAction() {
        if ($datas = $this->getRequest()->getParams() AND !empty($_FILES)) {
            try {

                $folder = Core_Model_Directory::getTmpDirectory(true).'/';

                $params = array();
                $params['validators'] = array(
                    'Extension' => array('jpg', 'png', 'jpeg', 'gif', 'case' => false),
                    'Size' => array('min' => 100, 'max' => 8000000),
                    'ImageSize' => array(
                        'minwidth' => 20,
                        'minheight' => 20,
                        'maxwidth' => 2000,
                        'maxheight' => 2000,
                    )
                );

                $params['destination_folder'] = $folder;
                $params['uniq'] = 1;
                $params['uniq_prefix'] = '';

                //param customs
                foreach($params['validators']['ImageSize'] as $key => $value) {
                    if(isset($datas[$key])) {
                        $params['validators']['ImageSize'][$key] = $datas[$key];
                    }
                }
                if(isset($datas['uniq_prefix'])) {
                    $params['uniq_prefix'] = $datas['uniq_prefix'];
                }

                if(isset($datas['desired_name']) && $datas['desired_name'] != '') {
                    $params['desired_name'] = $datas['desired_name'];
                }

                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->upload($params);

                $image_sizes = getimagesize(Core_Model_Directory::getTmpDirectory(true).'/'.$file);

                $datas = array(
                    'success' => 1,
                    'files' => $file,
                    'source_width' => $image_sizes[0],
                    'source_height' => $image_sizes[1],
                    'message_success' => 'Envoi réussi',
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

    public function cropAction() {
        $picture = $this->getRequest()->getParam('picture');
        $image_sizes = getimagesize(Core_Model_Directory::getTmpDirectory(true).'/'.$picture);
        $option_value_id = '';
        $is_colorizable = false;
        if($this->getRequest()->getParam('option_value_id')) {
            $option_value_id = $this->getRequest()->getParam('option_value_id');
        }
        if($this->getRequest()->getParam('is_colorizable')) {
            $is_colorizable = $this->getRequest()->getParam('is_colorizable');
        }
        $html = $this->getLayout()->addPartial('crop', 'core_view_default', 'page/layout/crop.phtml')
            ->setPicture($picture)
            ->setWidth($image_sizes[0])
            ->setHeight($image_sizes[1])
            ->setOutputWidth($this->getRequest()->getParam('outputWidth'))
            ->setOutputHeight($this->getRequest()->getParam('outputHeight'))
            ->setOutputUrl($this->getRequest()->getParam('outputUrl'))
            ->setUploader($this->getRequest()->getParam('uploader'))
            ->setOptionId($option_value_id)
            ->setIsColorizable($is_colorizable)
            ->toHtml()
        ;

        $this->getLayout()->setHtml($html);
    }

}
