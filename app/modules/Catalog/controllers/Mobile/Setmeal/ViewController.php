<?php

class Catalog_Mobile_Setmeal_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $set_meal_id = $this->getRequest()->getParam('set_meal_id')) {

            $set_meal = new Catalog_Model_Product();
            $set_meal->find($set_meal_id);

            $data = array();

            if($set_meal->getData("type") == "menu") {

                $data = array(
                    "name" => $set_meal->getName(),
                    "conditions" => $set_meal->getConditions(),
                    "description" => $set_meal->getDescription(),
                    "price" => $set_meal->getFormattedPrice(),
                    "picture" => $set_meal->getPictureUrl()
                );

            }

            $this->_sendHtml($data);
        }
    }

}