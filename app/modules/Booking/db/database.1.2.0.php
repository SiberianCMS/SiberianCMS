<?php

$option = new Application_Model_Option();
$option->find("booking", "code");
$option->setMobileUri("booking/mobile_view/")->save();
