<?php

$this->query("
    CREATE TABLE `contact` (
        `contact_id` int(11) NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `name` longtext COLLATE utf8_unicode_ci,
        `description` longtext COLLATE utf8_unicode_ci,
        `facebook` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `twitter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `website` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `cover` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `civility` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
        `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `street` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `postcode` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
        `city` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `country` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `phone` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`contact_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `contact`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('Contact')->save();

$icon_paths = array(
    '/contact/contact1.png',
    '/contact/contact2.png',
    '/contact/contact3.png',
    '/contact/contact4.png',
    '/contact/contact5.png',
    '/contact/contact6.png',
    '/contact/contact7.png',
    '/contact/contact8.png',
    '/contact/contact9.png',
    '/contact/contact10.png',
    '/contact/contact11.png'
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
    'code' => 'contact',
    'name' => 'Contact',
    'model' => 'Contact_Model_Contact',
    'desktop_uri' => 'contact/application/',
    'mobile_uri' => 'contact/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 120
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
