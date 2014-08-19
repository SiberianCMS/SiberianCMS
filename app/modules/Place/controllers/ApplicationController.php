<?php

class Place_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $application = $this->getApplication();

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving your contact informations.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $html = '';
                $contact = new Contact_Model_Contact();
                $contact->find($option_value->getId(), 'value_id');

                if(!empty($datas['file'])) {
                    $relative_path = '/feature/contact/cover/';
                    $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                    $file = Core_Model_Directory::getTmpDirectory(true).'/'.$datas['file'];

                    if(!is_dir($folder)) mkdir($folder, 0777, true);
                    if(!copy($file, $folder.$datas['file'])) {
                        throw new exception($this->_('An error occurred while saving your picture. Please try again later.'));
                    } else {
                        $datas['cover'] = $relative_path.$datas['file'];
                    }
                }
                else if(!empty($datas['remove_cover'])) {
                    $datas['cover'] = null;
                }

                $contact->setData($datas)->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Info successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
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

    public function uploadAction() {

        if(!empty($_FILES) AND $value_id = $this->getRequest()->getParam("value_id")) {
            try {

                $destination = Core_Model_Directory::getTmpDirectory(true);
                $params = array(
                    "destination_folder" => $destination,
                    "uniq" => 1,
                    "desired_name" => "file.xml",
                    "validators" => array(
                        'Extension' => array('xml', 'case' => false),
                        'Size' => array('min' => 100, 'max' => 8000000),
                    )
                );
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->upload($params);

                $places = Place_Model_Parser::parse("$destination/$file");

                if(empty($places)) {
                    throw new Exception($this->_("An error occured while parsing the file. Please try again later."));
                }

                foreach($places as $data) {
                    $place = new Place_Model_Place();
                    $place->find($data["identifier"], "identifier");
                    if(!$place->getId()) {
                        $data["value_id"] = $value_id;
                    }
                    $place->addData($data)->save();
                }

                $data = array(
                    'success' => 1,
                    'message' => $this->_("%d places updated", count($places)),
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $data = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($data));
         }

    }

    public function updatelocationsAction() {

        if($value_id = $this->getRequest()->getParam("value_id")) {
            try {

                $place = new Place_Model_Place();
                $places = $place->findAll(array("empty_coordinates" => new Zend_Db_Expr("latitude IS NULL")));
                $cpt = 0;
//                foreach($places as $place) {
//
//                    $address = array(
//                        "street" => $place->getStreet(),
//                        "postcode" => $place->getPostcode(),
//                        "city" => $place->getCity()
//                    );
//
//                    try {
//                        list($latitude, $longitude) = Siberian_Google_Geocoding::getLatLng($address);
//                        if(!empty($latitude) AND !empty($longitude)) {
//                            $place->setLatitude($latitude)
//                                ->setLongitude($longitude)
//                                ->save()
//                            ;
//
//                            $cpt++;
//                        }
//                    } catch(Exception $e) {
//
//                    }
//
//                }

                $data = array(
                    'success' => 1,
                    'success_message' => $this->_("%d places indexed", $cpt),
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $data = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($data));
         }

    }

}