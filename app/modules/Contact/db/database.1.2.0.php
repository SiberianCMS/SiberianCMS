<?php

$this->query("ALTER TABLE `contact`
    ADD `latitude` decimal(11,8) DEFAULT NULL AFTER `country`,
    ADD `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`;
");

$option = new Application_Model_Option();
$option->find("contact", "code");
$option->setMobileUri("contact/mobile_view/")->save();
