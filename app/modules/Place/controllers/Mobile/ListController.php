<?php

class Place_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($this->getRequest()->getParam('value_id')) {

            $option = $this->getCurrentOptionValue();
            $place = new Place_Model_Place();
//            $places = $place->findAll(array("value_id" => $option->getId()));
            $places = $place->findAll();
            $data = array("places" => array());

            $fields = $place->getFields();
//            unset($fields["place_id"]);
            unset($fields["information"]);
            unset($fields["opening_details"]);
            $fields += array("type", "label", "label_picture", "status", "picture_url");
//            $fields = array("identifier", "name", "street", "postcode", "city", "phone", "rating", "type_id", "type", "label_id", "label", "status_id", "status", "latitude", "longitude", "min_price", "max_price", "meal_min_price", "meal_max_price", "number_of_rooms", "opening_details");
            foreach($places as $place) {
                $place_data = array();
                foreach($fields as $field) {
                    $func = "get".Core_Model_Lib_String::decamelize($field);
                    if($place->$func()) {
                        $place_data[$field] = $place->$func();
                    }
                }
                $data['places'][] = $place_data;
            }

            foreach(Place_Model_Place::getStatuses() as $status) {
                $data['statuses'][] = $status->getData();
            }

            foreach(Place_Model_Place::getLabels() as $label) {
                $data['labels'][] = $label->getData();
            }

            $data['page_title'] = $option->getTabbarName();

            $this->_sendHtml($data);

        }
    }

}

