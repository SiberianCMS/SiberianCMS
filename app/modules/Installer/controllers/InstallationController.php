<?php

class Installer_InstallationController extends Installer_Controller_Installation_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function endAction() {

        if(Installer_Model_Installer::isInstalled()) {
            return $this;
        }

        try {
            if(Installer_Model_Installer::setIsInstalled()) {
                $html = array('success' => 1);
            } else {
                throw new Exception("An error occured while finalizing the installation.");
            }

        } catch (Exception $e) {
            $html = array('message' => $e->getMessage());
            $this->getResponse()->setHttpResponseCode(400);
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }


//    public function setlanguageAction() {
//        if($language_code = $this->getRequest()->getParam('__l')) {
//            if(in_array($language_code, Core_Model_Language::getLanguageCodes())) {
//                Core_Model_Language::setCurrentLanguage($language_code);
//            }
//            $this->_redirect('installer');
//        }
//    }

}