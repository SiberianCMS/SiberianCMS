<?php

class Installer_Model_Installer_Module_Parser extends Core_Model_Default
{

    protected $_tmp_file;
    protected $_tmp_directory;
    protected $_module_name;
    protected $_files;
    protected $_errors;

    public function __construct($config = array()) {
        parent::__construct($config);
    }

    public function setFile($file) {
        $this->_tmp_file = $file;
        $infos = pathinfo($this->_tmp_file);
        $this->_module_name = $infos['filename'];
        $this->_tmp_directory = Core_Model_Directory::getTmpDirectory(true).'/'.$this->_module_name;
        $this->_files = array();
        return $this;
    }

    public function check() {

        if(!$this->_extract()) return false;

        $this->_parse();

        if(!$this->_checkPermissions()) return false;

        if(!$this->_copy()) return false;

        $this->_removeTmpDirectory($this->_tmp_directory);

        return true;

    }

    public function getErrors() {
        return $this->_errors;
    }

    protected function _addError($error) {
        $this->_errors[] = $error;
        return $this;
    }

    protected function _extract() {

        $zip = new ZipArchive();
        if($zip->open($this->_tmp_file)) {
            $tmp_dir = Core_Model_Directory::getTmpDirectory(true).'/';
            if(!is_writable($tmp_dir)) {
                $this->_addError($this->_("The folder %s is not writable. Please fix this issue and try again.", $tmp_dir));
            } else {

                if(is_dir($this->_tmp_directory)) {
                    $this->_removeTmpDirectory($this->_tmp_directory);
                }
                mkdir($this->_tmp_directory, 0777);

                if($zip->extractTo($tmp_dir.$this->_module_name)) {
                    return true;
                } else {
                    $this->_addError($this->_("Unable to extract the archive."));
                }

                $zip->close();
            }
        } else {
            $this->_addError($this->_("Unable to open the archive."));
        }

        return false;
    }

    protected function _parse($dirIterator = null) {

        if(is_null($dirIterator)) $dirIterator = new DirectoryIterator($this->_tmp_directory);

        foreach($dirIterator as $element) {
            if($element->isDot()) {
                continue;
            }
            if($element->isDir()) {
                $this->_parse(new DirectoryIterator($element->getRealPath()));
            } else if($element->isFile()) {
                $this->_files[] = array(
                    'source' => $element->getRealPath(),
                    'destination' => str_replace($this->_tmp_directory, Core_Model_Directory::getBasePathTo(), $element->getRealPath())
                );

            }
        }

    }

    protected function _checkPermissions() {

        $errors = Installer_Model_Installer::checkPermissions();

        foreach($this->_files as $file) {
            $info = pathinfo($file['destination']);
            $dirname = $info['dirname'];
            if(is_dir($dirname) AND !is_writable($dirname)) {
                $dirname = str_replace(Core_Model_Directory::getBasePathTo(), '', $dirname);
                $errors[] = $dirname;
            }
        }

        if(!empty($errors)) {
            $errors = array_unique($errors);
            if(count($errors) > 1) {
                $errors = implode('<br /> - ', $errors);
                $message = $this->_("Les dossiers suivants n'ont pas les droits en écriture : <br /> - %s", $errors);
            } else {
                $error = current($errors);
                $message = $this->_("The folder %s is not writable.", $error);
            }

            $this->_addError($message);

            return false;

        }

        return true;
    }

    protected function _copy() {

        $errors = array();
        foreach($this->_files as $file) {
            $info = pathinfo($file['destination']);
            if(!is_dir($info['dirname'])) {
                if(!@mkdir($info['dirname'], 0775, true)) {
                    $errors[] = $info['dirname'];
                }
            }
        }

        if(!empty($errors)) {
            $errors = array_unique($errors);
            if(count($errors) > 1) {
                $errors = implode('<br /> - ', $errors);
                $message = $this->_("The following folders are not writable: <br /> - %s", $errors);
            } else {
                $error = current($errors);
                $message = $this->_("The folder %s is not writable.", $error);
            }

            $this->_addError($message);

            return false;

        } else {
            foreach($this->_files as $file) {
                $info = pathinfo($file['destination']);
                if(!is_dir($info['dirname'])) mkdir($info['dirname'], 0775, true);
                copy($file['source'], $file['destination']);
            }
        }

        return true;

    }

    protected function _removeTmpDirectory($dir) {

        $dirIterator = new DirectoryIterator($dir);
        foreach($dirIterator as $element) {
            if ($element->isDot()) {
                continue;
            }
            if ($element->isDir()) {
                $this->_removeTmpDirectory($element->getRealPath());
                rmdir($element->getRealPath());
            } else if($element->isFile()) {
                unlink($element->getRealPath());
            }
        }

        rmdir($dir);

        return $this;
    }

}