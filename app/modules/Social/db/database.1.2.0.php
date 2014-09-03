<?php

$option = new Application_Model_Option();
$option->find("facebook", "code");
$option->setMobileUri("social/mobile_facebook_list/")->save();
