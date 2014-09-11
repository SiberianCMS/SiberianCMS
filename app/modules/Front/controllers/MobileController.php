<?php

class Front_MobileController extends Application_Controller_Mobile_Default {

    public function styleAction() {
        $html = $this->getLayout()->addPartial('style', 'core_view_mobile_default', 'page/css.phtml')->toHtml();
        $this->getLayout()->setHtml($html);
    }

    public function backgroundimageAction() {

        $url = "";
        $option = $this->getCurrentOptionValue();

        if($option->getIsHomepage()) {
            $url = $this->getApplication()->getHomepageBackgroundImageUrl("retina4");
        } else if($option->hasBackgroundImage()) {
            $url = $option->getBackgroundImageUrl();
        } else if($option->getUseHomepageBackgroundImage()) {
            $url = $this->getApplication()->getHomepageBackgroundImageUrl("retina");
        }

        $this->getLayout()->setHtml($url);

    }

    protected function _getBackgroundImage() {

        $url = "";
        $option = $this->getCurrentOptionValue();

        if($option->getIsHomepage()) {
            $url = $this->getApplication()->getBackgroundImageUrl("retina4");
        } else if($option->getHasBackgroundImage()) {
            $url = $option->getBackgroundImageUrl();
        } else if($option->getUseHomepageBackgroundImage()) {
            $url = $this->getApplication()->getHomepageBackgroundImageUrl("retina");
        }

        return $url;
    }

}