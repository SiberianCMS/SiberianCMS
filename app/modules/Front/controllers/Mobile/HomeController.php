<?php

class Front_Mobile_HomeController extends Application_Controller_Mobile_Default {

    public function viewAction() {
        $this->loadPartials('home_mobile_view_l'.$this->_layout_id, false);
        $html = array('html' => $this->getLayout()->render(), 'hide_navbar' => true);
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

}