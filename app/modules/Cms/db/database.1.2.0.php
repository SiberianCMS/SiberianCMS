<?php

$option = new Application_Model_Option();
$option->find("custom_page", "code");
$option->setMobileUri("cms/mobile_page_view/")->save();
