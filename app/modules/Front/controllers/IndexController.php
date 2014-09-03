<?php

class IndexController extends Core_Controller_Default {

    public function indexAction() {

        $layout_id = null;
        $base = true;

        if(!$this->getRequest()->isApplication()) {
            if($this->getRequest()->isInstalling()) $layout_id = 'installer_installation_index';
            else if(!$this->getSession()->isLoggedIn()) $layout_id = 'admin_account_login';
            else $layout_id = 'application_customization_design_style_edit';

            $module = substr($layout_id, 0, stripos($layout_id, '_'));
            Core_Model_Translator::addModule($module);
        } else if($this->getRequest()->isXmlHttpRequest()) {
            $base = false;
        }

        $this->loadPartials($layout_id);

//        if($this->getRequest()->isApplication()) {
//
//            $templates = $this->_loadAllPartials();
//            $html = $this->getLayout()
//                ->addPartial("templates", "core_view_mobile_default", "page/base/templates.phtml")
//                ->setTemplates($templates)
//                ->toHtml()
//            ;
////            echo $html;die;
//            $this->getLayout()->setPartialHtml("templates", $html);
////            Zend_Debug::dump($this->getLayout());
////            die;
//        }
    }

    private function _loadAllPartials() {

        $pages = $this->getApplication()->getOptions();
        $baseUrl = $this->getApplication()->getUrl()."/";
        $partials = array();
        $origLayout = clone $this->getLayout();
        $this->_layout = clone $this->getLayout();
        $this->_layout->unload();

        foreach($pages as $page) {
            if(!$page->isActive()) continue;
            if(!$page->getIsAjax() AND $page->getObject()->getLink()) continue;

            $suffix = "_l{$page->getLayoutId()}";

            $layout = str_replace(array($baseUrl, "/"), array("", "_"), $page->getUrl("template").$suffix);
            $layout_id = str_replace($baseUrl, "", $this->getApplication()->getPath()."/".$page->getUrl("template"));
            $this->loadPartials($layout, false);
            $partials[$layout_id] = $this->getLayout()->render();
//            $this->getLayout()->unload();
        }

        $this->_layout = $origLayout;

        return $partials;
    }

}