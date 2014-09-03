<?php

$option = new Application_Model_Option();
$option->find("wordpress", "code");
$option->setMobileUri("wordpress/mobile_list/")->save();
