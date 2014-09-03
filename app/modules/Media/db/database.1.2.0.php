<?php

$option = new Application_Model_Option();
$option->find("image_gallery", "code");
$option->setMobileUri("media/mobile_gallery_image_list/")->save();

$option = new Application_Model_Option();
$option->find("video_gallery", "code");
$option->setMobileUri("media/mobile_gallery_video_list/")->save();
