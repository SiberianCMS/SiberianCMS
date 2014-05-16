<?php

// Créé les tables
$this->query("
    CREATE TABLE `wordpress` (
        `wp_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`wp_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `wordpress_category` (
        `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `wp_id` int(11) unsigned NOT NULL,
        `wp_category_id` int(11) NOT NULL,
        `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`category_id`),
        KEY `KEY_WP_ID` (`wp_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `wordpress`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `wordpress_category`
        ADD FOREIGN KEY `FK_WP_ID` (`wp_id`) REFERENCES `wordpress` (`wp_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('Wordpress')->save();

$icon_paths = array(
    '/wordpress/wordpress1.png'
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
    'code' => 'wordpress',
    'name' => 'Wordpress',
    'model' => 'Wordpress_Model_Wordpress',
    'desktop_uri' => 'wordpress/application/',
    'mobile_uri' => 'wordpress/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 170
        );
$option = new Application_Model_Option();
$option->setData($datas)->save();
