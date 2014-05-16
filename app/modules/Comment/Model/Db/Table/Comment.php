<?php
class Comment_Model_Db_Table_Comment extends Core_Model_Db_Table {

    protected $_name="comment";
    protected $_primary="comment_id";

    public function findByPos($value_id) {

        $select = $this->_prepareSelect($value_id);
        $select->order('created_at DESC');

        return $this->fetchAll($select);
    }

    public function findLast($value_id) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->order('c.created_at DESC')
            ->limit(1)
        ;

        return $this->fetchRow($select);
    }

    public function findLastFive($value_id) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->order('c.created_at DESC')
            ->limit(5)
        ;
        return $this->fetchAll($select);
    }

    public function pullMore($value_id, $start, $count) {
        $select = $this->_prepareSelect($value_id);
        $select
            ->where('is_visible = 1')
            ->where('comment_id < ?', $start)
            ->order('c.created_at DESC')
            ->limit($count)
        ;
        return $this->fetchAll($select);
    }

    protected function _prepareSelect($value_id) {

        $select = $this->select()
            ->from(array('c' => $this->_name))
            ->where($this->_db->quoteInto('c.value_id = ?', $value_id))
        ;

        return $select;

    }

}