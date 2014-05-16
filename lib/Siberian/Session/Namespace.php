<?php

class Siberian_Session_Namespace extends Zend_Session_Namespace
{

    public function & __get($name)
    {
        $value = parent::__get($name);
        if(@unserialize($value)) $value = unserialize($value);

        return $value;
    }

    public function __set($name, $value)
    {
        if(is_object($value)) $value = serialize($value);
        parent::__set($name, $value);
    }

}
