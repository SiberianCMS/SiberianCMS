<?php

abstract class Media_Model_Gallery_Video_Abstract extends Core_Model_Default {

    const MAX_RESULTS = 5;
    protected $_videos;

    abstract public function getVideos($offset);

}
