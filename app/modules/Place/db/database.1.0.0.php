<?php

$this->query("
    DROP TABLE IF EXISTS `place`;

    CREATE TABLE `place` (
        `place_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `identifier` VARCHAR(50) NOT NULL,
        `name` longtext COLLATE utf8_unicode_ci,
        `street` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `postcode` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
        `city` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `phone` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
        `rating` TINYINT(1) NOT NULL DEFAULT 0,
        `type_id` TINYINT(1) NOT NULL DEFAULT 0,
        `label_id` TINYINT(1) NOT NULL DEFAULT 0,
        `status_id` TINYINT(1) NOT NULL DEFAULT 0,
        `latitude` decimal(10,7) NULL DEFAULT NULL,
        `longitude` decimal(10,7) NULL DEFAULT NULL,
        `min_price` decimal(9,4) NULL DEFAULT NULL,
        `max_price` decimal(9,4) NULL DEFAULT NULL,
        `meal_min_price` decimal(9,4) NULL DEFAULT NULL,
        `meal_max_price` decimal(9,4) NULL DEFAULT NULL,
        `number_of_rooms` TINYINT(2) NOT NULL DEFAULT 0,
        `information` TEXT NULL DEFAULT NULL,
        `opening_details` VARCHAR(255) NULL DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`place_id`),
        UNIQUE `UNIQUE_IDENTIFIER` (`identifier`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$library = new Media_Model_Library();
$library->setName('Place')->save();

$icon_paths = array(
    '/contact/contact7.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $data = array('library_id' => $library->getId(), 'link' => $icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($data)->save();

    if($key == 0) $icon_id = $image->getId();
}

$data = array(
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'place',
    'name' => 'Place',
    'model' => 'Place_Model_Place',
    'desktop_uri' => 'place/application/',
    'mobile_uri' => 'place/mobile_list/',
    'only_once' => 1,
    'is_ajax' => 1,
    'position' => 125
);
$option = new Application_Model_Option();
$option->setData($data)->save();
