<?php

class Application_SettingsController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function savedomainAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(!empty($datas['domain'])) {
                    $datas['domain'] = trim(str_replace(array('https', 'http', '://'), '', $datas['domain']));
                    $parts = explode('/', $datas['domain']);
                    $datas['domain'] = !empty($parts[0]) ? $parts[0] : null;
                    $url = 'http://'.$datas['domain'];
                    if(!Zend_Uri::check($url)) {
                        throw new Exception($this->_('Please enter a valid address'));
                    } else if(addslashes($this->getRequest()->getBaseUrl()) == addslashes($url)) {
                        throw new Exception($this->_('Please enter a valid address'));
                    }
                }
                else {
                    $datas['domain'] = null;
                }

                $this->getApplication()
                     ->setDomain($datas['domain'])
                     ->save()
                ;

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Infos successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'domain' => $this->getApplication()->getDomain(),
                    'application_url' => $this->getApplication()->getUrl()
                );

            }
            catch(Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

    public function checkcnameAction() {

        if($this->getRequest()->isPost()) {

            try {
                $code = 1;
                $application = $this->getApplication();
                if($application->getDomain() AND Core_Model_Url::checkCname($application->getDomain(), $this->getRequest()->getServer('SERVER_ADDR'))) {
                    $code = 0;
                }
            }
            catch(Exception $e) {
                Zend_Debug::dump($e->getMessage());
                die;
            }
            $html = Zend_Json::encode(array('code' => $code));
            $this->getLayout()->setHtml($html);
        }

    }


}
