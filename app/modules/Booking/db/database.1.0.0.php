<?php

$this->query("
    CREATE TABLE `booking` (
        `booking_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `value_id` INT(11) UNSIGNED NOT NULL
    ) ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `booking_store` (
        `store_id` INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
        `booking_id` INT(11) NOT NULL,
        `store_name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL,
        KEY `KEY_BOOKING_ID` (`booking_id`)
    ) ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_unicode_ci;
");


$library = new Media_Model_Library();
$library->setName('Booking')->save();

$icon_paths = array(
    'booking1.png',
    'booking2.png',
    'booking3.png',
    'booking4.png',
    'booking5.png',
    'booking6.png',
    'booking7.png',
    'booking8.png',
    'booking9.png',
    'booking10.png',
    'booking11.png'
);

$icon_id = 0;
foreach($icon_paths as $key => $icon_path) {
    $datas = array('library_id' => $library->getId(), 'link' => '/booking/'.$icon_path, 'can_be_colorized' => 1);
    $image = new Media_Model_Library_Image();
    $image->setData($datas)->save();

    if($key == 0) $icon_id = $image->getId();
}

$datas = array(
    'library_id' => $library->getId(),
    'icon_id' => $icon_id,
    'code' => 'booking',
    'name' => 'Booking',
    'model' => 'Booking_Model_Booking',
    'desktop_uri' => 'booking/application/',
    'mobile_uri' => 'booking/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 140
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
