<?php

class Application_Model_Device_Android extends Core_Model_Default {

    const SOURCE_FOLDER = "/var/apps/android/Siberian";
    const DEST_FOLDER = "/var/tmp/applications/android/Siberian";

    protected $_current_version = '1.0.0';
    protected $_formatted_name = '';
    protected $_formatted_bundle_name = '';
    protected $_dst;
    protected $_sources_dst;
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
        $this->_sources_dst = "$dst/app/src/main";

        $src = $this->_sources_dst.'/java/com/siberiancms/app';
        $dst = $this->_sources_dst.'/java/com/'.$this->_formatted_bundle_name.'/'.$this->_formatted_name;

        Core_Model_Directory::move($src, $dst);
        Core_Model_Directory::delete($this->_sources_dst.'/java/com/siberiancms');

        return $this;

    }

    protected function _prepareFiles() {

        $source = $this->_sources_dst.'/java/com/'.$this->_formatted_bundle_name.'/'.$this->_formatted_name;
        $links = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, 4096), RecursiveIteratorIterator::SELF_FIRST);
        $url = $this->getUrl();
        $allowed_extensions = array("java", "xml");

        if(!$links) return $this;

        foreach($links as $link) {
            if(!$link->isDir() AND in_array($link->getExtension(), $allowed_extensions)) {
                if(strpos($link, 'CommonUtilities.java') !== false) {
                    $this->__replace(array(
                        'String SENDER_ID = ""' => 'String SENDER_ID = "'.Push_Model_Certificat::getAndroidSenderId().'"',
                        'SERVEUR_URL = "http://www.siberiancms.com/";' => 'SERVEUR_URL = "'.$this->getUrl().'";'
                    ), $link);
                }
            }
        }

        $source = $this->_dst;
        $links = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, 4096), RecursiveIteratorIterator::SELF_FIRST);
        foreach($links as $link) {
            if($link->isDir()) continue;
            $this->__replace(array('siberiancms.app' => $this->_formatted_bundle_name.'.'.$this->_formatted_name), $link->getRealPath());
        }

        $this->__replace(array('siberiancms.app' => $this->_formatted_bundle_name.'.'.$this->_formatted_name), $this->_sources_dst.'/AndroidManifest.xml');

        $name = str_replace(array('&', '/'), 'AND', $this->getApplication()->getName());


//        $this->__replace(array('<name>Siberian</name>' => '<name>'.$name.'</name>'), $this->_dst.'/.project');
        $replacements = array(
            'http://localhost/overview' => $this->getApplication()->getUrl(null, array(), false, 'en'),
            '<string name="app_name">SiberianCMS</string>' => '<string name="app_name">'.$name.'</string>',
        );
        $this->__replace($replacements, $this->_sources_dst.'/res/values/strings.xml');

        foreach(Core_Model_Language::getLanguageCodes() as $lang) {
            if($lang != 'en') {
                $replacements = array(
                    'http://localhost/overview' => $this->getApplication()->getUrl(null, array(), false, $lang),
                    '<string name="app_name">SiberianCMS</string>' => '<string name="app_name">'.$name.'</string>',
                );

                $this->__replace($replacements, $this->_sources_dst.'/res/values-'.$lang.'/strings.xml');
            }
        }

        return $this;

    }

    protected function _copyImages() {

        $application = $this->getApplication();
        $icons = array(
            $this->_sources_dst.'/res/drawable-mdpi/app_icon.png'    => $application->getIcon(48, null, true),
            $this->_sources_dst.'/res/drawable-mdpi/push_icon.png'   => $application->getIcon(24, null, true),
            $this->_sources_dst.'/res/drawable-hdpi/app_icon.png'    => $application->getIcon(72, null, true),
            $this->_sources_dst.'/res/drawable-hdpi/push_icon.png'   => $application->getIcon(36, null, true),
            $this->_sources_dst.'/res/drawable-xhdpi/app_icon.png'   => $application->getIcon(96, null, true),
            $this->_sources_dst.'/res/drawable-xhdpi/push_icon.png'  => $application->getIcon(48, null, true),
            $this->_sources_dst.'/res/drawable-xxhdpi/app_icon.png'  => $application->getIcon(144, null, true),
            $this->_sources_dst.'/res/drawable-xxhdpi/push_icon.png' => $application->getIcon(72, null, true),
            $this->_dst.'/app_icon.png' => $application->getIcon(512, null, true),
        );

        foreach($icons as $icon_dst => $icon_src) {
            if(!@copy($icon_src, $icon_dst)) {
                throw new Exception($this->_('An error occured while copying your app icon. Please check the icon, try to send it again and try again.'));
            }
        }

        return $this;
    }

    protected function _zipFolder() {

        $src = $this->_dst;

        Core_Model_Directory::zip($src, $src.'/'.$this->_zipname.'.zip');

        if(!file_exists($src.'/'.$this->_zipname.'.zip')) {
            throw new Exception('Une erreur est survenue lors de la création de l\'archive ('.$src.'/'.$this->_zipname.'.zip)');
        }

        return $src.'/'.$this->_zipname.'.zip';

    }

    private function __replace($replacement, $in, $print = false) {

        $contents = @file_get_contents($in);
        if(!$contents) {
            throw new Exception('Un problème est survenue lors de la copie des fichiers sources ('.$in.')');
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
