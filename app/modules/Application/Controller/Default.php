<?php

class Application_Controller_Default extends Admin_Controller_Default {

    protected $_application;
    protected $_current_option_value;

    public function init() {

        parent::init();
        $excluded = array(
            'admin_application_list',
            'admin_application_new',
            'admin_application_set',
            'admin_application_createpost',
            'front_index_noroute',
            'front_index_error',
        );

        // Test si un id de value est passé en paramètre
        if($id = $this->getRequest()->getParam('option_value_id')) {
            // Créé et charge l'objet
            $this->_current_option_value = new Application_Model_Option_Value();
            $this->_current_option_value->find($id);
        }

    }

    public function editAction() {

        if($this->getCurrentOptionValue()) {
            $this->loadPartials(null, false);
            $this->getLayout()->getPartial('content')->setOptionValue($this->getCurrentOptionValue());
            if($this->getLayout()->getPartial('content_editor')) $this->getLayout()->getPartial('content_editor')->setOptionValue($this->getCurrentOptionValue());
            $html = array('html' => mb_convert_encoding($this->getLayout()->render(), 'UTF-8', 'UTF-8'));
            $path =  $this->getCurrentOptionValue()->getPath(null, array(), "mobile");
            $html["path"] = $path ? $path : "";
            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }
    }

    public function getCurrentOptionValue() {
        return $this->_current_option_value;
    }

}
