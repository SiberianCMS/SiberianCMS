<?php

class Application_Customization_Design_StyleController extends Application_Controller_Default {

    protected $_application;
    protected $_homepage_path;
    protected $_homepage_relative_path;

    public function preDispatch() {
        parent::preDispatch();
        $this->_homepage_relative_path = '/homepage_image/';
        $this->_homepage_path = Application_Model_Application::getBaseImagePath().$this->_homepage_relative_path;
    }

    public function editAction() {
        $this->loadPartials();
        if($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function changelayoutAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $html = array();

                if(empty($datas['layout_id'])) throw new Exception($this->_('An error occurred while changing your layout.'));

                $layout = new Application_Model_Layout_Homepage();
                $layout->find($datas['layout_id']);

                if(!$layout->getId()) throw new Exception($this->_('An error occurred while changing your layout.'));

                $html = array('success' => 1);
                if($layout->getId() != $this->getApplication()->getLayoutId()) {
                    $this->getApplication()->setLayoutId($datas['layout_id'])->save();
                    $html['reload'] = 1;
                }

            }
            catch(Exception $e) {
//                $html = array('message' => 'Une erreur est survenue lors de la sauvegarde, merci de réessayer ultérieurement');
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1,
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function mutualizebackgroundimagesAction() {

        try {
            $this->getApplication()
                ->setUseHomepageBackgroundImageInSubpages((int) $this->getRequest()->getPost('use_homepage_background_image_in_subpages', 0))
                ->save()
            ;

            $html = array('success' => '1');

        }
        catch(Exception $e) {
            $html = array('message' => $e->getMessage());
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function savehomepageAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $filetype = $this->getRequest()->getParam('filetype');
                $folder = Application_Model_Application::getBaseImagePath().$this->_homepage_relative_path.$filetype.'/';
                $datas['dest_folder'] = $folder;

                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);

                if($filetype == 'bg') {
                        $this->getApplication()->setHomepageBackgroundImageRetinaLink($this->_homepage_relative_path.'bg'.'/'.$file);
                } else {
                        $this->getApplication()->setHomepageBackgroundImageLink($this->_homepage_relative_path.'bg_lowres'.'/'.$file);
                }
                $this->getApplication()->save();

                $datas = array(
                    'success' => 1,
                    'file' => $file
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

    public function deletehomepageAction() {
        $filetype = $this->_request->getparam('filetype');
        try {
            if($filetype == 'bg') {
                $this->getApplication()->setHomepageBackgroundImageRetinaLink(null);
                $this->getApplication()->setHomepageBackgroundImageLink(null);
                $this->getApplication()->setHomepageBackgroundImageId(null);
            } else if($filetype == 'icon') {
                $this->getApplication()->setHomepageLogoLink(null);
            }
            $this->getApplication()->save();
            $html = array('success' => '1');
        } catch(Exception $e) {
            $html = array('message' => $e->getMessage());
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function savefontAction() {
        if($datas = $this->getRequest()->getPost()) {

            try {
                if(!empty($datas['font_family'])) $this->getApplication()->setFontFamily($datas['font_family']);
//                if(!empty($datas['font_size'])) $this->getApplication()->setFontSize($datas['font_size']);

                $this->getApplication()->save();

                $html = array('success' => '1');

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function savelocaleAction() {
        if($datas = $this->getRequest()->getPost()) {

            try {
                if(!empty($datas['locale'])) $this->getApplication()->setLocale($datas['locale']);
                $this->getApplication()->save();
                $html = array('success' => '1');
            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

}

