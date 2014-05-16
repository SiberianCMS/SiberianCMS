<?php

class Core_View_Mobile_Default extends Core_View_Default
{

    protected static $_current_option;
    protected static $_blocks;

    public function setCurrentOption($option) {
        self::$_current_option = $option;
    }

    public function getCurrentOption() {
        return self::$_current_option;
    }

    public static function setBlocks($blocks) {
        self::$_blocks = $blocks;
    }

    public function getBlocks() {
        return self::$_blocks;
    }

    public function getBlock($code) {
        foreach($this->getBlocks() as $block) {
            if($block->getCode() == $code) return $block;
        }

        return new Template_Model_Block();
    }
}