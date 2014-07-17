<?php

$this->query("
    ALTER TABLE `application_layout_homepage`
        ADD `number_of_displayed_icons` tinyint(2) NULL DEFAULT NULL AFTER `use_more_button`
    ;
");

$layout = new Application_Model_Layout_Homepage();
$layout->find(1);
$layout->setNumberOfDisplayedIcons(5)->save();

$layout = new Application_Model_Layout_Homepage();
$layout->find(2);
$layout->setNumberOfDisplayedIcons(10)->save();
