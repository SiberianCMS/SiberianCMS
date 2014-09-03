<?php

class Application_Mobile_TemplateController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        $pages = $this->getApplication()->getOptions();
        $baseUrl = $this->getApplication()->getUrl()."/";
        $path = $this->getApplication()->getPath()."/";
        $partials = array();

        foreach($pages as $page) {
            if(!$page->isActive()) continue;
            if(!$page->getIsAjax() AND $page->getObject()->getLink()) continue;

            $suffix = "_l{$page->getLayoutId()}";

            $layout = str_replace(array($baseUrl, "/"), array("", "_"), $page->getUrl("template").$suffix);
            $layout_id = str_replace($baseUrl, "", $path.$page->getUrl("template"));
            $this->loadPartials($layout, false);
            $partials[$layout_id] = $this->getLayout()->render();
            $this->getLayout()->unload();
        }

        if($this->getApplication()->usesUserAccount()) {

            $account_partials = array("customer/mobile_account_login/template", "customer/mobile_account_register/template",
            "customer/mobile_account_edit/template", "customer/mobile_account_forgottenpassword/template");

            foreach($account_partials as $account_partial) {
                $layout = str_replace("/", "_", $account_partial.$suffix);
                $layout_id = $path.$account_partial;
                $this->loadPartials($layout, false);

                $partials[$layout_id] = $this->getLayout()->render();
                $this->getLayout()->unload();
            }
        }

        $this->_sendHtml($partials);
    }

}
