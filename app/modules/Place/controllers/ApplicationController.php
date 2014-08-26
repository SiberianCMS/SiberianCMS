<?php

class Place_ApplicationController extends Application_Controller_Default
{

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
                $estimated_time = 0;
                if(empty($places)) {
                    throw new Exception($this->_("An error occured while parsing the file. Please try again later."));
                }

                foreach($places as $data) {
                    $place = new Place_Model_Place();
                    $place->find($data["identifier"], "identifier");
//                    if(!$place->getId()) {
//                        $data["value_id"] = $value_id;
//                    }
                    $place->addData($data)->save();
                    if(!$place->getLatitude()) {
                        $estimated_time++;
                    }
                }

                $hours = 0;
                $minutes = 0;
                if($estimated_time > 3600) {
                    $estimated_time = round($estimated_time / 3600);
                    if($estimated_time > 1) $estimated_time = $this->_("about %s hours", $estimated_time);
                    else $estimated_time = $this->_("about %s hour", $estimated_time);
                } else if($estimated_time > 60) {
                    $estimated_time = round($estimated_time / 60);
                    if($estimated_time > 1) $estimated_time = $this->_("about %s minutes", $estimated_time);
                    else $estimated_time = $this->_("about %s minute", $estimated_time);
                } else {
                    $estimated_time = $this->_("less than a minute");
                }

                $data = array(
                    'success' => 1,
                    'estimated_time' => $estimated_time,
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
                foreach($places as $place) {

                    $place->findCoordinates();

                    if($place->getLatitude() AND $place->getLongitude()) {
                        $place->save();
                        $cpt++;
                    }

                }

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