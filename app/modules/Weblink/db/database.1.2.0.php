<?php

$option = new Application_Model_Option();
$option->find("weblink_mono", "code");
$option->setMobileUri("weblink/mobile_mono/")->save();

$option = new Application_Model_Option();
$option->find("weblink_multi", "code");
$option->setMobileUri("weblink/mobile_multi/")->save();
