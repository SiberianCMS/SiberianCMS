<?php

$this->query("
    CREATE TABLE `folder` (
        `folder_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `root_category_id` int(11) unsigned NOT NULL,
        PRIMARY KEY (`folder_id`),
        KEY `KEY_VALUE_ID` (`value_id`),
        KEY `KEY_CAT_ID` (`root_category_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `folder_category` (
        `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `parent_id` int(11) unsigned DEFAULT NULL,
        `type_id` enum('folder') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'folder',
        `picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `subtitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `pos` int(11) DEFAULT NULL,
        PRIMARY KEY (`category_id`),
        KEY `KEY_PARENT_ID` (`parent_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");


$this->query("
    ALTER TABLE `folder`
        ADD FOREIGN KEY `FK_VALUE_ID` (`value_id`) REFERENCES `application_option_value` (`value_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        ADD FOREIGN KEY `FK_ROOT_CATEGORY_ID` (`root_category_id`) REFERENCES `folder_category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `folder_category`
        ADD FOREIGN KEY `FK_PARENT_ID` (`parent_id`) REFERENCES `folder_category` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$library = new Media_Model_Library();
$library->setName('Folders')->save();

$icon_paths = array(
    '/folders/folder1.png',
    '/folders/folder2.png',
    '/folders/folder3.png',
    '/folders/folder4.png',
    '/folders/folder5.png'
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
    'code' => 'folder',
    'name' => 'Folder',
    'model' => 'Folder_Model_Folder',
    'desktop_uri' => 'folder/application/',
    'mobile_uri' => 'folder/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 180
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
