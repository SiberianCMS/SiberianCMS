<?php
/** Zend_Controller_Router_Route_Abstract */
require_once 'Zend/Controller/Router/Route/Module.php';

class Siberian_Controller_Router_Route_Module extends Zend_Controller_Router_Route_Module
{
    public function getVersion() {
        return 0;
    }

    public function match($request, $partial = false) {
        return parent::match($request->getPathInfo(), $partial);
    }

    public function assemble($data = array(), $reset = false, $encode = false, $partial = false)
    {

        if(isset($data['error_handler'])) unset($data['error_handler']);

        $url = '';

        if(empty($url)) {
            $url = parent::assemble($data, $reset, $encode, $partial);
        }

        $url = !empty($url) ? explode('/', $url) : array();

        if($this->_request->useApplicationKey()) {
            array_unshift($url, Application_Model_Application::OVERVIEW_PATH);
        }
        if($this->_request->addLanguageCode() AND $this->_request->getLanguageCode()) {
            array_unshift($url, $this->_request->getLanguageCode());
        }

        return implode('/', $url);
    }

}
