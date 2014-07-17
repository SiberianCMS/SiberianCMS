<?php

class Rss_Mobile_Feed_ViewController extends Application_Controller_Mobile_Default {

    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $feed_id = $this->getRequest()->getParam('feed_id')) {

            $rss_feed = new Rss_Model_Feed();
            $rss_feeds = $rss_feed->findAll(array('value_id' => $value_id), 'position ASC');
            $data = array('feed' => array());
            $feed_id = base64_decode($feed_id);
            $page_title = $this->getCurrentOptionValue()->getTabbarName();

            foreach($rss_feeds as $rss_feed) {

                $news = $rss_feed->getNews();
                foreach($news->getEntries() as $entry) {

                    if($feed_id == $entry->getEntryId()) {

                        $data = array(
                            "id" => base64_encode($entry->getEntryId()),
                            "url" => $entry->getEntryId(),
                            "title" => $entry->getTitle(),
                            "content" => $entry->getContent(),
                            "image_url" => $entry->getPicture(),
                            "updated_at" => $this->_('%s ago', $this->_getUpdatedAt($entry))
                        );
                    }

                }
            }

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