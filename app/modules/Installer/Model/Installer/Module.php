<?php

class Installer_Model_Installer_Module extends Core_Model_Default
{

    const DEFAULT_VERSION = '0.0.1';

    protected $_name;

    protected $_lastVersion;

    protected $_dbFiles = array();

    protected $_isInstalled = false;

    protected $_basePath;

    public function __construct($config = array()) {
        $this->_db_table = 'Installer_Model_Db_Table_Installer_Module';
        parent::__construct($config);
    }

    public function prepare($name) {

        $this->_name = $name;
        $this->findByName($name);

        if(!$this->getId()) {
            $this->setName($name)
                ->setVersion(self::DEFAULT_VERSION)
            ;
            $this->_isInstalled = false;
        }
        else {
            $this->_isInstalled = true;
        }

        $this->_basePath = APPLICATION_PATH . "/modules/{$name}";
        $excluded = array('.', '..');

        $versions = array();

        if(is_dir("$this->_basePath/db")) {

            $dir = opendir("$this->_basePath/db");
            while($file = readdir($dir)) {
                if(preg_match('/(database.)/i', $file)) {
                    $version = str_replace(array('database.', '.php'), '', $file);
                    $this->_dbFiles[$version] = "$this->_basePath/db/$file";
                    $versions[] = $version;
                }
            }
            ksort($this->_dbFiles);
            sort($versions);
        }

        $this->_lastVersion = !empty($versions) ? end($versions) : self::DEFAULT_VERSION;

        return $this;
    }

    public function reset() {
        $this->_lastVersion = null;
        $this->_dbFiles = array();
        $this->_isInstalled = false;
        $this->_basePath = null;
        return $this;

    }

    public function findByName($name) {

        if($this->getTable()->isInstalled()) {
            $this->find($name, 'Name');
        }

        return $this;
    }

    public function getName() {
        return $this->_name;
    }

    public function isInstalled() {
        return $this->_isInstalled;
    }

    public function canUpdate() {
        return version_compare($this->getVersion(), $this->_lastVersion, '<');
    }

    public function install() {
        
        foreach($this->_dbFiles as $version => $file) {
            if(version_compare($version, $this->getVersion(), '>')) {
                $this->_run($file, $version);
                $this->save();
            }
        }

    }

    protected function _run($file, $version) {

        try {
            $this->getTable()->install($this->getName(), $file, $version);
            $this->setVersion($version);
        }
        catch(Exception $e) {
            Zend_Debug::dump($e);
            die;
        }
        return $this;
    }

}