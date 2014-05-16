<?php

$this->query("
    CREATE TABLE `comment` (
        `comment_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `is_visible` int(11) DEFAULT '1',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`comment_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `comment_answer` (
        `answer_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `comment_id` int(11) UNSIGNED NOT NULL,
        `customer_id` int(11) NOT NULL,
        `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `is_visible` tinyint(1) NOT NULL DEFAULT '1',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`answer_id`),
        KEY `KEY_COMMENT_ID` (`comment_id`),
        KEY `KEY_CUSTOMER_ID` (`customer_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `comment_like` (
        `like_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `comment_id` int(11) UNSIGNED NOT NULL,
        `customer_id` int(11) UNSIGNED DEFAULT NULL,
        `customer_ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`like_id`),
        KEY `KEY_COMMENT_ID` (`comment_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `comment`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `comment_answer`
        ADD FOREIGN KEY `FK_COMMENT_ID` (`comment_id`) REFERENCES `comment` (`comment_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `comment_like`
        ADD FOREIGN KEY `FK_COMMENT_ID` (`comment_id`) REFERENCES `comment` (`comment_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('Newswall')->save();

$icon_paths = array(
    '/newswall/newswall1.png',
    '/newswall/newswall2.png',
    '/newswall/newswall3.png',
    '/newswall/newswall4.png'
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
    'code' => 'newswall',
    'name' => 'Newswall',
    'model' => 'Comment_Model_Comment',
    'desktop_uri' => 'comment/application/',
    'mobile_uri' => 'comment/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 10
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
