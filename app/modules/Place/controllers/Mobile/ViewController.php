<?php

class Place_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($place_id = $this->getRequest()->getParam('place_id')) {

            $place = new Place_Model_Place();
            $place->find($place_id);

            $data = array(
                "information" => $place->getInformation(),
                "opening_details" => $place->getOpeningDetails()
            );

            $this->_sendHtml($data);

        }

    }

}