<?php

$this->query("
    CREATE TABLE `cms_application_block` (
        `block_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
        `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `title` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
        `template` text COLLATE utf8_unicode_ci NOT NULL,
        `mobile_template` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`block_id`)
  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `cms_application_page` (
        `page_id` int(11) NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `content` text COLLATE utf8_unicode_ci,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`page_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `cms_application_page_block` (
        `value_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `block_id` int(11) unsigned NOT NULL,
        `page_id` int(11) unsigned NOT NULL,
        `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `cms_application_page_block_address` (
        `address_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `label` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
        `address` text COLLATE utf8_unicode_ci NOT NULL,
        `show_address` tinyint(1) NOT NULL DEFAULT '0',
        `show_geolocation_button` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`address_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `cms_application_page_block_image` (
        `image_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `library_id` int(11) DEFAULT NULL,
        `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `layout` text COLLATE utf8_unicode_ci,
        PRIMARY KEY (`image_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `cms_application_page_block_image_library` (
        `image_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `library_id` int(11) unsigned NOT NULL,
        `image_url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `image_fullsize_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`image_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `cms_application_page_block_text` (
        `text_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `content` text COLLATE utf8_unicode_ci NOT NULL,
        `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `size` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
        `alignment` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
        `layout_id` tinyint(1) unsigned NOT NULL,
        PRIMARY KEY (`text_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `cms_application_page_block_video` (
        `video_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `type_id` enum('link','youtube','podcast') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'link',
        `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`video_id`),
        KEY `FK_CMS_APPLICATION_PAGE_BLOCK_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `cms_application_page_block_video_link` (
        `video_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`video_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `cms_application_page_block_video_podcast` (
        `video_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `search` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`video_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `cms_application_page_block_video_youtube` (
        `video_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `search` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `youtube` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`video_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `cms_application_page`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `cms_application_page_block_address`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `cms_application_page_block` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `cms_application_page_block_image`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `cms_application_page_block` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `cms_application_page_block_text`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `cms_application_page_block` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `cms_application_page_block_video`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `cms_application_page_block` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `cms_application_page_block_video_link`
        ADD FOREIGN KEY `FK_VIDEO_ID` (`video_id`) REFERENCES `cms_application_page_block_video` (`video_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `cms_application_page_block_video_podcast`
        ADD FOREIGN KEY `FK_VIDEO_ID` (`video_id`) REFERENCES `cms_application_page_block_video` (`video_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `cms_application_page_block_video_youtube`
        ADD FOREIGN KEY `FK_VIDEO_ID` (`video_id`) REFERENCES `cms_application_page_block_video` (`video_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('Custom Page')->save();

$icon_paths = array(
    '/custom_page/custom1.png',
    '/loyalty/loyalty6.png',
    '/newswall/newswall1.png',
    '/newswall/newswall2.png',
    '/newswall/newswall3.png',
    '/newswall/newswall4.png',
    '/push_notifications/push1.png',
    '/push_notifications/push2.png',
    '/catalog/catalog6.png',
    '/catalog/catalog8.png',
    '/catalog/catalog9.png',
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
    'code' => 'custom_page',
    'name' => 'Custom Page',
    'model' => 'Cms_Model_Application_Page',
    'desktop_uri' => 'cms/application_page/',
    'mobile_uri' => 'cms/mobile_page/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 70
);
$option = new Application_Model_Option();
$option->setData($datas)->save();


$datas = array(
    array('type' => 'text', 'position' => 1, 'icon' => 'icon-file-alt', 'title' => 'Text', 'template' => 'cms/application/page/edit/block/text.phtml', 'mobile_template' => 'cms/page/%s/view/block/text.phtml'),
    array('type' => 'image', 'position' => 2, 'icon' => 'icon-picture', 'title' => 'Image', 'template' => 'cms/application/page/edit/block/image.phtml', 'mobile_template' => 'cms/page/%s/view/block/image.phtml'),
    array('type' => 'video', 'position' => 3, 'icon' => 'icon-facetime-video', 'title' => 'Video', 'template' => 'cms/application/page/edit/block/video.phtml', 'mobile_template' => 'cms/page/%s/view/block/video.phtml'),
    array('type' => 'address', 'position' => 4, 'icon' => 'icon-location-arrow', 'title' => 'Address', 'template' => 'cms/application/page/edit/block/address.phtml', 'mobile_template' => 'cms/page/%s/view/block/address.phtml'),
);

foreach($datas as $data) {
    $block = new Cms_Model_Application_Block();
    $block->setData($data)->save();
}
