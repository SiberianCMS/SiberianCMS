<?php

class Application_CustomizationController extends Application_Controller_Default {

    public function indexAction() {
        $this->_redirect('application/customization_design_style/edit');
    }

}
