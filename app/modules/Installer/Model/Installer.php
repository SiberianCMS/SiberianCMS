<?php

class Installer_Model_Installer extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        return $this;
    }

    public static function isInstalled() {
        $ini = new Zend_Config_Ini(APPLICATION_PATH . '/configs/app.ini', APPLICATION_ENV);
        $isInstalled = false;
        try {
            $isInstalled = !empty($ini->resources->db->params->host) && Application_Model_Application::getInstance()->getId() && is_file(Core_Model_Directory::getBasePathTo('.htaccess'));
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
            "app/configs/app.ini"
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

}
