<?php

class Application_Model_Device_Android extends Core_Model_Default {

    const SOURCE_FOLDER = "/var/apps/android/Siberian";
    const DEST_FOLDER = "/var/tmp/applications/android/Siberian";

    protected $_current_version = '1.0.0';
    protected $_formatted_name = '';
    protected $_formatted_bundle_name = '';
    protected $_dst;
    protected $_base_dst;
    protected $_zipname;

    public function getCurrentVersion() {
        return $this->_current_version;
    }

    public function getStoreName() {
        return 'Google Play';
    }

    public function getResources() {

        $umask = umask(0);

        $src = $this->prepareResources();

        umask($umask);

        return $src;

    }

    public function prepareResources() {

        $this->_cpFolder();
        $this->_prepareFiles();
        $this->_copyImages();
        $zip = $this->_zipFolder();

        return $zip;
    }

    protected function _cpFolder() {

        $this->_formatted_name = Core_Model_Lib_String::format($this->getApplication()->getName(), true);
        $this->_formatted_bundle_name = $this->_formatted_name;

        $src = Core_Model_Directory::getBasePathTo(self::SOURCE_FOLDER);
        $dst = Core_Model_Directory::getBasePathTo(self::DEST_FOLDER);

        // Supprime le dossier s'il existe puis le créé
        if(is_dir($dst)) Core_Model_Directory::delete($dst);
        mkdir($dst, 0775, true);

        // Copie les sources
        Core_Model_Directory::duplicate($src, $dst);

        $this->_zipname = 'android_source';

        $this->_dst = $dst;

        $src = $this->_dst.'/src/com/siberian/app';
        $dst = $this->_dst.'/src/com/'.$this->_formatted_bundle_name.'/'.$this->_formatted_name;
        Core_Model_Directory::move($src, $dst);
        Core_Model_Directory::delete($this->_dst.'/src/com/siberian');

        return $this;

    }

    protected function _prepareFiles() {

        $links = glob($this->_dst.'/src/com/'.$this->_formatted_bundle_name.'/'.$this->_formatted_name.'/*');
        $url = $this->getUrl();

        if(!$links) return $this;

        $links = array_merge(
            array($this->_dst.'/AndroidManifest.xml'),
            $links
        );

        foreach($links as $link) {
            if(!is_dir($link)) {
                $this->__replace(array('siberian.app' => $this->_formatted_bundle_name.'.'.$this->_formatted_name), $link);
                if(strpos($link, 'CommonUtilities.java') !== false) {
                    $this->__replace(array(
                        'String SENDER_ID = ""' => 'String SENDER_ID = "'.Push_Model_Certificat::getAndroidSenderId().'"',
                        'SERVEUR_URL = "http://www.siberiancms.com/";' => 'SERVEUR_URL = "'.$this->getUrl().'";'
                    ), $link);
                }
            }
        }

        $name = str_replace(array('&', '/'), 'AND', $this->getApplication()->getName());

        $this->__replace(array('<name>Siberian</name>' => '<name>'.$name.'</name>'), $this->_dst.'/.project');
        $replacements = array(
            'http://app.siberiancms.com' => $this->getApplication()->getUrl(null, array(), false, 'en'),
            '<string name="app_name">Siberian</string>' => '<string name="app_name">'.$name.'</string>',
        );
        $this->__replace($replacements, $this->_dst.'/res/values/strings.xml');

        foreach(Core_Model_Language::getLanguageCodes() as $lang) {
            if($lang != 'en') {
                $replacements = array(
                    'http://app.siberiancms.com' => $this->getApplication()->getUrl(null, array(), false, $lang),
                    '<string name="app_name">Siberian</string>' => '<string name="app_name">'.$name.'</string>',
                );
                $this->__replace($replacements, $this->_dst.'/res/values-'.$lang.'/strings.xml');
            }
        }

        return $this;

    }

    protected function _copyImages() {

        // Touch Icon
        $application = $this->getApplication();
        $icon_src = Core_Model_Directory::getBasePathTo($this->getApplication()->getIcon());
        $icons = array(
            $application->getIcon(36, null, true)  => $this->_dst .'/res/drawable-ldpi/app_icon.png',
            $application->getIcon(19, null, true)  => $this->_dst .'/res/drawable-ldpi/push_icon.png',
            $application->getIcon(48, null, true)  => $this->_dst .'/res/drawable-mdpi/app_icon.png',
            $application->getIcon(25, null, true)  => $this->_dst .'/res/drawable-mdpi/push_icon.png',
            $application->getIcon(72, null, true)  => $this->_dst .'/res/drawable-hdpi/app_icon.png',
            $application->getIcon(38, null, true)  => $this->_dst .'/res/drawable-hdpi/push_icon.png',
            $application->getIcon(96, null, true)  => $this->_dst .'/res/drawable-xhdpi/app_icon.png',
            $application->getIcon(50, null, true)  => $this->_dst .'/res/drawable-xhdpi/push_icon.png',
            $application->getIcon(512, null, true) => $this->_dst .'/app_icon.png',
        );

        foreach($icons as $icon_src => $icon_dst) {
            if(!@copy($icon_src, $icon_dst)) {
                throw new Exception($this->_('An error occurred while copying your app icon. Please check the icon, try to send it again and try again.'));
            }
        }

        return $this;
    }

    protected function _zipFolder() {

        $src = $this->_dst;

        Core_Model_Directory::zip($src, $src.'/'.$this->_zipname.'.zip');

        if(!file_exists($src.'/'.$this->_zipname.'.zip')) {
            throw new Exception($this->_("An error occurred while creating the archive (%s)", $src."/".$this->_zipname.".zip"));
        }

        return $src.'/'.$this->_zipname.'.zip';

    }

    private function __replace($replacement, $in, $print = false) {

        $contents = @file_get_contents($in);
        if(!$contents) {
            throw new Exception($this->_("An error occurred while copying the source files (%s)", $in));
        }

        if($print) {
            Zend_Debug::dump(filesize($in));
            Zend_Debug::dump($in);
            Zend_Debug::dump($contents);
            die;
        }
        foreach($replacement as $that => $with) {
            if($print) {
                Zend_Debug::dump($that);
                Zend_Debug::dump($with);
            }
            $contents = str_replace($that, $with, $contents);
//            if($print) Zend_Debug::dump($contents);
        }
        $file = fopen($in, 'w');
        fwrite($file, $contents);
        fclose($file);
        if($print) die;
        return $this;
    }

}
