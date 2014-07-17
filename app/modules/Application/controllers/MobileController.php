<?php

class Application_MobileController extends Application_Controller_Mobile_Default {

    public function defaultAction() {
        $this->loadPartials('front_index_index');
        $this->getLayout()->setHtml($this->getLayout()->toJson());
    }

}
