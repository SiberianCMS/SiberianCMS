<?php

class Application_Model_Device_Iphone extends Core_Model_Default {

    const SOURCE_FOLDER = "/var/apps/iphone/Siberian";
    const DEST_FOLDER = "/var/tmp/applications/iphone/Siberian";

    protected $_current_version = '1.0.0';
    protected $_dst;
    protected $_base_dst;
    protected $_zipname;
    protected $_new_xml;

    public function getCurrentVersion() {
        return $this->_current_version;
    }

    public function getStoreName() {
        return 'App Store';
    }

    public function prepareResources() {

        $this->_cpFolder();
        $this->_preparePList();
        $this->_changeData();
        $this->_copyImages();
        $zip = $this->_zipFolder();

        return $zip;
    }

    public function getResources() {

        $umask = umask(0);

        $src = $this->prepareResources();

        umask($umask);

        return $src;

    }

    protected function _cpFolder() {

        $src = Core_Model_Directory::getBasePathTo(self::SOURCE_FOLDER);
        $dst = Core_Model_Directory::getBasePathTo(self::DEST_FOLDER);

        // Supprime le dossier s'il existe puis le créé
        if(is_dir($dst)) Core_Model_Directory::delete($dst);
        mkdir($dst, 0775, true);

        // Copie les sources
        Core_Model_Directory::duplicate($src, $dst);

        $this->_zipname = 'ios_source';
        $this->_dst = $dst.'/Siberian';

        $this->_base_dst = $dst;
        return $this;

    }

    protected function _preparePList() {

        $file = $this->_dst.'/Siberian-Info.plist';
        $xml = simplexml_load_file($file);
        $str = '<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd"><plist version="1.0"><dict></dict></plist>';
        $this->_new_xml = simplexml_load_string($str);
        $this->_parsePList($xml->dict, $this->_new_xml->dict, $this->_new_xml);

        $plist = fopen($file, 'w+');
        if(!$plist) {
            throw new Exception('An error occured while copying the source files ('.$file.')');
        }

        $r = fwrite($plist, $this->_new_xml->asXml());
        fclose($plist);

        return $this;

    }

    protected function _parsePList($node, $newNode) {

        $lastValue = '';
        foreach($node->children() as $key => $child) {

            $value = (string) $child;
            if(count($child->children()) > 0) {
                $this->_parsePList($child, $newNode->addChild($key));
            } else {
                if($lastValue == 'CFBundleDisplayName') {
                    $value = $this->getApplication()->getName();
                }
                else if($lastValue == 'CFBundleIdentifier') {
                    $value = $this->getApplication()->getBundleId();
                }

                $newNode->addChild($key, $value);
                $lastValue = $value;
            }
        }

    }

    protected function _changeData() {

        // Créé les variables et ouvre le fichier
        $file = $this->_dst.'/Application/Objects/url.m';
        $newContent = '';
        $common = @fopen($file, 'r+');
        if(!$common) {
            throw new Exception('An error occured while processing the file '.$file);
        }

        if(!$uri = parse_url($this->getApplication()->getUrl())) {
            throw new Exception("An error occured while parsing the application's URL. Please check the URL and try again.");
        }
        if(empty($uri['scheme']) OR empty($uri['host'])) {
            throw new Exception($this->_("An error occured while parsing the application's URL. Please check the URL and try again."));
        }

        $scheme = $uri['scheme'];
        $domain = $uri['host'];
        $path = ltrim(str_replace(Core_Model_Language::getLanguageCodes(), '', $uri['path']), '/');

        while($data = fgets($common, 1024)) {
            if(stripos($data, 'scheme = @"') !== false)      $newContent .= '        scheme = @"'.$scheme.'";
';
            else if(stripos($data, 'domain = @"') !== false) $newContent .= '        domain = @"'.$domain.'";
';
            else if(stripos($data, 'path = @"') !== false)   $newContent .= '        path = @"'.$path.'";
';
            else $newContent .= $data;
        }

        fclose($common);

        // Met à jour le contenu du fichier
        $common = @fopen($file, 'w');
        fputs($common, $newContent);
        fclose($common);

        // Met à jour le contenu du fichier
        $common = @fopen($file, 'w');
        fputs($common, $newContent);
        fclose($common);
    }

    protected function _copyImages() {

        $application = $this->getApplication();

        // Touch Icon
        $icons = array(
            $application->getIcon(29, null, true)   => $this->_dst.'/Resources/Images/TouchIcon/SpotlightIcon.png',
            $application->getIcon(58, null, true)   => $this->_dst.'/Resources/Images/TouchIcon/SpotlightIcon@2x.png',
            $application->getIcon(80, null, true)   => $this->_dst.'/Resources/Images/TouchIcon/SpotlightIcon-iOS7.png',
            $application->getIcon(57, null, true)   => $this->_dst.'/Resources/Images/TouchIcon/TouchIcon.png',
            $application->getIcon(114, null, true)  => $this->_dst.'/Resources/Images/TouchIcon/TouchIcon@2x.png',
            $application->getIcon(120, null, true)  => $this->_dst.'/Resources/Images/TouchIcon/TouchIcon-iOS7.png',
            $application->getAppStoreIcon(true)     => $this->_dst.'/../TouchIcon.png',
        );

        foreach($icons as $icon_src => $icon_dst) {
            if(!@rename($icon_src, $icon_dst)) {
                throw new Exception($this->_('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
            }
        }


        // Startup Images
        $startup_src_normal = $application->getStartupImageUrl('normal', true);
        $startup_src_retina = $application->getStartupImageUrl('retina', true);
        $startup_dst = $this->_dst .'/Resources/Images/Startup/Default.png';
        $startup2_dst = $this->_dst .'/Resources/Images/Startup/Default@2x.png';
        $startup568h_dst = $this->_dst .'/Resources/Images/Startup/Default-568h@2x.png';


        try {
            // Startup image: 320x480
            list($width, $height) = getimagesize($startup_src_normal);
            $newStartupImage = imagecreatetruecolor(320, 480);
            imagecopyresized($newStartupImage, imagecreatefrompng($startup_src_normal), 0, 0, 0, 0, 320, 480, $width, $height);
            imagepng($newStartupImage, $startup_dst);
        }
        catch(Exception $e) {
            throw new Exception('An error occured while resizing the startup image. Please check the image, try to send it again and try again.');
        }

        try {
            // Startup image: 640x960
            list($width, $height) = getimagesize($startup_src_normal);
            $newStartupImage = imagecreatetruecolor(640, 960);
            imagecopyresized($newStartupImage, imagecreatefrompng($startup_src_normal), 0, 0, 0, 0, 640, 960, $width, $height);
            imagepng($newStartupImage, $startup2_dst);
        }
        catch(Exception $e) {
            throw new Exception('An error occured while resizing the startup image. Please check the image, try to send it again and try again.');
        }

        try {
            // Startup image: 640x1136
            list($width, $height) = getimagesize($startup_src_retina);
            $newStartupImage = imagecreatetruecolor(640, 1136);
            imagecopyresized($newStartupImage, imagecreatefrompng($startup_src_retina), 0, 0, 0, 0, 640, 1136, $width, $height);
            imagepng($newStartupImage, $startup568h_dst);
        }
        catch(Exception $e) {
            throw new Exception('An error occured while resizing the startup image. Please check the image, try to send it again and try again.');
        }

    }

    protected function _zipFolder() {

        $src = $this->_base_dst;
        $name = $this->_zipname;

        Core_Model_Directory::zip($this->_base_dst, $src.'/'.$this->_zipname.'.zip');
//        shell_exec('cd "'.$src.'"; zip -r ./'.$name.'.zip ./*');
//
//        if(!file_exists($src.'/'.$name.'.zip')) {
//            throw new Exception('An error occured while creating the archive ('.$src.'/'.$name.'.zip)');
//        }

        return $src.'/'.$name.'.zip';

    }

}
