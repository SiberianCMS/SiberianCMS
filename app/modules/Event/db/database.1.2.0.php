<?php

$option = new Application_Model_Option();
$option->find("calendar", "code");
$option->setMobileUri("event/mobile_list/")->save();
