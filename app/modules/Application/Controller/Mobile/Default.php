<?php

class Application_Controller_Mobile_Default extends Core_Controller_Default {

    protected $_current_option_value;
    protected $_layout_id;

    public function init() {

        parent::init();

        // Test si un id de value est passé en paramètre
        if($id = $this->getRequest()->getParam('option_value_id') OR $id = $this->getRequest()->getParam('value_id')) {
            // Créé et charge l'objet
            $this->_current_option_value = new Application_Model_Option_Value();

            if($id != "homepage") {
                $this->_current_option_value->find($id);
                // Récupère le layout de l'option_value en cours
                if($this->_current_option_value->getLayoutId()) {
                    $this->_layout_id = $this->_current_option_value->getLayoutId();
                }
            } else {
                $this->_current_option_value->setIsHomepage(true);
            }

        }

//        $excluded = '/('.join(')|(',
//            array(
//                'front_mobile_home_view',
//                'front_mobile_home_template',
//                'application_device_check',
//                'customer_mobile_account',
//                'customer_mobile_account_autoconnect',
//                'push_mobile_list',
//                'push_mobile_count',
//                'application_mobile_customization_colors',
//                'application_mobile_previewer_infos',
//                'front_mobile_gmaps_view',
//                'mcommerce_mobile_cart_view',
//                'findall',
//                'find',
//                'backgroundimage'
//            )
//        ).')/';
//
//        Zend_Debug::dump($excluded);die;
//
//        if(!$this->_current_option_value AND !preg_match($excluded, $this->getFullActionName('_'))) {
//            $this->_redirect('/');
//            return $this;
//        }
//        else
        if($this->getFullActionName('_') == 'front_mobile_home_view') {
            $this->_layout_id = $this->getApplication()->getLayoutId();
        }
        else {
            $this->_layout_id = 1;//$this->_current_option_value->getLayout()->getCode();
        }

        Core_View_Mobile_Default::setCurrentOption($this->_current_option_value);

        $this->log();

        return $this;
    }

    public function isOverview() {
        return $this->getSession()->isOverview;
    }

    /**
     * @depecrated
     */
    public function viewAction() {
        $option = $this->getCurrentOptionValue();
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array('html' => mb_convert_encoding($this->getLayout()->render(), 'UTF-8', 'UTF-8'), 'title' => $option->getTabbarName());
        if($url = $option->getBackgroundImageUrl()) $html['background_image_url'] = $url;
        $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function backgroundimageAction() {

        $url = "";
        $option = $this->getCurrentOptionValue();
        if($option->getUseHomepageBackgroundImage()) {
            $url = $this->getApplication()->getHomepageBackgroundImageUrl("retina");
        }
        if($option->getHasBackgroundImage()) {
            $url = $option->getBackgroundImageUrl();
        }

        $this->getLayout()->setHtml($url);

    }

    public function getCurrentOptionValue() {
        return $this->_current_option_value;
    }

    protected function _prepareHtml() {

        $option = $this->getCurrentOptionValue();
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
        $html = array('html' => mb_convert_encoding($this->getLayout()->render(), 'UTF-8', 'UTF-8'), 'title' => $option->getTabbarName());
        if($url = $option->getBackgroundImageUrl()) $html['background_image_url'] = $url;
        $html['use_homepage_background_image'] = (int) $option->getUseHomepageBackgroundImage() && !$option->getHasBackgroundImage();
        return $html;

    }

    protected function _sendHtml($html) {
        if(is_array($html) AND !empty($html['error'])) {
            $this->getResponse()->setHttpResponseCode(400);
        }
//        $this->getResponse()->setHeader('Content-type', 'text/json');
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function log() {

        if($this->getCurrentOptionValue()) {

            $uri = $this->getFullActionName();
            $log = new Core_Model_Log();
            $detect = new Mobile_Detect();

            $host = !empty($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : '';
            $user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
            $other = array(
                'user_agent' => $user_agent,
                'host' => $host
            );

            if($this->getSession()->getCustomerId()) $log->setCustomerId($this->getSession()->getCustomerId());
            $log->setUri($uri)
                ->setDeviceName($detect->getDeviceName())
                ->setOther(serialize($other))
                ->save()
            ;

        }
        return $this;
    }
}
