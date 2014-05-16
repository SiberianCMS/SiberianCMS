<?php

class Rss_Model_Feed extends Rss_Model_Feed_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Rss_Model_Db_Table_Feed';
        return $this;
    }

    public function updatePositions($positions) {
        $this->getTable()->updatePositions($positions);
        return $this;
    }

    public function getNews() {

        if($this->getId() AND empty($this->_news)) {
            $this->_parse();
        }

        return $this->_news;
    }

}
