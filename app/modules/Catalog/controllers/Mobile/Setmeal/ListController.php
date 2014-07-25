<?php

class Catalog_Mobile_Setmeal_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $data = array("set_meal" => array());

            $menu = new Catalog_Model_Product();
            $menus = $menu->findAll(array('value_id' => $value_id, 'type' => 'menu'));

            foreach($menus as $menu) {
                $data["set_meal"][] = array(
                    "title" => $menu->getName(),
                    "subtitle" => $menu->getConditions(),
                    "picture" => $menu->getPictureUrl(),
                    "url" => $this->getPath("catalog/mobile_setmeal_view", array("value_id" => $value_id, "set_meal_id" => $menu->getId())),
                );
            }

            $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();

            $this->_sendHtml($data);
        }
    }

}