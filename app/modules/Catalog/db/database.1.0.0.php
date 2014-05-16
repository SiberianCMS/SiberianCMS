<?php

$this->query("
    CREATE TABLE `catalog_category` (
        `category_id` int(11) NOT NULL AUTO_INCREMENT,
        `parent_id` int(11) DEFAULT NULL,
        `value_id` int(11) unsigned NOT NULL,
        `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `position` smallint(5) NOT NULL DEFAULT '0',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`category_id`),
        KEY `KEY_VALUE_ID` (`value_id`),
        KEY `KEY_PARENT_ID` (`parent_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `catalog_product` (
        `product_id` int(11) NOT NULL AUTO_INCREMENT,
        `category_id` int(11) DEFAULT NULL,
        `value_id` int(11) unsigned NOT NULL,
        `tax_id` int(11) DEFAULT NULL,
        `type` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'basic',
        `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `description` text COLLATE utf8_unicode_ci NOT NULL,
        `picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `price` decimal(12,4) DEFAULT NULL,
        `format_quantity` tinyint(2) NOT NULL DEFAULT '0',
        `conditions` varchar(75) COLLATE utf8_unicode_ci DEFAULT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `position` smallint(5) NOT NULL DEFAULT '0',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`product_id`),
        KEY `KEY_CATEGORY_ID` (`category_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `catalog_product_format` (
      `option_id` int(11) NOT NULL AUTO_INCREMENT,
      `product_id` int(11) NOT NULL,
      `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
      `price` decimal(8,2) NOT NULL,
      PRIMARY KEY (`option_id`),
      KEY `KEY_PRODUCT_ID` (`product_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `catalog_category`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");
$this->query("
    ALTER TABLE `catalog_product`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");
$this->query("
    ALTER TABLE `catalog_product_format`
        ADD FOREIGN KEY `FK_PRODUCT_ID` (`product_id`) REFERENCES `catalog_product` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('Catalog')->save();

$icon_paths = array(
    '/catalog/catalog1.png',
    '/catalog/catalog2.png',
    '/catalog/catalog3.png',
    '/catalog/catalog4.png',
    '/catalog/catalog5.png',
    '/catalog/catalog6.png',
    '/catalog/catalog7.png',
    '/promotion/discount4.png',
    '/catalog/catalog8.png',
    '/catalog/catalog9.png',
    '/catalog/catalog10.png',
    '/catalog/catalog11.png',
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
    'code' => 'catalog',
    'name' => 'Catalog',
    'model' => 'Catalog_Model_Product',
    'desktop_uri' => 'catalog/application/',
    'mobile_uri' => 'catalog/mobile_category/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 30
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
