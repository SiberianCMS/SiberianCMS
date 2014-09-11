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

            $description = "";
            if($entry->getContent()) {
                $content = new Dom_SmartDOMDocument();
                $content->loadHTML($entry->getContent());
                $content->encoding = 'utf-8';
                $description = $content->documentElement;
                $imgs = $description->getElementsByTagName('img');

                if($imgs->length > 0) {

                    foreach($imgs as $k => $img) {
                        if($k == 0) {

                            $img = $imgs->item(0);

                            if($img->getAttribute('src') AND stripos($img->getAttribute('src'), ".gif") === false) {
                                $picture = $img->getAttribute('src');
                                $img->parentNode->removeChild($img);
                            }

                        }

                        $img->removeAttribute('width');
                        $img->removeAttribute('height');
                    }

                }

                $as = $description->getElementsByTagName('a');

                if($as->length > 0) {

                    foreach($as as $a) {
                        if(!$a->hasAttribute('target')) {
                            $a->setAttribute('target', '_blank');
                        }
                    }
                }

                $description = $content->saveHTMLExact();
            }

            $edata = new Core_Model_Default(array(
                'entry_id'     => $entry->getId(),
                'title'        => $entry->getTitle(),
                'description'  => $description,
                'short_description'  => strip_tags($description),
                'dateModified' => $entry->getDateModified(),
                'authors'      => $entry->getAuthors(),
                'link'         => $entry->getLink(),
                'content'      => $description,
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
