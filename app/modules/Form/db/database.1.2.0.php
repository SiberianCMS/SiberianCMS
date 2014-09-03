<?php

$option = new Application_Model_Option();
$option->find("form", "code");
$option->setMobileUri("form/mobile_view/")->save();
