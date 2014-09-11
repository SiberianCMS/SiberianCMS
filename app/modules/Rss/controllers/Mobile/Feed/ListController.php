<?php

class Rss_Mobile_Feed_ListController extends Application_Controller_Mobile_Default {

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $rss_feed = new Rss_Model_Feed();
            $rss_feeds = $rss_feed->findAll(array('value_id' => $value_id), 'position ASC');
            $data = array();
            $color = $this->getApplication()->getBlock('background')->getColor();

            foreach($rss_feeds as $rss_feed) {

                $news = $rss_feed->getNews();
                foreach($news->getEntries() as $entry) {

                    $author = "";
                    $authors = array();
                    foreach($entry->getAuthors() as $author) {
                        $authors[] = $author["name"];
                    }
                    if(!empty($authors)) {
                        $author = implode(", ", $authors);
                    }

                    $data['collection'][] = array(
                        "url" => $this->getPath("rss/mobile_feed_view", array("value_id" => $value_id, "feed_id" => base64_encode($entry->getEntryId()))),
                        "title" => $author ? $author : $entry->getTitle(),
                        "subtitle" => $author ? $entry->getTitle() : $entry->getShortDescription(),
                        "picture" => $entry->getPicture()
//                        "meta" => array(
//                            "area3" => array(
//                                "picto" => $this->_getColorizedImage($this->_getImage("pictos/pencil.png"), $color),
//                                "text" => $this->_('%s ago', $this->_getUpdatedAt($entry))
//                            )
//                        )
                    );

                }
            }

            $data['page_title'] = $this->getCurrentOptionValue()->getTabbarName();

            $this->_sendHtml($data);
        }

    }

    protected function _getUpdatedAt($entry) {

        $date = new Zend_Date($entry->getTimestamp());
        $now = Zend_Date::now();
        $difference = $now->sub($date);

        $seconds = $difference->toValue() % 60; $allMinutes = ($difference->toValue() - $seconds) / 60;
        $minutes = $allMinutes % 60; $allHours = ($allMinutes - $minutes) / 60;
        $hours =  $allHours % 24; $allDays = ($allHours - $hours) / 24;
        $allDays.= ' ';
        $hours.= ' ';
        $minutes.= ' ';

        if($allDays > 0) {
            $allDays .= $this->_('day');
            if($allDays > 1) {
                $allDays .= "s";
            }
        } else {
            $allDays = '';
        }
        if($hours > 0) {
            $hours .= $this->_('hour');
            if($hours > 1) {
                $hours .= "s";
            }
        } else {
            $hours = '';
        }
        if($minutes > 0) {
            $minutes .= $this->_('minute');
            if($minutes > 1) {
                $minutes .= "s";
            }
        } else {
            $minutes = '';
        }

        $updated_at = '';
        if($allDays != '') {
            $updated_at = $allDays;
        } elseif($hours != '') {
            $updated_at = $hours;
        } elseif($minutes != '') {
            $updated_at = $minutes;
        }

        return $updated_at;

    }


}