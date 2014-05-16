<?php

class Template_DesignController extends Application_Controller_Default {

    public function listAction() {

        $layout = $this->getLayout();
        $layout->setBaseRender('modal', 'html/modal.phtml', 'core_view_default')->setTitle($this->_('TEMPLATES'))->setSubtitle($this->_('Choose a template to customize'));
        $layout->addPartial('modal_content', 'admin_view_default', 'template/application/design/list.phtml')->setTitle('Test title');
        $html = array('modal_html' => $layout->render());

        $layout->setHtml(Zend_Json::encode($html));

    }

    public function saveAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {
                if(empty($datas['design_id'])) throw new Exception($this->_('An error occurred while saving'));

                $application = $this->getApplication();
                $design = new Template_Model_Design();
                $design->find($datas['design_id']);

                if(!$design->getId()) throw new Exception($this->_('An error occurred while saving'));

                $this->getApplication()->setDesign($design)->save();

                $html = array(
                    'success' => 1,
                    'overview_src' => $design->getOverview(),
                    'homepage_normal' => $application->getHomepageBackgroundImageUrl(),
                    'homepage_retina4' => $application->getHomepageBackgroundImageUrl('retina')
                );
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_buttom' => 1,
                    'message_loader' => 1
                );
            }
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }

}
