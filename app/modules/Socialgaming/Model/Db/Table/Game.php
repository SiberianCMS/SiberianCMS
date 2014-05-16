<?php

class Socialgaming_Model_Db_Table_Game extends Core_Model_Db_Table {

    protected $_name = "socialgaming_game";
    protected $_primary = "game_id";

    public function findCurrent() {
        $select = $this->_prepareSelect();
        $select->order('main.created_at ASC');

        return $this->fetchRow($select);
    }

    public function findNext() {
        $select = $this->_prepareSelect();
        $select->order('main.created_at DESC');

        $last = $this->fetchRow($select);
        $current = $this->findCurrent();
        if($current AND $last AND $current->getId() == $last->getId()) {
            $last = null;
        }

        return $last;
    }

    protected function _prepareSelect() {
        return $this->select()
            ->from(array('main' => $this->_name))
            ->where('(main.end_at >= NOW() OR main.end_at IS NULL)')
            ->limit(1)
        ;
    }
}
