<?php

$this->query('
    CREATE TABLE `event` (
        `event_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `value_id` int(11) unsigned NOT NULL,
        `name` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
        `event_type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
        `url` text COLLATE utf8_unicode_ci,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`event_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `event_custom` (
        `event_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `agenda_id` int(11) unsigned NOT NULL,
        `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
        `start_at` datetime DEFAULT NULL,
        `end_at` datetime DEFAULT NULL,
        `description` text COLLATE utf8_unicode_ci,
        `location` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `rsvp` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `picture` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`event_id`),
        KEY `KEY_AGENDA_ID` (`agenda_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

');


$this->query("
    ALTER TABLE `event_custom`
        ADD FOREIGN KEY `FK_AGENDA_ID` (`agenda_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$library = new Media_Model_Library();
$library->setName('Calendar')->save();

$icon_paths = array(
    '/calendar/calendar1.png',
    '/calendar/calendar2.png',
    '/calendar/calendar3.png'
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
    'code' => 'calendar',
    'name' => 'Calendar',
    'model' => 'Event_Model_Event',
    'desktop_uri' => 'event/application/',
    'mobile_uri' => 'event/mobile/',
    'only_once' => 0,
    'is_ajax' => 1,
    'position' => 200
);
$option = new Application_Model_Option();
$option->setData($datas)->save();
