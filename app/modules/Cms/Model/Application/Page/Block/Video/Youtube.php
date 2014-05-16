<?php

class Cms_Model_Application_Page_Block_Video_Youtube  extends Core_Model_Default {

    private $_videos;
    private $_link;
    protected $_flux = 'http://gdata.youtube.com/feeds/api/videos/?q=%s&start-index=%d1&max-results=%d2';

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Cms_Model_Db_Table_Application_Page_Block_Video_Youtube';
        return $this;
    }

    public function isValid() {
        if($this->getYoutube()) return true;
        return false;
    }

    public function getImageUrl() {
        return "http://img.youtube.com/vi/{$this->getYoutube()}/0.jpg";
    }

    /**
     * Récupère les vidéos youtube
     *
     * @param string $search
     * @return array
     */
    public function getList($search) {

        if(!$this->_videos) {

            $this->_videos = array();

            try {
                $video_id = $search;
                if(Zend_Uri::check($search)) {
                    $params = Zend_Uri::factory($search)->getQueryAsArray();
                    if(!empty($params['v'])) {
                        $video_id = $params['v'];
                    }
                }

                $api_key = Api_Model_Key::findKeysFor('youtube')->getApiKey();
                $url = "https://www.googleapis.com/youtube/v3/videos?id={$video_id}&key={$api_key}&part=snippet,contentDetails,status";
                $datas = @file_get_contents($url);

                if($datas && !empty($datas['pageInfo']['totalResults'])) {
                    $datas = Zend_Json::decode($datas);
                    $feed = array();
                    foreach($datas['items'] as $item) {
                        $feed[] = new Core_Model_Default(array(
                            'title' => !empty($item['snippet']['title']) ? $item['snippet']['title'] : null,
                            'content' => !empty($item['snippet']['description']) ? $item['snippet']['description'] : null,
                            'link' => "http://www.youtube.com/watch?v=$video_id"
                        ));
                    }
                }
                else {
                    $this->_setYoutubeUrl($search);
                    $feed = Zend_Feed_Reader::import($this->getLink());
                }

            } catch(Exception $e) {
                $feed = array();
            }

            foreach ($feed as $entry) {
                $params = Zend_Uri::factory($entry->getLink())->getQueryAsArray();
                if(empty($params['v'])) continue;

                $video = new Core_Model_Default(array(
                    'id'           => $params['v'],
                    'title'        => $entry->getTitle(),
                    'description'  => $entry->getContent(),
                    'link'         => "http://www.youtube.com/embed/{$params['v']}",
                    'image'        => "http://img.youtube.com/vi/{$params['v']}/0.jpg"
                ));

                $this->_videos[] = $video;
            }

        }

        return $this->_videos;
    }

    public function getVideo($id) {

    }

    /**
     * Construit le lien
     *
     * @param string $search
     * @return \Cms_Model_Application_Page_Block_Youtube
     */
    protected function _setYoutubeUrl($search) {
        $search = str_replace(' ', '+', $search);
        $url = str_replace('%s', $search, $this->_flux);
        $url = str_replace('%d1', '1', $url);
        $url = str_replace('%d2', '24', $url);
        $this->setLink($url);
        return $this;
    }

    protected function setLink($url) {
        $this->_link = $url;
    }

    protected function getLink() {
        return $this->_link;
    }

}

