<?php

class Siberian_Pdf extends Zend_Pdf
{
    /**
     * @param mixed $param1
     * @param mixed $param2
     * @return Siberian_Pdf_Page
     */
    public function newPage($param1, $param2 = null)
    {
        require_once 'Siberian/Pdf/Page.php';
        if ($param2 === null) {
            return new Siberian_Pdf_Page($param1, $this->_objFactory);
        } else {
            return new Siberian_Pdf_Page($param1, $param2, $this->_objFactory);
        }
    }
}
