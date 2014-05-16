<?php

class Application_Webservice_PreviewController extends Core_Controller_Default
{
    public function loginAction() {

        if($datas = $this->getRequest()->getPost()) {

            $canBeLoggedIn = false;

            try {

                if(empty($datas['email']) OR empty($datas['password'])) {
                    throw new Exception($this->_('Authentification impossible. Merci de vérifier votre email et/ou votre mot de passe'));
                }
                $admin = new Admin_Model_Admin();
                $admin->findByEmail($datas['email']);

                if($admin->authenticate($datas['password'])) {

                    $application = $this->getApplication();
                    $datas = array('applications' => array());
                    $url = parse_url($application->getUrl());
                    $url['path'] = 'overview';
                    $icon = '';
                    if($application->getIcon()) {
                        $icon = $this->getRequest()->getBaseUrl() . $application->getIcon();
                    }


                    $datas['application'] = array(
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
                    throw new Exception($this->_('Authentification impossible. Merci de vérifier votre email et/ou votre mot de passe'));
                }

            }
            catch(Exception $e) {
                $datas = array('error' => $this->_('Authentification impossible. Merci de vérifier votre email et/ou votre mot de passe'));
//                $datas = array('error' => $e->getMessage());
            }

            $this->getResponse()->setBody(Zend_Json::encode($datas))->sendResponse();
            die;
        }

    }

}
