<?php

$this->query("
    CREATE TABLE `rss_feed` (
        `feed_id` int(11) NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `title` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
        `link` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `position` tinyint(1) unsigned NOT NULL DEFAULT '0',
        `picture` tinyint(1) DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`feed_id`),
        KEY `KEY_VALUE_ID` (`value_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `rss_feed`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('RSS Feed')->save();

$icon_paths = array(
    '/rss_feed/rss1.png',
    '/rss_feed/rss2.png',
    '/rss_feed/rss3.png',
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
    'code' => 'rss_feed',
    'name' => 'RSS Feed',
    'model' => 'Rss_Model_Feed',
    'desktop_uri' => 'rss/application_feed/',
    'mobile_uri' => 'rss/mobile_feed/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 80
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
