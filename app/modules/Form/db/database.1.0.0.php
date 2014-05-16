<?php

// Créé la table de base
$this->query("
    CREATE TABLE `form` (
        `form_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`form_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `form_field` (
        `field_id` int(11) NOT NULL AUTO_INCREMENT,
        `section_id` int(11) NOT NULL,
        `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `option` text COLLATE utf8_unicode_ci NOT NULL,
        `required` tinyint(4) NOT NULL DEFAULT '0',
        `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `position` smallint(5) NOT NULL DEFAULT '0',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`field_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `form_section` (
        `section_id` int(11) NOT NULL AUTO_INCREMENT,
        `value_id` int(10) unsigned NOT NULL,
        `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`section_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

");


$library = new Media_Model_Library();
$library->setName('Form')->save();

$icon_paths = array(
    '/form/form1.png',
    '/form/form2.png',
    '/form/form3.png',
    '/calendar/calendar1.png',
    '/catalog/catalog6.png',
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

$datas = array(
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'form',
    'name' => 'Form',
    'model' => 'Form_Model_Form',
    'desktop_uri' => 'form/application/',
    'mobile_uri' => 'form/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 190
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
