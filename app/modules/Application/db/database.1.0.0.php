<?php

$this->query("
    CREATE TABLE `application` (
        `app_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `layout_id` int(11) unsigned NOT NULL,
        `design_id` INT(11) unsigned NULL DEFAULT NULL,
        `bundle_id` varchar(100) DEFAULT NULL,
        `locale` varchar(6) DEFAULT NULL,
        `tabbar_account_name` varchar(30) DEFAULT NULL,
        `tabbar_more_name` varchar(30) DEFAULT NULL,
        `country_code` varchar(5) DEFAULT NULL,
        `name` varchar(30) DEFAULT NULL,
        `description` longtext NOT NULL,
        `keywords` varchar(255) DEFAULT NULL,
        `background_image` varchar(255) DEFAULT NULL,
        `background_image_color` varchar(255) DEFAULT NULL,
        `font_family` varchar(30) DEFAULT NULL,
        `homepage_background_image_id` int(11) unsigned DEFAULT NULL,
        `homepage_background_image_link` varchar(255) DEFAULT NULL,
        `homepage_background_image_retina_link` varchar(255) DEFAULT NULL,
        `use_homepage_background_image_in_subpages` tinyint(1) NOT NULL DEFAULT '0',
        `logo` varchar(255) DEFAULT NULL,
        `icon` varchar(255) DEFAULT NULL,
        `startup_image` varchar(255) DEFAULT NULL,
        `startup_image_retina` varchar(255) DEFAULT NULL,
        `domain` varchar(100) DEFAULT NULL,
        `subdomain` varchar(20) DEFAULT NULL,
        `subdomain_is_validated` tinyint(1) DEFAULT NULL,
        `facebook_token` varchar(255) DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`app_id`),
        UNIQUE KEY `UNIQUE_KEY_SUBDOMAIN_DOMAIN` (`subdomain`,`domain`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `application_layout_homepage` (
        `layout_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(50) NOT NULL,
        `preview` varchar(255) NOT NULL,
        `use_more_button` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`layout_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("

    CREATE TABLE `application_option` (
        `option_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `library_id` int(11) unsigned NOT NULL,
        `icon_id` int(11) NOT NULL,
        `code` varchar(20) NOT NULL,
        `name` varchar(25) NOT NULL,
        `model` varchar(100) NOT NULL,
        `desktop_uri` varchar(100) NOT NULL,
        `mobile_uri` varchar(100) NOT NULL,
        `only_once` tinyint(1) NOT NULL DEFAULT '0',
        `is_ajax` tinyint(1) NOT NULL DEFAULT '1',
        `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`option_id`),
        KEY `KEY_LIBRARY_ID` (`library_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `application_option_value` (
        `value_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `option_id` int(11) unsigned NOT NULL,
        `layout_id` int(11) unsigned NOT NULL DEFAULT '1',
        `icon_id` int(11) DEFAULT NULL,
        `folder_category_id` int(11) unsigned DEFAULT NULL,
        `folder_category_position` int(11) unsigned DEFAULT NULL,
        `tabbar_name` varchar(30) DEFAULT NULL,
        `icon` varchar(255) DEFAULT NULL,
        `background_image` varchar(255) DEFAULT NULL,
        `is_visible` tinyint(1) NOT NULL DEFAULT '1',
        `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `is_active` tinyint(1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`value_id`),
        KEY `KEY_OPTION_ID` (`option_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `application_option_value`
        ADD FOREIGN KEY `FK_OPTION_ID` (`option_id`) REFERENCES `application_option` (`option_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$datas = array(
    array('name' => 'Layout 1', 'preview' => '/customization/layout/homepage/layout_1.png', 'use_more_button' => 1, 'position' => 10),
    array('name' => 'Layout 2', 'preview' => '/customization/layout/homepage/layout_2.png', 'use_more_button' => 1, 'position' => 20),
    array('name' => 'Layout 4', 'preview' => '/customization/layout/homepage/layout_3.png', 'use_more_button' => 0, 'position' => 30),
    array('name' => 'Layout 5', 'preview' => '/customization/layout/homepage/layout_4.png', 'use_more_button' => 0, 'position' => 40),
    array('name' => 'Layout 6', 'preview' => '/customization/layout/homepage/layout_5.png', 'use_more_button' => 0, 'position' => 50),
    array('name' => 'Layout 7', 'preview' => '/customization/layout/homepage/layout_6.png', 'use_more_button' => 0, 'position' => 60),
    array('name' => 'Layout 8', 'preview' => '/customization/layout/homepage/layout_7.png', 'use_more_button' => 0, 'position' => 70),
);

foreach($datas as $data) {
    $layout = new Application_Model_Layout_Homepage();
    $layout->setData($data)->save();
}

