<?php

class Socialgaming_View_Admin_Game_Edit extends Core_View_Default {

    protected $_game;

    public function setGame($game) {
        $this->_game = $game;
        return $this;
    }

    public function getGame() {
        return $this->_game;
    }
}
