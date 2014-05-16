<?php

$this->query('
    CREATE TABLE `social_facebook` (
        `facebook_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `fb_user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`facebook_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
');


$library = new Media_Model_Library();
$library->setName('Facebook')->save();

$icon_paths = array(
    '/social_facebook/facebook1.png'
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
    'code' => 'facebook',
    'name' => 'Facebook',
    'model' => 'Social_Model_Facebook',
    'desktop_uri' => 'social/application_facebook/',
    'mobile_uri' => 'social/mobile_facebook/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 210
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
