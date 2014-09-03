<?php

$option = new Application_Model_Option();
$option->find("push_notification", "code");
$option->setMobileUri("push/mobile_list/")->save();
