<?php

class Application_Model_Device_Iphone extends Core_Model_Default {

    const SOURCE_FOLDER = "/var/apps/iphone/Siberian";
    const DEST_FOLDER = "/var/tmp/applications/iphone/Siberian";

    protected $_current_version = '1.0.0';
    protected $_dst;
    protected $_base_dst;
    protected $_zipname;
    protected $_new_xml;
    protected $_request;

    public function getCurrentVersion() {
        return $this->_current_version;
    }

    public function getStoreName() {
        return 'App Store';
    }

    public function prepareResources() {

        $this->_prepareRequest();
        $this->_cpFolder();
        $this->_preparePList();
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

    protected function _prepareRequest() {
        $request = new Siberian_Controller_Request_Http($this->getApplication()->getUrl());
        $request->setPathInfo();
        $this->_request = $request;
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
        $this->_dst = $dst.'/SiberianCMS';

        $this->_base_dst = $dst;
        return $this;

    }

    protected function _preparePList() {

        $file = $this->_dst.'/SiberianCMS-Info.plist';
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
                } else if($lastValue == "Default URL") {
                    $value = $this->getApplication()->getUrl();
                } else if(stripos($lastValue, "url_") !== false) {
                    $value = $this->__getUrlValue($lastValue);
                }

                $newNode->addChild($key, $value);
                $lastValue = $value;
            }
        }

    }

    protected function _copyImages() {

        $application = $this->getApplication();

        // Touch Icon
        $icons = array(
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/29x29.png'    => $application->getIcon(29, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/29x29@2x.png' => $application->getIcon(58, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/29x29@3x.png' => $application->getIcon(87, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/40x40@2x.png' => $application->getIcon(80, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/40x40@3x.png' => $application->getIcon(120, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/57x57.png'    => $application->getIcon(57, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/57x57@2x.png' => $application->getIcon(114, null, true),
            // $this->_dst.'/Images.xcassets/AppIcon.appiconset/60x60@2x.png' => $application->getIcon(120, null, true),
            $this->_dst.'/Images.xcassets/AppIcon.appiconset/60x60@3x.png' => $application->getIcon(180, null, true),
            $this->_dst.'/../TouchIcon.png' => $application->getAppStoreIcon(true)
        );

        foreach($icons as $icon_dst => $icon_src) {
            if(!@rename($icon_src, $icon_dst)) {
                Zend_Debug::dump(file_exists($icon_src));
                Zend_Debug::dump($this->_($icon_src . " => " . $icon_dst));
                Zend_Debug::dump($application->getIcon(120, null, true));
                Zend_Debug::dump($application->getIcon(60, null, true));
                die;
                throw new Exception($this->_('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
            }
        }
        
        if(!@rename($application->getIcon(120, null, true), $this->_dst.'/Images.xcassets/AppIcon.appiconset/60x60@2x.png')) {
            throw new Exception($this->_('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
        }


        // Startup Images
        $startup_src_normal = $application->getStartupImageUrl('normal', true);
        $startup_src_retina = $application->getStartupImageUrl('retina', true);
        $startup_dst = $this->_dst .'/Images.xcassets/LaunchImage.launchimage/Default.png';
        $startup2_dst = $this->_dst .'/Images.xcassets/LaunchImage.launchimage/Default@2x.png';
        $startup_ios7_2_dst = $this->_dst .'/Images.xcassets/LaunchImage.launchimage/Default-iOS7@2x.png';
        $startup568h_dst = $this->_dst .'/Images.xcassets/LaunchImage.launchimage/Default-568h@2x.png';
        $startup_ios7_568h_dst = $this->_dst .'/Images.xcassets/LaunchImage.launchimage/Default-iOS7-568h@2x.png';


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
            copy($startup2_dst, $startup_ios7_2_dst);
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
            copy($startup568h_dst, $startup_ios7_568h_dst);
        }
        catch(Exception $e) {
            throw new Exception('An error occured while resizing the startup image. Please check the image, try to send it again and try again.');
        }

    }

    protected function _zipFolder() {

        $src = $this->_base_dst;
        $name = $this->_zipname;

        Core_Model_Directory::zip($this->_base_dst, $src.'/'.$this->_zipname.'.zip');

        return $src.'/'.$name.'.zip';

    }

    private function __getUrlValue($key) {

        switch($key) {
            case "url_scheme": $value = $this->_request->getScheme(); break;
            case "url_domain": $value = $this->_request->getHttpHost(); break;
            case "url_path": $value = ltrim($this->_request->getBaseUrl(), "/"); break;
            case "url_key":
                if($this->_request->useApplicationKey()) {
                    $value = Application_Model_Application::OVERVIEW_PATH;
                }
                break;
            default: $value = "";
        }

        return $value;
    }

}
