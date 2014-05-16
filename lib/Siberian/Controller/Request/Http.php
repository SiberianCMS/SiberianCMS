<?php
/**
 * Siberian_Controller_Request_Http
 *
 * HTTP request object for use with Zend_Controller family.
 * Add set & getMediaUrl
 *
 * @uses Zend_Controller_Request_Abstract
 * @package Siberian_Controller
 * @subpackage Request
 */
class Siberian_Controller_Request_Http extends Zend_Controller_Request_Http
{

    protected $_language_code;
    protected $_force_language_code = false;
    protected $_is_application;
    protected $_use_application_key = false;
    protected $_is_installing = false;
    protected $_mediaUrl;

    public function setPathInfo($pathInfo = null) {

        parent::setPathInfo($pathInfo);

        $path = $this->_pathInfo;
        $paths = explode('/', trim($path, '/'));
        $language = !empty($paths[0]) ? $paths[0] : '';

        if(in_array($language, Core_Model_Language::getLanguageCodes())) {
            $this->_language_code = $language;
            unset($paths[array_search($language, $paths)]);
            $paths = array_values($paths);
        }

        if(!$this->isInstalling()) {

            if(!empty($paths[0]) AND $paths[0] == Application_Model_Application::OVERVIEW_PATH) {
                $this->_is_application = true;
                $this->_use_application_key = true;
                unset($paths[0]);
            }

            if(Application_Model_Application::getInstance()->getDomain() == $this->getHttpHost()) {
                $this->_is_application = true;
                $this->_use_application_key = false;
            }
        }

        $paths = array_diff($paths, Core_Model_Language::getLanguageCodes());
        $paths = array_values($paths);
        $this->_pathInfo = '/'.implode('/', $paths);

        return $this;
    }

    public function setMediaUrl($url) {
        $this->_mediaUrl = $url;
        return $this;
    }

    public function getMediaUrl() {
        $url = $this->_mediaUrl;
        if(!$url) {
            $url = $this->_baseUrl;
        }

        return $url;
    }

    public function getLanguageCode() {
        return $this->_language_code;
    }

    public function setLanguageCode($language_code) {
        $this->_language_code = $language_code;
        return $this;
    }

    public function addLanguageCode($language_code = null) {
        if(!is_null($language_code)) {
            $this->_force_language_code = true;
            $this->_language_code = $language_code;
            return $this;
        } else {
            return $this->_force_language_code;
        }
    }

    public function isApplication() {
        return $this->_is_application;
    }

    public function useApplicationKey($useAppKey = null) {
        if(!is_null($useAppKey)) {
            $this->_use_application_key = $useAppKey;
            return $this;
        } else {
            return $this->_use_application_key;
        }

    }

    public function isInstalling($isInstalling = null) {
        if(!is_null($isInstalling)) {
            $this->_is_installing = $isInstalling;
            return $this;
        } else {
            return $this->_is_installing;
        }
    }

}
