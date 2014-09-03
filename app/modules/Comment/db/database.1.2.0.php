<?php

$option = new Application_Model_Option();
$option->find("newswall", "code");
$option->setMobileUri("comment/mobile_list/")->save();
