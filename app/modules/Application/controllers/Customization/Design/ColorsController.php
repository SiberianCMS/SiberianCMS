<?php

class Application_Customization_Design_ColorsController extends Application_Controller_Default {

    public function editAction() {
        $this->loadPartials();

        if($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                // S'il y a embrouille
                if(empty($datas['block_id'])) throw new Exception($this->_('An error occurred while saving your colors.'));

                // Récupère l'application en cours
                $application = $this->getApplication();

                // Récupère le block
                $block = new Template_Model_Block();
                $block->find($datas['block_id']);
                // S'il y a re-embrouille
                if(!$block->getId()) throw new Exception($this->_('An error occurred while saving your colors.'));

                if(!empty($datas['color'])) {
                    $block->setData('color', $datas['color']);
                }
                if(!empty($datas['background_color'])) {
                    $block->setData('background_color', $datas['background_color']);
                }
                if(!empty($datas['tabbar_color'])) {
                    $block->setData('image_color', $datas['tabbar_color']);
                }

                $block->save();
                $html = array('success' => '1');

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }
}
