<?php

class IndexController extends Core_Controller_Default {

    public function indexAction() {

        $layout_id = null;

        if(!$this->getRequest()->isApplication()) {
            if($this->getRequest()->isInstalling()) $layout_id = 'installer_installation_index';
            else if(!$this->getSession()->isLoggedIn()) $layout_id = 'admin_account_login';
            else $layout_id = 'application_customization_design_style_edit';

            $module = substr($layout_id, 0, stripos($layout_id, '_'));
            Core_Model_Translator::addModule($module);
        }

        $this->loadPartials($layout_id);
    }

}