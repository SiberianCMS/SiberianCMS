<?php
class Booking_Model_Booking extends Core_Model_Default {

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Booking_Model_Db_Table_Booking';
        return $this;
    }

}
