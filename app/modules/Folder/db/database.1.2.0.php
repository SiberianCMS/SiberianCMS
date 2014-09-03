<?php

$option = new Application_Model_Option();
$option->find("folder", "code");
$option->setMobileUri("folder/mobile_list/")->save();
