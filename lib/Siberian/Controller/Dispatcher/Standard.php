<?php

class Siberian_Controller_Dispatcher_Standard extends Zend_Controller_Dispatcher_Standard
{

    protected $_moduleDirectories = array();

    /**
     * Add a single path to the controller directory stack
     *
     * @param string $path
     * @param string $module
     * @return Zend_Controller_Dispatcher_Standard
     */
    public function addControllerDirectory($path, $module = null)
    {
        if (null === $module) {
            $module = $this->_defaultModule;
        }

        $module = (string) $module;
        $path   = rtrim((string) $path, '/\\');

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $exists = $autoloader->getNamespaceAutoloaders($module);
        if(empty($exists)) {
            $autoloader->registerNamespace($module);
        }

        $this->_moduleDirectories[] = $module;
        $this->_controllerDirectory[$module] = $path;
        $this->_controllerDirectory[strtolower($module)] = $path;
        return $this;
    }

    public function getModuleDirectories() {
        return $this->_moduleDirectories;
    }

    public function getSortedModuleDirectories() {
        $dirs = $this->_moduleDirectories;
        sort($dirs);
        unset($dirs[array_search('Core', $dirs)]);
        unset($dirs[array_search('Application', $dirs)]);
        unset($dirs[array_search('Media', $dirs)]);
        $dirs = array_reverse($dirs);
        $dirs[] = "Media";
        $dirs[] = "Application";
        $dirs[] = "Core";
        $dirs = array_reverse($dirs);

        return $dirs;
    }

}
