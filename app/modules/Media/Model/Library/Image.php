<?php

class Media_Model_Library_Image extends Core_Model_Default {

    const PATH = '/images/library';

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Library_Image';
        return $this;
    }

    public static function getImagePathTo($path = '') {
        if(!empty($path) AND substr($path,0,1) != '/') $path = '/'.$path;
        return Core_Model_Directory::getPathTo(self::PATH.$path);
    }

    public static function getBaseImagePathTo($path = '') {
        if(!empty($path) AND substr($path,0,1) != '/') $path = '/'.$path;
        return Core_Model_Directory::getBasePathTo(self::PATH.$path);
    }

    public function getUrl($t = false) {
        $url = '';
        if($this->getLink()) {
            $url = self::getImagePathTo($this->getLink());
            if(!file_exists(self::getBaseImagePathTo($this->getLink()))) $url = '';
        }

        if(empty($url)) {
            $url = $this->getNoImage();
        }
        return $url;

    }

    public function getSecondaryUrl() {
        $url = '';
        if($this->getSecondaryLink()) {
            $url = self::getImagePathTo($this->getSecondaryLink());
            if(!file_exists(self::getBaseImagePathTo($this->getSecondaryLink()))) $url = '';
        }

        if(empty($url)) {
            $url = $this->getNoImage();
        }

        return $url;

    }

    public function getThumbnailUrl() {
        $url = '';
        if($this->getThumbnail()) {
            $url = self::getImagePathTo($this->getThumbnail());
            if(!file_exists(self::getBaseImagePathTo($this->getThumbnail()))) $url = '';
        }

        if(empty($url)) {
            $url = $this->getUrl();
        }

        return $url;
    }
}
