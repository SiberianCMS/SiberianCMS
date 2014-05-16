<?php

$this->query("

    CREATE TABLE `template_design` (
        `design_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `layout_id` int(11) unsigned NOT NULL,
        `code` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
        `name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
        `overview` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `background_image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `background_image_retina` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        PRIMARY KEY (`design_id`),
        KEY `KEY_LAYOUT_ID` (`layout_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

    CREATE TABLE `template_block` (
        `block_id` int(11) NOT NULL AUTO_INCREMENT,
        `code` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
        `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
        `color` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
        `background_color` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
        `image_color` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
        `position` smallint(5) NOT NULL DEFAULT '0',
        `created_at` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        PRIMARY KEY (`block_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
");

$this->query("
    CREATE TABLE `template_design_block` (
        `design_block_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `design_id` int(11) unsigned NOT NULL,
        `block_id` int(11) NOT NULL,
        `background_color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
        `color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
        `image_color` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`design_block_id`),
        KEY `KEY_DESIGN_ID` (`design_id`),
        KEY `KEY_BLOCK_ID` (`block_id`)
    ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

");


$this->query("
    ALTER TABLE `template_design`
        ADD FOREIGN KEY `FK_LAYOUT_ID` (`layout_id`) REFERENCES `application_layout_homepage` (`layout_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");

$this->query("
    ALTER TABLE `template_design_block`
        ADD FOREIGN KEY `FK_DESIGN_ID` (`design_id`) REFERENCES `template_design` (`design_id`) ON DELETE CASCADE ON UPDATE CASCADE,
        ADD FOREIGN KEY `FK_BLOCK_ID` (`block_id`) REFERENCES `template_block` (`block_id`) ON DELETE CASCADE ON UPDATE CASCADE;
");


$datas = array(
    array('code' => 'header', 'name' => 'Header', 'use_color' => 1, 'color' => '#00377a', 'use_background_color' => 1, 'background_color' => '#739c03', 'position' => 10),
    array('code' => 'subheader', 'name' => 'Subheader', 'use_color' => 1, 'color' => '#00377a', 'use_background_color' => 1, 'background_color' => '#739c03', 'position' => 20),
    array('code' => 'connect_button', 'name' => 'Connect Button', 'use_color' => 1, 'color' => '#233799', 'use_background_color' => 1, 'background_color' => '#f2f2f2', 'position' => 30),
    array('code' => 'background', 'name' => 'Background', 'use_color' => 1, 'color' => '#ffffff', 'use_background_color' => 1, 'background_color' => '#0c6ec4', 'position' => 40),
    array('code' => 'discount', 'name' => 'Discount Zone', 'use_color' => 1, 'color' => '#fcfcfc', 'use_background_color' => 1, 'background_color' => '#739c03', 'position' => 50),
    array('code' => 'button', 'name' => 'Button', 'use_color' => 1, 'color' => '#fcfcfc', 'use_background_color' => 1, 'background_color' => '#00377a', 'position' => 60),
    array('code' => 'news', 'name' => 'News', 'use_color' => 1, 'color' => '#fcfcfc', 'use_background_color' => 1, 'background_color' => '#00377a', 'position' => 70),
    array('code' => 'comments', 'name' => 'Comments', 'use_color' => 1, 'color' => '#ffffff', 'use_background_color' => 1, 'background_color' => '#4d5d8a', 'position' => 80),
    array('code' => 'tabbar', 'name' => 'Tabbar', 'use_color' => 1, 'color' => '#ffffff', 'use_background_color' => 1, 'background_color' => '#739c03', 'image_color' => '#ffffff', 'position' => 90)
);

foreach($datas as $data) {
    $block = new Template_Model_Block();
    $block->setData($data)->save();
}

$datas = array(
    array("layout_id" => 1, "code" => "zenstitut", "name" => "Zenstitut", "overview" => "/zenstitut/overview.jpg", "background_image" => "/zenstitut/background.jpg", "background_image_retina" => "/zenstitut/background-568h@2x.jpg"),
    array("layout_id" => 2, "code" => "hairdresser", "name" => "Hairdresser", "overview" => "/hairdresser/overview.jpg", "background_image" => "/hairdresser/background.jpg", "background_image_retina" => "/hairdresser/background-568h@2x.jpg"),
    array("layout_id" => 3, "code" => "fall_wedding", "name" => "Wedding", "overview" => "/wedding/overview.jpg", "background_image" => "/wedding/background.jpg", "background_image_retina" => "/wedding/background-568h@2x.jpg"),
    array("layout_id" => 4, "code" => "purple_croco", "name" => "Violet", "overview" => "/purple_croco/overview.jpg", "background_image" => "/purple_croco/background.jpg", "background_image_retina" => "/purple_croco/background-568h@2x.jpg"),
    array("layout_id" => 5, "code" => "grand_palace", "name" => "Grand Palace", "overview" => "/grand_palace/overview.jpg", "background_image" => "/grand_palace/background.jpg", "background_image_retina" => "/grand_palace/background-568h@2x.jpg"),
    array("layout_id" => 6, "code" => "white_shadow", "name" => "White Shadow", "overview" => "/white_shadow/overview.jpg", "background_image" => "/white_shadow/background.jpg", "background_image_retina" => "/white_shadow/background-568h@2x.jpg"),
    array("layout_id" => 7, "code" => "side_brown", "name" => "Marron", "overview" => "/side_brown/overview.jpg", "background_image" => "/side_brown/background.jpg", "background_image_retina" => "/side_brown/background-568h@2x.jpg"),
);

foreach($datas as $data) {
    $design = new Template_Model_Design();
    $design->setData($data)->save();
}

$this->query("
    INSERT INTO `template_design_block` VALUES(1, 1, 1, '#CB0052', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(2, 1, 2, '#FFFFFF', '#CB0052', NULL);
    INSERT INTO `template_design_block` VALUES(3, 1, 3, '#CB0052', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(4, 1, 4, '#CB0052', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(5, 1, 5, '#CB0052', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(6, 1, 6, '#CB0052', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(7, 1, 7, '#2B8901', '#000000', NULL);
    INSERT INTO `template_design_block` VALUES(8, 1, 8, '#FFFFFF', '#CB0052', NULL);
    INSERT INTO `template_design_block` VALUES(9, 1, 9, '#2B8901', '#000000', '#000000');
");

$this->query("
    INSERT INTO `template_design_block` VALUES(10, 2, 1, '#56718E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(11, 2, 2, '#FFFFFF', '#56718E', NULL);
    INSERT INTO `template_design_block` VALUES(12, 2, 3, '#56718E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(13, 2, 4, '#56718E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(14, 2, 5, '#56718E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(15, 2, 6, '#56718E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(16, 2, 7, '#56718E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(17, 2, 8, '#FFFFFF', '#56718e', NULL);
    INSERT INTO `template_design_block` VALUES(18, 2, 9, '#56718E', '#FFFFFF', '#FFFFFF');
");

$this->query("
    INSERT INTO `template_design_block` VALUES(19, 3, 1, '#BA5521', '#5B371F', NULL);
    INSERT INTO `template_design_block` VALUES(20, 3, 2, '#5B371F', '#BA5521', NULL);
    INSERT INTO `template_design_block` VALUES(21, 3, 3, '#BA5521', '#5B371F', NULL);
    INSERT INTO `template_design_block` VALUES(22, 3, 4, '#BA5521', '#5B371F', NULL);
    INSERT INTO `template_design_block` VALUES(23, 3, 5, '#BA5521', '#5B371F', NULL);
    INSERT INTO `template_design_block` VALUES(24, 3, 6, '#BA5521', '#5B371F', NULL);
    INSERT INTO `template_design_block` VALUES(25, 3, 7, '#BA5521', '#5B371F', NULL);
    INSERT INTO `template_design_block` VALUES(26, 3, 8, '#5B371F', '#BA5521', NULL);
    INSERT INTO `template_design_block` VALUES(27, 3, 9, '#BA5521', '#5B371F', '#5B371F');
");

$this->query("
    INSERT INTO `template_design_block` VALUES(28, 4, 1, '#2E2E2E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(29, 4, 2, '#734957', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(30, 4, 3, '#734957', '#000000', NULL);
    INSERT INTO `template_design_block` VALUES(31, 4, 4, '#2E2E2E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(32, 4, 5, '#2E2E2E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(33, 4, 6, '#2E2E2E', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(34, 4, 7, '#FFFFFF', '#2E2E2E', NULL);
    INSERT INTO `template_design_block` VALUES(35, 4, 8, '#734957', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(36, 4, 9, '#FFFFFF', '#2E2E2E', '#2E2E2E');
");

$this->query("
    INSERT INTO `template_design_block` VALUES(37, 5, 1, '#000000', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(38, 5, 2, '#FFFFFF', '#000000', NULL);
    INSERT INTO `template_design_block` VALUES(39, 5, 3, '#000000', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(40, 5, 4, '#FFFFFF', '#000000', NULL);
    INSERT INTO `template_design_block` VALUES(41, 5, 5, '#FFFFFF', '#000000', NULL);
    INSERT INTO `template_design_block` VALUES(42, 5, 6, '#FFFFFF', '#000000', NULL);
    INSERT INTO `template_design_block` VALUES(43, 5, 7, '#FFFFFF', '#000000', NULL);
    INSERT INTO `template_design_block` VALUES(44, 5, 8, '#000000', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(45, 5, 9, '#FFFFFF', '#FFFFFF', '#FFFFFF');
");

$this->query("
    INSERT INTO `template_design_block` VALUES(46, 6, 1, '#1F6FAA', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(47, 6, 2, '#FFFFFF', '#1f6faa', NULL);
    INSERT INTO `template_design_block` VALUES(48, 6, 3, '#1F6FAA', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(49, 6, 4, '#FFFFFF', '#1F6FAA', NULL);
    INSERT INTO `template_design_block` VALUES(50, 6, 5, '#FFFFFF', '#1F6FAA', NULL);
    INSERT INTO `template_design_block` VALUES(51, 6, 6, '#FFFFFF', '#1F6FAA', NULL);
    INSERT INTO `template_design_block` VALUES(52, 6, 7, '#FFFFFF', '#1F6FAA', NULL);
    INSERT INTO `template_design_block` VALUES(53, 6, 8, '#FFFFFF', '#1F6FAA', NULL);
    INSERT INTO `template_design_block` VALUES(54, 6, 9, '#FFFFFF', '#FFFFFF', '#FFFFFF');
");

$this->query("
    INSERT INTO `template_design_block` VALUES(55, 7, 1, '#FFFFFF', '#43352A', NULL);
    INSERT INTO `template_design_block` VALUES(56, 7, 2, '#43352A', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(57, 7, 3, '#FFFFFF', '#43352A', NULL);
    INSERT INTO `template_design_block` VALUES(58, 7, 4, '#43352A', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(59, 7, 5, '#43352A', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(60, 7, 6, '#43352A', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(61, 7, 7, '#FFFFFF', '#43352A', NULL);
    INSERT INTO `template_design_block` VALUES(62, 7, 8, '#43352A', '#FFFFFF', NULL);
    INSERT INTO `template_design_block` VALUES(63, 7, 9, '#FFFFFF', '#43352A', '#43352A');
");
