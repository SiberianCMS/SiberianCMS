<?php

$this->query("
    CREATE TABLE `promotion` (
        `promotion_id` int(11) NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `description` text COLLATE utf8_unicode_ci NOT NULL,
        `conditions` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `is_unique` tinyint(1) NOT NULL DEFAULT '0',
        `end_at` date DEFAULT NULL,
        `force_validation` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `condition_type` varchar(9) COLLATE utf8_unicode_ci DEFAULT NULL,
        `condition_number_of_points` tinyint(2) DEFAULT NULL,
        `condition_period_number` tinyint(2) DEFAULT NULL,
        `condition_period_type` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
        `is_shared` tinyint(1) NOT NULL DEFAULT '0',
        `owner` tinyint(1) NOT NULL DEFAULT '1',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`promotion_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `promotion_customer` (
        `promotion_customer_id` int(11) NOT NULL AUTO_INCREMENT,
        `promotion_id` int(11) NOT NULL,
        `pos_id` int(11) DEFAULT NULL,
        `customer_id` int(11) UNSIGNED NOT NULL,
        `is_used` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `number_of_error` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `created_at` datetime NOT NULL,
        `last_error` datetime DEFAULT NULL,
        PRIMARY KEY (`promotion_customer_id`),
        KEY `KEY_PROMOTION_ID` (`promotion_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `promotion`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `promotion_customer`
        ADD FOREIGN KEY `FK_PROMOTION_ID` (`promotion_id`) REFERENCES `promotion` (`promotion_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('Discount')->save();

$icon_paths = array(
    '/discount/discount1.png',
    '/discount/discount2.png',
    '/discount/discount3.png',
    '/discount/discount4.png',
    '/discount/discount5.png',
    '/loyalty/loyalty6.png',
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
    'code' => 'discount',
    'name' => 'Discount',
    'model' => 'Promotion_Model_Promotion',
    'desktop_uri' => 'promotion/application/',
    'mobile_uri' => 'promotion/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 20
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
