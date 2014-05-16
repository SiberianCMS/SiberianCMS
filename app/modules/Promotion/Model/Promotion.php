<?php

class Promotion_Model_Promotion extends Core_Model_Default
{

    public function __construct($datas = array()) {
        parent::__construct($datas);
        $this->_db_table = 'Promotion_Model_Db_Table_Promotion';
    }

    public function getFormattedEndAt() {
        if($this->getData('end_at')) {
            $date = new Zend_Date($this->getData('end_at'));
            return $date->toString($this->_('MM/dd/y'));
        }
    }

    public function hasCondition() {
        return !is_null($this->getConditionType());
    }

    public function resetConditions() {
        $conditions = array('type', 'number_of_points', 'period_number', 'period_type');
        foreach($conditions as $name) {
            $this->setData('condition_'.$name, null);
        }
        return $this;
    }

    public function getUsedPromotions($start_at, $end_at) {
        return $this->getTable()->getUsedPromotions($start_at, $end_at);
    }

    public function save() {
        if($this->getIsIllimited()) $this->setEndDate(null);
        parent::save();
    }

}