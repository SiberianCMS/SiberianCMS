<?php

class Place_Application_FormController extends Application_Controller_Default
{

    public function viewAction() {

        if($data = $this->getRequest()->getParams()) {

            try {

                $place_id = !empty($data["place_id"]) ? $data["place_id"] : null;
                $place = new Place_Model_Place();
                $place->find($place_id);

                $this->loadPartials(null, false);
                $html = array("form" => $this->getLayout()->getPartial("content")
                    ->setPlace($place)
                    ->setValueId($data["value_id"])
                   ->toHtml()
                );

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function saveAction() {

        if($data = $this->getRequest()->getPost()) {

            $html = array();

            try {

                // Test s'il y a un value_id
                if(empty($data['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($data['value_id']);

                $place = new Place_Model_Place();
                $dummy = new Place_Model_Place();

                if($id = $this->getRequest()->getParam('id')) {
                    $place->find($id);
                    if($place->getValueId() AND $place->getValueId() != $option_value->getId()) {
                        throw new Exception('An error occurred while saving. Please try again later.');
                    }
                }

                $required_fields = array("identifier", "name", "street", "postcode", "city", "phone");
                foreach($required_fields as $required_field) {
                    if(empty($data[$required_field])) {
                        throw new Exception($this->_("An error occurred while saving. Please fill in all the fields"));
                    }
                }

                $dummy->find($data["identifier"], "identifier");
                if($dummy->getId() AND $dummy->getId() != $place->getId()) {
                    throw new Exception($this->_("This identifier is already used."));
                }

                $place->setData($data);

                if(!$place->getLatitude() OR !$place->getLongitude()) {
                    $place->findCoordinates();

                    if(!$place->getLatitude() OR !$place->getLongitude()) {
                        throw new Exception($this->_("Unable to automatically retrieve the coordinates of this place. Please, provide them manually."));
                    }
                }

                $place->save();

                $html = array(
                    'success_message' => $this->_("Info successfully saved"),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'url' => '/promotion/admin/list'
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }
    }

}