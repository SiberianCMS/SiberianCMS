<?php

$option = new Application_Model_Option();
$option->find("discount", "code");
$option->setMobileUri("promotion/mobile_list/")->save();
