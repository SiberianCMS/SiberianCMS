<?php

class Application_PromoteController extends Application_Controller_Default {

    public function indexAction() {
        $this->loadPartials();
    }

    public function qrcodeAction() {

//        if($this->getApplication()->getDomain()) {
            $qrcode = file_get_contents($this->getApplication()->getQrcode(null, array('size' => '512x512', 'without_template' => 1)));
            if($qrcode) {
                $this->_download($qrcode, 'qrcode.png', 'image/png');
            }
            else {
                $this->getSession()->addError($this->_('An error occurred during the generation of your QRCode. Please try again later.'));
                $this->_redirect('application/promote');
            }
//        }

    }

}
