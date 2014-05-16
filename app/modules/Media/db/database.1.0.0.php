<?php

$this->query("
    CREATE TABLE `media_gallery_image` (
        `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `type_id` enum('picasa','custom','instagram') COLLATE utf8_unicode_ci NOT NULL,
        `name` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`gallery_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `media_gallery_image_custom` (
        `image_id` int(11) NOT NULL AUTO_INCREMENT,
        `gallery_id` int(11) unsigned NOT NULL,
        `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `title` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
        `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`image_id`),
        KEY `KEY_GALLERY_ID` (`gallery_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `media_gallery_image_instagram` (
        `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `param_instagram` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `order_by` enum('updated') COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`gallery_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `media_gallery_image_picasa` (
        `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `type` enum('album','search') COLLATE utf8_unicode_ci DEFAULT NULL,
        `param` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `album_id` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
        `order_by` enum('updated') COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`gallery_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `media_gallery_video` (
        `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `type_id` enum('youtube','itunes','vimeo') COLLATE utf8_unicode_ci NOT NULL,
        `name` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`gallery_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `media_gallery_video_itunes` (
        `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `param` text COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`gallery_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `media_gallery_video_vimeo` (
        `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `type` enum('user','group','channel','album') COLLATE utf8_unicode_ci NOT NULL,
        `param` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`gallery_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `media_gallery_video_youtube` (
        `gallery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `type` enum('user','channel','search','favorite','playlist') COLLATE utf8_unicode_ci NOT NULL,
        `param` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
        `order_by` enum('updated') COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`gallery_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `media_library` (
        `library_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`library_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `media_library_image` (
        `image_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `library_id` int(11) unsigned NOT NULL,
        `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `secondary_link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `thumbnail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `option_id` int(11) DEFAULT NULL,
        `can_be_colorized` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`image_id`),
        KEY `KEY_LIBRARY_ID` (`library_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");



$this->query("
    ALTER TABLE `media_gallery_image_custom`
        ADD FOREIGN KEY `FK_GALLERY_ID` (`gallery_id`) REFERENCES `media_gallery_image` (`gallery_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `media_gallery_image_picasa`
        ADD FOREIGN KEY `FK_GALLERY_ID` (`gallery_id`) REFERENCES `media_gallery_image` (`gallery_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `media_gallery_video_itunes`
        ADD FOREIGN KEY `FK_GALLERY_ID` (`gallery_id`) REFERENCES `media_gallery_video` (`gallery_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `media_gallery_video_vimeo`
        ADD FOREIGN KEY `FK_GALLERY_ID` (`gallery_id`) REFERENCES `media_gallery_video` (`gallery_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `media_gallery_video_youtube`
        ADD FOREIGN KEY `FK_GALLERY_ID` (`gallery_id`) REFERENCES `media_gallery_video` (`gallery_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `media_library_image`
        ADD FOREIGN KEY `FK_LIBRARY_ID` (`library_id`) REFERENCES `media_library` (`library_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$this->query("
    ALTER TABLE `application_option`
        ADD FOREIGN KEY `FK_LIBRARY_ID` (`library_id`) REFERENCES `media_library` (`library_id`) ON DELETE NO ACTION ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `media_gallery_image`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `media_gallery_video`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


// Images Gallery
$library = new Media_Model_Library();
$library->setName('Images')->save();

$icon_paths = array(
    '/images/image1.png',
    '/images/image2.png',
    '/images/image3.png',
    '/images/image4.png',
    '/images/image5.png',
    '/images/image6.png',
    '/images/image7.png'
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
    'code' => 'image_gallery',
    'name' => 'Images',
    'model' => 'Media_Model_Gallery_Image',
    'desktop_uri' => 'media/application_gallery_image/',
    'mobile_uri' => 'media/mobile_gallery_image/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 90
);
$option = new Application_Model_Option();
$option->setData($datas)->save();


// Videos Gallery
$library = new Media_Model_Library();
$library->setName('Videos')->save();

$icon_paths = array(
    '/videos/video1.png',
    '/videos/video2.png',
    '/videos/video3.png',
    '/videos/video4.png',
    '/videos/video5.png',
    '/videos/video6.png'
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
    'code' => 'video_gallery',
    'name' => 'Videos',
    'model' => 'Media_Model_Gallery_Video',
    'desktop_uri' => 'media/application_gallery_video/',
    'mobile_uri' => 'media/mobile_gallery_video/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 100
);
$option = new Application_Model_Option();
$option->setData($datas)->save();

