<?php

$this->query("ALTER TABLE `admin` ADD `parent_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `admin_id`;");
$this->query("ALTER TABLE `admin` ADD KEY `KEY_PARENT_ID` (`parent_id`);");