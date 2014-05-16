<?php

class Installer_InstallationController extends Installer_Controller_Installation_Default {

    public function indexAction() {

        $this->loadPartials();
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