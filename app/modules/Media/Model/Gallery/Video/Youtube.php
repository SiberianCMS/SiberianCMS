<?php

class Media_Model_Gallery_Video_Youtube extends Media_Model_Gallery_Video_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Media_Model_Db_Table_Gallery_Video_Youtube';
        return $this;
    }

    protected $_flux = array(
        'channel' => 'http://gdata.youtube.com/feeds/api/users/%s/uploads?start-index=%d1&max-results=%d2',
        'search' => 'http://gdata.youtube.com/feeds/api/videos/?q=%s&start-index=%d1&max-results=%d2',
    );

    public function getAllTypes() {
        return array_keys($this->_flux);
    }

    public function getVideos($offset) {

        if(!$this->_videos) {

            $this->_videos = array();

            try {
                $this->_setYoutubeUrl($offset);
                $feed = Zend_Feed_Reader::import($this->getLink());
            }
            catch(Exception $e) {
                $feed = array();
            }

            foreach ($feed as $entry) {
                $params = Zend_Uri::factory($entry->getLink())->getQueryAsArray();
                $image = null;
                $link = null;
                if(!empty($params['v'])) {
                    $image = "http://img.youtube.com/vi/{$params['v']}/0.jpg";
                    $link = "http://www.youtube.com/embed/{$params['v']}?autoplay=1";
                }
                else {
                    $link = $entry->getLink();
                }

                $video = new Core_Model_Default(array(
                    'video_id'     => $params['v'],
                    'title'        => $entry->getTitle(),
                    'description'  => $entry->getContent(),
                    'link'         => $link,
                    'image'        => $image
                ));

                $this->_videos[] = $video;
            }

        }

        return $this->_videos;
    }

    public function getFields() {
        return $this->getTable()->getFields();
    }

    protected function _setYoutubeUrl($offset) {
        $url = str_replace('%s', $this->getParam(), $this->_flux[$this->getType()]);
        $url = str_replace('%d1', $offset, $url);
        $url = str_replace('%d2', self::MAX_RESULTS, $url);
        $this->setLink($url);
        return $this;
    }

}

