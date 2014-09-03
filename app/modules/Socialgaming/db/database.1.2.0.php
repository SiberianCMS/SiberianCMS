<?php

$option = new Application_Model_Option();
$option->find("social_gaming", "code");
$option->setMobileUri("socialgaming/mobile_view/")->save();
