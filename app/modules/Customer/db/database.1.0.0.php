<?php

$this->query("
    CREATE TABLE `customer` (
        `customer_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `civility` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
        `firstname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `lastname` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `show_in_social_gaming` tinyint(1) NOT NULL DEFAULT '1',
        `is_active` tinyint(1) NOT NULL DEFAULT '1',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`customer_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE IF NOT EXISTS `customer_address` (
        `address_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `customer_id` INT(11) UNSIGNED NOT NULL,
        `street` VARCHAR(255) NOT NULL,
        `postcode` VARCHAR(10) NOT NULL,
        `city` VARCHAR(100) NOT NULL,
        `comment` VARCHAR(255) NULL DEFAULT NULL,
        `type` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` DATETIME NOT NULL,
        `updated_at` DATETIME NOT NULL,
        PRIMARY KEY (`address_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `customer_social` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) UNSIGNED NOT NULL,
        `type` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
        `social_id` bigint(13) NOT NULL,
        `datas` text COLLATE utf8_unicode_ci,
        PRIMARY KEY (`id`),
        UNIQUE KEY `UNIQUE_KEY_SOCIAL_ID` (`social_id`),
        KEY `KEY_CUSTOMER_ID` (`customer_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `customer_social_post` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) UNSIGNED NOT NULL,
        `customer_message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `message_type` tinyint(1) unsigned NOT NULL DEFAULT '1',
        `points` tinyint(2) NOT NULL DEFAULT '1',
        PRIMARY KEY (`id`),
        KEY `KEY_CUSTOMER_ID` (`customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `customer_address`
        ADD FOREIGN KEY `FK_CUSTOMER_ID` (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `customer_social`
        ADD FOREIGN KEY `FK_CUSTOMER_ID` (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `customer_social_post`
        ADD FOREIGN KEY `FK_CUSTOMER_ID` (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

