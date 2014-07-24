<?php

abstract class Media_Model_Gallery_Image_Abstract extends Core_Model_Default {

    const MAX_RESULTS = 25;

    protected $_images;

    abstract public function getImages($offset);

}
