<?php

$option = new Application_Model_Option();
$option->find("contact", "code");
$option->setMobileUri("contact/mobile_view/")->save();
