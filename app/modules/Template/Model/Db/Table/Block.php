<?php

class Template_Model_Db_Table_Block extends Core_Model_Db_Table {

    protected $_name = "template_block";
    protected $_primary = "block_id";

    public function findByDesign($design_id) {

        $select = $this->select()
            ->from(array('td' => 'template_design'), array())
            ->join(array('tdb' => 'template_design_block'), 'tdb.design_id = td.design_id', array('block_id', 'color', 'background_color', 'image_color'))
            ->join(array('tb' => $this->_name), 'tb.block_id = tdb.block_id', array('name', 'code', 'position', 'created_at', 'updated_at'))
            ->where('td.design_id = ?', $design_id)
//            ->order('tdb.position ASC')
            ->setIntegrityCheck(false)
        ;

        return $this->fetchAll($select);
    }

}
