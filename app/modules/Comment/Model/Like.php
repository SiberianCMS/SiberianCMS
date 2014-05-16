<?php
class Comment_Model_Like extends Core_Model_Default {

    protected $_comment;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Comment_Model_Db_Table_Like';
        return $this;
    }
    
    public function findByComment($comment_id, $pos_id = null) {
        $viewAll = true;
        return $this->getTable()->findByComment($comment_id, $pos_id);
    }
    
    public function setComment($comment) {
        $this->_comment = $comment;
        return $this;
    }
    
    public function save($comment_id, $customer_id, $ip, $ua) {
        $duplicate_like = $this->getTable()->findByIp($comment_id, $customer_id, $ip, $ua);
        if(count($duplicate_like) == 0) {
            parent::save();
            return true;
        } else {
            return false;
        }
    }

}
