<?php

abstract class Rss_Model_Feed_Abstract extends Core_Model_Default {

    protected $_news = array();

    protected function _parse() {

        $feed = Zend_Feed_Reader::import($this->getLink());
        $this->_news = new Core_Model_Default(array(
            'title'        => $feed->getTitle(),
            'link'         => $feed->getLink(),
            'dateModified' => $feed->getDateModified(),
            'description'  => $feed->getDescription(),
            'language'     => $feed->getLanguage(),
            'entries'      => array(),
        ));
        $data = array();
        foreach ($feed as $entry) {
            $picture = null;
            if($entry->getEnclosure() && $entry->getEnclosure()->url) $picture = $entry->getEnclosure()->url;

            if($entry->getDescription()) {
                $content = new Dom_SmartDOMDocument();
                $content->loadHTML($entry->getDescription());
                $content->encoding = 'utf-8';
                $description = $content->documentElement;
                $imgs = $description->getElementsByTagName('img');

                if($imgs->length > 0) {
                    $img = $imgs->item(0);

                    if($img->getAttribute('src')) {
                        $picture = $img->getAttribute('src');
                    }

                }
            }

            $edata = new Core_Model_Default(array(
                'title'        => $entry->getTitle(),
                'description'  => strip_tags($entry->getDescription()),
                'dateModified' => $entry->getDateModified(),
                'authors'      => $entry->getAuthors(),
                'link'         => $entry->getLink(),
                'content'      => strip_tags($entry->getContent()),
                'enclosure'    => $entry->getEnclosure(),
                'timestamp'    => $entry->getDateCreated()->getTimestamp(),
                'picture'      => $picture,
            ));
            $data[] = $edata;
        }

        $this->_news->setEntries($data);

        return $this;
    }

}
