<?php

class Application_Model_Layout_Homepage extends Application_Model_Layout_Abstract {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Application_Model_Db_Table_Layout_Homepage';
        return $this;
    }

    public function getNumberOfDisplayedIcons() {
        return (int) $this->getData('number_of_displayed_icons');
    }

}
