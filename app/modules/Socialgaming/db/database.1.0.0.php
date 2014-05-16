<?php

$this->query("
    CREATE TABLE `socialgaming_game` (
        `game_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `period_id` tinyint(1) NOT NULL DEFAULT '0',
        `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `gift` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
        `end_at` date DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`game_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$library = new Media_Model_Library();
$library->setName('RSS Feed')->save();

$icon_paths = array(
    '/contest/contest1.png',
    '/contest/contest2.png',
    '/contest/contest3.png',
    '/contest/contest4.png',
    '/contest/contest5.png'
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
    'code' => 'social_gaming',
    'name' => 'Contest',
    'model' => '',
    'desktop_uri' => 'socialgaming/application/',
    'mobile_uri' => 'socialgaming/mobile/',
    'only_once' => 1,
    'is_ajax' => 1,
    'position' => 60
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
