<?php

class Core_Model_Translator
{

    public static $_translator;

    public static function prepare($module_name) {

        $current_language = Core_Model_Language::getCurrentLanguage();

        if(!file_exists(Core_Model_Directory::getBasePathTo("/languages/$current_language/default.csv"))) return;

        self::$_translator = new Zend_Translate(array(
            'adapter' => 'csv',
            'content' => Core_Model_Directory::getBasePathTo("/languages/$current_language/default.csv"),
            'locale' => $current_language
        ));

        if(file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/default.csv"))) {
            self::$_translator->addTranslation(array(
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/default.csv"),
                'locale' => $current_language
            ));
        }

        if($module_name != 'application') {
            self::addModule('application');
        }

        self::addModule($module_name);

        return;

    }

    public static function addModule($module_name) {

        $current_language = Core_Model_Language::getCurrentLanguage();
        if(file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/{$module_name}.csv"))) {
            self::$_translator->addTranslation(array(
                'content' => Core_Model_Directory::getBasePathTo("/languages/$current_language/{$module_name}.csv"),
                'locale' => $current_language
            ));
        }
        if(file_exists(Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/{$module_name}.csv"))) {
            self::$_translator->addTranslation(array(
                'content' => Core_Model_Directory::getBasePathTo("/languages/{$current_language}/emails/{$module_name}.csv"),
                'locale' => $current_language
            ));
        }

    }

    public static function translate($text, array $args = array()) {

        $translator = self::$_translator;
        if(count($args) > 1) unset($args[0]);

        $text = stripslashes($text);
        if(!is_null($translator)) $text = $translator->_(trim($text));

        if(count($args) > 0) {
            while(count($args) < substr_count($text, '%s')) $args[] = '';
            array_unshift($args, $text);
            $text = call_user_func_array('sprintf', $args);
        }

        return $text;
    }
}