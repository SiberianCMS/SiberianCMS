<?php

class Application_Model_Db_Table_Option_Value extends Core_Model_Db_Table
{

    protected $_name = "application_option_value";
    protected $_primary = "value_id";

    public function findAll($values, $order, $params) {

        $option_fields = array_keys($this->_db->describeTable('application_option'));
        $option_fields = array_combine($option_fields, $option_fields);
        unset($option_fields['icon_id']);
        unset($option_fields['position']);
        $use_homepage_background_image = (int) Application_Model_Application::getInstance()->getUseHomepageBackgroundImageInSubpages();
        $homepage_background_image = Application_Model_Application::getInstance()->getHomepageBackgroundImageRetinaLink();
        $option_value_fields = $this->getFields();

        $option_value_fields['tabbar_name'] = new Zend_Db_Expr('IFNULL(aov.tabbar_name, ao.name)');
        $option_value_fields['layout_id'] = new Zend_Db_Expr('IFNULL(aov.layout_id, "1")');
        if($use_homepage_background_image) {
            if($homepage_background_image) {
                $option_value_fields['background_image'] = new Zend_Db_Expr('IFNULL(aov.background_image, "'.$homepage_background_image.'")');
            } else {
                $option_value_fields['background_image'] = new Zend_Db_Expr('IFNULL(aov.background_image, "no-image")');
            }
        } else {
            $option_value_fields['background_image'] = new Zend_Db_Expr('aov.background_image');
        }
        $option_value_fields['use_homepage_background_image'] = new Zend_Db_Expr($use_homepage_background_image);
        $option_value_fields['has_background_image'] = new Zend_Db_Expr('IF(aov.background_image IS NOT NULL, 1, 0)');
        $option_value_fields['icon_id'] = new Zend_Db_Expr('IFNULL(aov.icon_id, ao.icon_id)');

        $select = $this->select()
            ->from(array('aov' => $this->_name), $option_value_fields)
            ->join(array('ao' => 'application_option'), 'ao.option_id = aov.option_id', $option_fields)
        ;

        if(!empty($values)) {
            foreach($values as $quote => $value) {
                if($value instanceof Zend_Db_Expr) $select->where($value);
                else if(stripos($quote, '?') !== false) $select->where($this->_db->quoteInto($quote, $value));
                else $where[] = $select->where($this->_db->quoteInto($quote . ' = ?', $value));
            }
        }

        if(!empty($order)) $select->order($order);
        else $select->order('aov.folder_category_position ASC')->order('aov.position ASC')->order('ao.position ASC');

        if(!empty($params)) {
            if(!empty($params['limit'])) $select->limit($params['limit']);
            if(!empty($params['offset'])) $select->offset($params['offset']);
        }

        $select->setIntegrityCheck(false);

        $rows = $this->fetchAll($select);

        foreach($rows as $row) {
            $row->prepareUri();
        }

        return $rows;

    }

    public function getLastPosition() {

        $select = $this->select()->from($this->_name, array('position'))
            ->order('position DESC')
            ->limit(1)
        ;

        $position = $this->_db->fetchOne($select);

        return $position ? $position : 0;

    }

    public function getLastFolderCategoryPosition($category_id) {

        $select = $this->select()->from($this->_name, array('folder_category_position'))
            ->where('folder_category_id = ?', $category_id)
            ->order('folder_category_position DESC')
            ->limit(1)
        ;

        $position = $this->_db->fetchOne($select);

        return $position ? $position : 0;

    }

    public function getFolderValues($option_id) {
        $select = $this->select()
            ->from(array('aov' => 'application_option_value'))
            ->where('aov.option_id = ?', $option_id)
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }

    public function findLibraryId($option_id) {
        $select = $this->_db->select()
            ->from(array('ao' => 'application_option'), array('library_id'))
            ->where('ao.option_id = ?', $option_id)
        ;

        return $this->_db->fetchOne($select);
    }

    public function getOptionDatas($option_id) {
        $fields = array_keys($this->_db->describeTable('application_option'));
        $fields = array_combine($fields, $fields);
        $fields['tabbar_name'] = new Zend_Db_Expr('name');
        $fields['use_homepage_background_image'] = new Zend_Db_Expr((int) Application_Model_Application::getInstance()->getUseHomepageBackgroundImageInSubpages());
        $select = $this->_db->select()
            ->from(array('ao' => 'application_option'), $fields)
            ->where('ao.option_id = ?', $option_id)
        ;
        return $this->_db->fetchRow($select);
    }

}