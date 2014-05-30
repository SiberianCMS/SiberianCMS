<?php

class Installer_Model_Installer extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        return $this;
    }

    public static function isInstalled() {

        $isInstalled = false;
        try {
            if(!file_exists(APPLICATION_PATH . '/configs/app.ini')) {
                throw new Exception('');
            }
            $ini = new Zend_Config_Ini(APPLICATION_PATH . '/configs/app.ini', APPLICATION_ENV);
            $isInstalled = (bool) $ini->isInstalled;
        } catch (Exception $e) {
            $isInstalled = false;
        }

        return $isInstalled;
    }

    public static function checkPermissions() {

        $errors = array();
        $base_path = Core_Model_Directory::getBasePathTo('/');
        if(is_file($base_path.'htaccess.txt')) {
            if(!is_writable($base_path)) {
                $errors[] = 'The root directory /';
            }
            if(!is_writable($base_path.'htaccess.txt')) {
                $errors[] = '/htaccess.txt';
            }
        }

        $paths = array('var/cache', 'var/session', 'var/tmp');
        foreach($paths as $path) {
            if(!is_writable($base_path.$path)) {
                $errors[] = '/var and all of its subfolders';
                break;
            }
        }

        if(!is_writable($base_path.'app/modules')) {
            $errors[] = '/app/modules and all of its subfolders';
        }

        $paths = array('app/design/desktop/siberian/layout', 'app/design/desktop/siberian/template');
        foreach($paths as $path) {
            if(!is_writable($base_path.$path)) {
                $errors[] = '/app/design/desktop/siberian and all of its subfolders';
                break;
            }
        }

        $paths = array('app/design/mobile/siberian/layout', 'app/design/mobile/siberian/template');
        foreach($paths as $path) {
            if(!is_writable($base_path.$path)) {
                $errors[] = '/app/design/mobile/siberian and all of its subfolders';
                break;
            }
        }


        $paths = array(
            "app/design/email",
            "images",
            "app/configs",
            "app/configs/app.sample.ini"
        );

        foreach($paths as $path) {
            if(!is_writable($base_path.$path)) {
                $errors[] = $path;
            }
        }

        return $errors;
    }

    public function install() {
        $module = new Installer_Model_Installer_Module();
        $module->prepare($this->getModuleName())
            ->install()
        ;
        return $this;
    }

    public static function setIsInstalled() {

        if(self::isInstalled()) {
            return;
        }

        try {

            $writer = new Zend_Config_Writer_Ini();

            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/app.ini', null, array('skipExtends' => true, 'allowModifications' => true));
            $config->production->isInstalled = "1";

            $writer->setConfig($config)
                ->setFilename(APPLICATION_PATH . '/configs/app.ini')
                ->write()
            ;

            return true;

        } catch (Exception $e) {
            return false;
        }

    }

}
