<?php

class Core_Controller_Default extends Zend_Controller_Action
{

    protected $_layout;
    protected static $_session = array();

    public function init() {

        $this->_initSession();

        $this->_initLanguage();
        $this->_initLocale();

        if($url = $this->_needToBeRedirected()) {
            $this->_redirect($url, $this->getRequest()->getParams());
            return $this;
        }

        $this->_initTranslator();

        $this->_layout = $this->_helper->layout->getLayoutInstance();

        if(preg_match('/(?i)msie \b[5-9]\b/',$this->getRequest()->getHeader('user_agent')) && !preg_match('/(oldbrowser)/', $this->getRequest()->getActionName())) {
            $message = $this->_("Your browser is too old to view the content of our website.<br />");
            $message .= $this->_("In order to fully enjoy our features, we encourage you to use at least:.<br />");
            $message .= '- Internet Explorer 10 ;<br />';
            $message .= '- Firefox 3.5 ;<br />';
            $message .= '- Chrome 8 ;<br />';
            $message .= '- Safari 6 ;<br />';

            $this->getSession()->addWarning($message, 'old_browser');

        }

    }

    public function getApplication() {
        return Application_Model_Application::getInstance();
    }

    public function _($text) {
        $args = func_get_args();
        return Core_Model_Translator::translate($text, $args);
    }

    public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            return $this->_forward('noroute');
        }

        throw new Exception('Méthode invalide "' . $method . '" appelée',500);
    }

    public function isProduction() {
        return APPLICATION_ENV == 'production';
    }

    public function getSession($type = 'front') {
        return isset(self::$_session[$type]) ? self::$_session[$type] : null;
    }

    public static function setSession($session, $type = 'front') {
        self::$_session[$type] = $session;
    }

    public function loadPartials($action = null, $use_base = null) {
        if(is_null($use_base)) $use_base = true;
        if(is_null($action)) $action = $this->getFullActionName('_');
        $this->getLayout()->setAction($action)->load($use_base);

        return $this;
    }

    public function render() {

    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
//                $priority = Zend_Log::NOTICE;
//                $this->view->message = '404, Page not found';
                $action = 'noroute';

                break;
            default:
                Zend_Debug::dump($errors->exception);
                die;
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
//                $this->_forward('exception');
                $action = 'exception';
                break;
        }

        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }

//        // conditionally display exceptions
//        if ($this->getInvokeArg('displayExceptions') == true) {
//            $this->view->exception = $errors->exception;
//        }

//        $this->view->request   = $errors->request;

        $this->_forward($action);
    }

    public function oldbrowserAction() {
        $this->loadPartials('front_index_oldbrowser');
        return $this;
    }

    public function preDispatch() {
        parent::preDispatch();
    }

    public function postDispatch() {

        if(!$this->getLayout()->isLoaded() AND $this->getRequest()->isDispatched()) {
            $this->_forward('noroute');
        }

        parent::postDispatch();
    }

    public function norouteAction() {

        if(!$this->getRequest()->isApplication()) {
            $this->getResponse()->setHeader('HTTP/1.0', '404 Not Found');;
            $this->loadPartials('front_index_noroute');
        } else {
            $this->forward("index", "index");
        }

    }

    public function exceptionAction() {
        $errors = $this->_getParam('error_handler');

        Zend_Debug::dump($errors);die;
        $this->loadPartials('front_index_error');
    }

    public function getLayout() {
        return $this->_layout;
    }

    public function getFullActionName($separator = '/') {

        return strtolower(join($separator, array(
            $this->getRequest()->getModuleName(),
            $this->getRequest()->getControllerName(),
            $this->getRequest()->getActionName()
        )));

    }

    public function getUrl($url = '', array $params = array(), $locale = null) {
        return Core_Model_Url::create($url, $params, $locale);
    }

    public function getPath($uri = '', array $params = array()) {
        return Core_Model_Url::createPath($uri, $params);
    }

    public function getCurrentUrl($withParams = true, $locale = null) {
        return Core_Model_Url::current($withParams, $locale);
    }

    public function downloadAction() {

        $path = $this->getRequest()->getParam('path');
        $path = base64_decode($path);
        $name = $this->getRequest()->getParam('name');
        $name = base64_decode($name);

        $content_type = $this->getRequest()->getParam('content_type');

        $this->_download($path, $name, $content_type);

    }

    protected function _getImage($name, $base = false) {

        if(file_exists(Core_Model_Directory::getDesignPath(true) . '/images/' . $name)) {
            return Core_Model_Directory::getDesignPath($base).'/images/'.$name;
        }
        else if(file_exists(Media_Model_Library_Image::getBaseImagePathTo($name))) {
            return $base ? Media_Model_Library_Image::getBaseImagePathTo($name) : Media_Model_Library_Image::getImagePathTo($name);
        }

        return "";

    }

    protected function _getColorizedImage($image_id, $color) {

        $color = str_replace('#', '', $color);
        $id = md5(implode('+', array($image_id, $color)));
        $url = '';

        $image = new Media_Model_Library_Image();
        if(is_numeric($image_id)) {
            $image->find($image_id);
            if(!$image->getId()) return $url;
            if(!$image->getCanBeColorized()) $color = null;
            $path = $image->getLink();
            $path = Media_Model_Library_Image::getBaseImagePathTo($path);
        } else if(!Zend_Uri::check($image_id) AND stripos($image_id, Core_Model_Directory::getBasePathTo()) === false) {
            $path = Core_Model_Directory::getBasePathTo($image_id);
        } else {
            $path = $image_id;
        }

        try {
            $image = new Core_Model_Lib_Image();
            $image->setId($id)
                ->setPath($path)
                ->setColor($color)
                ->colorize()
            ;
            $url = $image->getUrl();
        } catch(Exception $e) {
            $url = '';
        }

        return $url;
    }

    protected function _redirect($url, array $options = array()) {
        $url = Core_Model_Url::create($url, $options);
        parent::_redirect($url, $options);
    }

    protected function _initSession() {

        $configSession = new Zend_Config_Ini(APPLICATION_PATH . '/configs/session.ini', APPLICATION_ENV);

        if(!$this->getRequest()->isInstalling()) {
            $config = array(
                'name'           => 'session',
                'primary'        => 'session_id',
                'modifiedColumn' => 'modified',
                'dataColumn'     => 'data',
                'lifetimeColumn' => 'lifetime',
                'lifetime'       => $configSession->gc_maxlifetime
            );

            Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable($config));
        }

        if(!$this->getRequest()->isInstalling() OR is_writable(Core_Model_Directory::getSessionDirectory(true))) {
            $types = array();
            $options = $configSession->toArray();

            if(isset($options['types'])) {
                $types = $options['types'];
                unset($options['types']);
            }

            Zend_Session::start($options);

            $session_type = 'front';
            if($this->getRequest()->isApplication()) $session_type = 'mobile';
            $session = new Core_Model_Session($session_type);
            $session->prepare($types);

            $language_session = new Core_Model_Session('language');
            if(!$language_session->current_language) {
                $language_session->current_language = null;
            }

            Core_Model_Language::setSession($language_session);
            Core_View_Default::setSession($session);
            Core_Controller_Default::setSession($session);

        }

    }

    protected function _initLanguage() {

        $available_languages = Core_Model_Language::getLanguageCodes();
        $current_language = in_array($this->getRequest()->getLanguageCode(), $available_languages) ? $this->getRequest()->getLanguageCode() : '';
        $language_session = Core_Model_Language::getSession();
        $language = '';
        if($language_session) {
            $language = $language_session->current_language;
        }

        if(!empty($current_language)) {
            Core_Model_Language::setCurrentLanguage($current_language);
        } else if(!empty($language)) {
        } else if($accepted_languages = Zend_Locale::getBrowser()) {
            $accepted_languages = array_keys($accepted_languages);
            foreach($accepted_languages as $lang) {
                if(in_array($lang, $available_languages)) {
                    $current_language = $lang;
                    break;
                }
            }

            if(!$current_language) {
                $current_language = Core_Model_Language::getDefaultLanguage();
            }

            Core_Model_Language::setCurrentLanguage($current_language);

        } else {
            Core_Model_Language::setCurrentLanguage(Core_Model_Language::getDefaultLanguage());
        }

    }

    protected function _initLocale() {

        $locale = Core_Model_Language::DEFAULT_LOCALE;

        if($this->getRequest()->isApplication()) {
            $locale = $this->getApplication()->getLocale();
        } else {
            $locale = new Zend_Locale(Core_Model_Language::getCurrentLocale());
        }

        Zend_Registry::set('Zend_Locale', new Zend_Locale($locale));

    }

    protected function _initTranslator() {
        Core_Model_Translator::prepare(strtolower($this->getRequest()->getModuleName()));
        return $this;
    }

    protected function _needToBeRedirected() {

        $url = null;

        if($this->getRequest()->isInstalling()) {
            if(!$this->getRequest()->isXmlHttpRequest() AND !in_array($this->getFullActionName('_'), array('front_index_index'))) {
//                if(!$this->getRequest()->getParam('error_handler')) {
                    $url = '/';
//                }
            }
        }

        if($this->getRequest()->getLanguageCode()) {
            $url = is_null($url) ? $this->getRequest()->getPathInfo() : $url;
        }

        return $url;
    }

    protected function _sendHtml($html) {
        if(!empty($html['error'])) {
            $this->getResponse()->setHttpResponseCode(400);
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    protected function _download($file, $filename, $content_type = 'application/vnd.ms-excel') {

        if(file_exists($file)) $content = file_get_contents($file);
        else $content = $file;

        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$filename);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $content_type);
        $response->setBody($content);
        $response->sendResponse();
        die;

    }

    protected function _setBaseLayout($layout) {
        $this->_helper->layout()->setLayout($layout);
        return $this;
    }

}
