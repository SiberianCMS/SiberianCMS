<?php

$option = new Application_Model_Option();
$option->find("loyalty", "code");
$option->setMobileUri("loyaltycard/mobile_view/")->save();
