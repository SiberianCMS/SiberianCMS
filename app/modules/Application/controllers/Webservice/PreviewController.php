<?php

class Application_Webservice_PreviewController extends Core_Controller_Default
{
    public function loginAction() {

        if($data = $this->getRequest()->getPost()) {

            $canBeLoggedIn = false;

            try {

                if(empty($data['email']) OR empty($data['password'])) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }
                $admin = new Admin_Model_Admin();
                $admin->findByEmail($data['email']);

                if($admin->authenticate($data['password'])) {

                    $application = $this->getApplication();
                    $data = array('applications' => array());
                    $url = parse_url($application->getUrl());
                    $url['path'] = 'overview';
                    $icon = '';
                    if($application->getIcon()) {
                        $icon = $this->getRequest()->getBaseUrl() . $application->getIcon();
                    }


                    $data['application'] = array(
                        'id' => $application->getId(),
                        'icon' => $icon,
                        'startup_image' => $application->getStartupImageUrl(),
                        'startup_image_retina' => $application->getStartupImageUrl('retina'),
                        'name' => $application->getName(),
                        'scheme' => $url['scheme'],
                        'host' => $url['host'],
                        'path' => ltrim($url['path'], '/'),
                        'url' => $application->getUrl(),
                    );

                }
                else {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }

            }
            catch(Exception $e) {
                $data = array('error' => $this->_('Authentication failed. Please check your email and/or your password'));
            }

            $this->getResponse()->setBody(Zend_Json::encode($data))->sendResponse();
            die;
        }

    }

}
