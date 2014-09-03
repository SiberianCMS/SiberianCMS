<?php

$option = new Application_Model_Option();
$option->find("rss_feed", "code");
$option->setMobileUri("rss/mobile_feed_list/")->save();
