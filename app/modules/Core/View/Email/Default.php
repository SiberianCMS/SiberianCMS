<?php

class Core_View_Email_Default extends Core_View_Default
{

    public function getImage($name) {
        return $this->getRequest()->getMediaUrl().'/app/design/email/images/' . $name;
    }

    public function getJs($name) {
        return $this->getRequest()->getMediaUrl().'/app/design/email/js/' . $name;
    }

}