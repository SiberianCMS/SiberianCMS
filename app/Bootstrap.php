<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected $_request = null;
    protected $_front_controller = false;

    protected function _initPaths() {

        Zend_Loader_Autoloader::getInstance()->registerNamespace('Core');

        $base_path = '';
        if(isset($_SERVER['SCRIPT_FILENAME'])) $base_path = realpath(dirname($_SERVER['SCRIPT_FILENAME']));
        Core_Model_Directory::setBasePath($base_path);

        $path = '';
        if(isset($_SERVER['SCRIPT_NAME'])) $path = $_SERVER['SCRIPT_NAME'];
        else if(isset($_SERVER['PHP_SELF'])) $path = $_SERVER['PHP_SELF'];
        $path = str_replace('/'.basename($path), '', $path);
        Core_Model_Directory::setPath($path);

    }

    protected function _initHtaccess() {

        $old_htaccess = Core_Model_Directory::getBasePathTo('htaccess.txt');
        $new_htaccess = Core_Model_Directory::getBasePathTo('.htaccess');
        if(!file_exists($new_htaccess) AND is_readable($old_htaccess) AND is_writable(Core_Model_Directory::getBasePathTo())) {
            $content = file_get_contents($old_htaccess);
            $content = str_replace('# ${RewriteBase}', 'RewriteBase '.Core_Model_Directory::getPathTo(), $content);
            $htaccess = fopen($new_htaccess, 'w');
            fputs($htaccess, $content);
            fclose($htaccess);
        }

    }

    protected function _initConnection() {

        $this->bootstrap('db');
        $resource = $this->getResource('db');
        Zend_Registry::set('db', $resource);

    }

    /**
     * Permet de garder le nom des modules avec une majuscule et les url en minuscule
     */
    protected function _initDispatcher() {

        $frontController = Zend_Controller_Front::getInstance();
        $frontController->setDispatcher(new Siberian_Controller_Dispatcher_Standard());
        $this->bootstrap('frontController');
        $this->_front_controller = $frontController;
    }

    protected function _initRequest() {

        Core_Model_Language::prepare();
        $frontController = $this->_front_controller;
        $this->_request = new Siberian_Controller_Request_Http();
        $this->_request->isInstalling(!Installer_Model_Installer::isInstalled());
        $this->_request->setPathInfo();
        $baseUrl = $this->_request->getScheme().'://'.$this->_request->getHttpHost().$this->_request->getBaseUrl();
        $this->_request->setBaseUrl($baseUrl);
        $frontController->setRequest($this->_request);
        Siberian_View::setRequest($this->_request);
        Core_Model_Default::setBaseUrl($this->_request->getBaseUrl());

    }

    protected function _initRouter() {

        $front = $this->_front_controller;
        $router = $front->getRouter();
        $router->addRoute('default', new Siberian_Controller_Router_Route_Module(array(), $front->getDispatcher(), $front->getRequest()));

    }

    protected function _initCache() {

        $cache_dir = Core_Model_Directory::getCacheDirectory(true);
        if(is_writable($cache_dir)) {
            $frontendConf = array ('lifetime' => 345600, 'automatic_seralization' => true);
            $backendConf = array ('cache_dir' => $cache_dir);
            $cache = Zend_Cache::factory('Core','File',$frontendConf,$backendConf);
            $cache->setOption('automatic_serialization', true);
            Zend_Locale::setCache($cache);
            Zend_Registry::set('cache', $cache);
        }
    }

    protected function _initDesign() {

        if(!$this->_request->isInstalling()) {
            $this->_prepareBlocks();
        }

        $detect = new Mobile_Detect();

        $this->getPluginLoader()->addPrefixPath('Siberian_Application_Resource', 'Siberian/Application/Resource');

        if(!$this->_request->isInstalling()) {
            if($this->_request->isApplication()) $apptype = 'mobile';
            else $apptype = 'desktop';
            if($detect->isMobile() || $apptype == 'mobile') $device_type = 'mobile';
            else $device_type = 'desktop';
            $code = 'siberian';
            if($this->_request->isApplication()) $code = "angular";
        } else {
            $apptype = 'desktop';
            $device_type = 'desktop';
            $code = "installer";
        }
        $base_paths = array(APPLICATION_PATH . "/design/email/template/");

        define('APPLICATION_TYPE', $apptype);
        define('DEVICE_TYPE', $device_type);
        define('DEVICE_IS_IPHONE', $detect->isIphone() || $detect->isIpad());
        define('IS_APPLICATION', $detect->isNative() && $this->_request->isApplication());
        Core_Model_Directory::setDesignPath("/app/design/$apptype/$code");
        define('DESIGN_CODE', $code);

        $resources = array(
            'resources' => array(
                'layout' => array('layoutPath' => APPLICATION_PATH . "/design/$apptype/$code/template/page")
            )
        );

        $base_paths[] = APPLICATION_PATH . "/design/$apptype/$code/template/";

        $this->setOptions($resources);

        $this->bootstrap('View');
        $view = $this->getResource('View');
        $view->doctype('HTML5');

        foreach($base_paths as $base_path) {
            $view->addBasePath($base_path);
        }

        Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->setNeverRender(true);

        Core_View_Default::setDevice($detect);
    }

    protected function _prepareBlocks() {
        if($this->_request->isApplication()) {
            Core_View_Mobile_Default::setBlocks(Application_Model_Application::getInstance()->getBlocks());
        }
    }

    public function run() {

        $front   = $this->_front_controller;
        $default = $front->getDefaultModule();
        if (null === $front->getControllerDirectory($default)) {
            throw new Zend_Application_Bootstrap_Exception(
                'No default controller directory registered with front controller'
            );
        }

        $front->setParam('bootstrap', $this);
        $request = $front->getRequest();

        $response = $front->dispatch($request);
        if ($front->returnResponse()) {
            return $response;
        }
    }

}

function debugbacktrace() {

    $errors = debug_backtrace();
    $dump = '';
    foreach($errors as $error) {
        if(!empty($error['file'])) $dump .= 'file : ' . $error['file'];
        if(!empty($error['function'])) $dump .= ':: ' . $error['function'];
        if(!empty($error['line'])) $dump .= ' (l:' . $error['line'] . ')';
        $dump .= '
';
    }

    Zend_Debug::dump($dump);

}