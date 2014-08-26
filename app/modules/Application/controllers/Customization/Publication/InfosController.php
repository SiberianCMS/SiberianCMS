<?php

class Application_Customization_Publication_InfosController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();

        if($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(!empty($datas["name"])) {
                    $this->getApplication()->setName($datas['name'])->save();
                } else if(!empty($datas['bundle_id'])) {
                    if(count(explode('.', $datas['bundle_id'])) < 2) {
                        throw new Exception($this->_('The entered bundle id is incorrect, it should be like: com.siberiancms.app'));
                    }
                    $this->getApplication()->setBundleId($datas['bundle_id'])->save();
                } else {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                $html = array('success' => '1');

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

    public function generateAction() {

        if($datas = $this->getRequest()->getParams()) {

            try {
                if(empty($datas['device_id'])) throw new Exception("L'application recherchée n'existe pas");

                $device_id = $datas['device_id'];
                $device = new Application_Model_Device(array('device_id' => $device_id));

                $zip = $device->getResources();
                $path = explode('/', $zip);
                end($path);

                $this->_download($zip, current($path), 'application/octet-stream');

            }
            catch(Exception $e) {
                Zend_Debug::dump($e);
            }


            die('ok');

        }

    }

}
