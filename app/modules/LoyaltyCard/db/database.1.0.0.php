<?php

$this->query("

    CREATE TABLE `loyalty_card` (
        `card_id` int(11) NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `number_of_points` smallint(5) NOT NULL,
        `advantage` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `conditions` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        KEY `KEY_VALUE_ID` (`value_id`),
        PRIMARY KEY (`card_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `loyalty_card_customer` (
        `customer_card_id` int(11) NOT NULL AUTO_INCREMENT,
        `card_id` int(11) NOT NULL,
        `customer_id` int(11) UNSIGNED NOT NULL,
        `number_of_points` smallint(5) NOT NULL,
        `is_used` tinyint(1) NOT NULL DEFAULT '0',
        `number_of_error` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `validate_by` int(11) DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `used_at` datetime DEFAULT NULL,
        `last_error` datetime DEFAULT NULL,
        PRIMARY KEY (`customer_card_id`),
        KEY `KEY_CARD_ID` (`card_id`),
        KEY `KEY_CUSTOMER_ID` (`customer_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `loyalty_card_customer_log` (
        `log_id` int(11) NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) UNSIGNED NOT NULL,
        `card_id` int(11) NOT NULL,
        `password_id` int(11) NOT NULL,
        `number_of_points` smallint(5) unsigned NOT NULL DEFAULT '1',
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`log_id`),
        KEY `KEY_CUSTOMER_ID` (`customer_id`),
        KEY `KEY_CARD_ID` (`card_id`),
        KEY `KEY_PASSWORD_ID` (`password_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `loyalty_card_password` (
        `password_id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`password_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `loyalty_card`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `loyalty_card_customer`
        ADD FOREIGN KEY `FK_CARD_ID` (`card_id`) REFERENCES `loyalty_card` (`card_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `loyalty_card_customer_log`
        ADD FOREIGN KEY `FK_CARD_ID` (`card_id`) REFERENCES `loyalty_card` (`card_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('Loyalty Card')->save();

$icon_paths = array(
    '/loyalty/loyalty1.png',
    '/loyalty/loyalty2.png',
    '/loyalty/loyalty3.png',
    '/loyalty/loyalty4.png',
    '/loyalty/loyalty5.png',
    '/loyalty/loyalty6.png'
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
    'code' => 'loyalty',
    'name' => 'Loyalty Card',
    'model' => 'LoyaltyCard_Model_LoyaltyCard',
    'desktop_uri' => 'loyaltycard/application/',
    'mobile_uri' => 'loyaltycard/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 50
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
