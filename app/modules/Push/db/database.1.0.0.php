<?php

$this->query("
    CREATE TABLE `push_apns_devices` (
        `device_id` int(11) NOT NULL AUTO_INCREMENT,
        `app_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `app_version` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
        `device_uid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `last_known_latitude` decimal(11,8) DEFAULT NULL,
        `last_known_longitude` decimal(11,8) DEFAULT NULL,
        `device_token` char(64) COLLATE utf8_unicode_ci NOT NULL,
        `device_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `device_model` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `device_version` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
        `push_badge` enum('disabled','enabled') COLLATE utf8_unicode_ci DEFAULT 'disabled',
        `push_alert` enum('disabled','enabled') COLLATE utf8_unicode_ci DEFAULT 'disabled',
        `push_sound` enum('disabled','enabled') COLLATE utf8_unicode_ci DEFAULT 'disabled',
        `status` enum('active','uninstalled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
        `created_at` datetime NOT NULL,
        `updated_at` timestamp NOT NULL,
        PRIMARY KEY (`device_id`),
        UNIQUE KEY `UNIQUE_KEY_APP_NAME_APP_VERSION_DEVICE_UID` (`app_name`,`app_version`,`device_uid`),
        KEY `KEY_DEVICE_TOKEN` (`device_token`),
        KEY `KEY_STATUS` (`status`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `push_delivered_message` (
        `deliver_id` int(11) NOT NULL AUTO_INCREMENT,
        `device_id` int(11) NOT NULL,
        `device_uid` text COLLATE utf8_unicode_ci NOT NULL,
        `device_type` tinyint(1) NOT NULL,
        `message_id` int(11) NOT NULL,
        `status` tinyint(1) NOT NULL DEFAULT '0',
        `is_read` tinyint(1) NOT NULL DEFAULT '0',
        `is_displayed` int(11) NOT NULL DEFAULT '0',
        `delivered_at` datetime NOT NULL,
        PRIMARY KEY (`deliver_id`),
        KEY `KEY_DEVICE_ID` (`device_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `push_gcm_devices` (
        `device_id` int(11) NOT NULL AUTO_INCREMENT,
        `app_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `registration_id` text COLLATE utf8_unicode_ci,
        `development` enum('production','sandbox') CHARACTER SET latin1 NOT NULL DEFAULT 'production',
        `status` enum('active','uninstalled') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
        `created_at` datetime NOT NULL,
        `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`device_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `push_messages` (
        `message_id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
        `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `latitude` decimal(11,8) DEFAULT NULL,
        `longitude` decimal(11,8) DEFAULT NULL,
        `radius` decimal(7,2) DEFAULT NULL,
        `send_at` datetime DEFAULT NULL,
        `send_until` datetime DEFAULT NULL,
        `delivered_at` datetime DEFAULT NULL,
        `status` enum('queued','delivered','sending','failed') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'queued',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`message_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `push_certificat` (
        `certificat_id` int(11) NOT NULL AUTO_INCREMENT,
        `type` varchar(30) NOT NULL,
        `path` varchar(255) NULL DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`certificat_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$library = new Media_Model_Library();
$library->setName('Push')->save();

$icon_paths = array(
    '/push_notifications/push1.png',
    '/push_notifications/push2.png',
    '/push_notifications/push3.png',
    '/push_notifications/push4.png',
    '/push_notifications/push5.png',
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
    'code' => 'push_notification',
    'name' => 'Push Notifications',
    'model' => '',
    'desktop_uri' => 'push/application/',
    'mobile_uri' => 'push/mobile/',
    'only_once' => 1,
    'is_ajax' => 1,
    'position' => 130
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
