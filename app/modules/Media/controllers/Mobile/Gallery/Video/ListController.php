<?php

class Media_Mobile_Gallery_Video_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam("value_id")) {

            try {

                $video = new Media_Model_Gallery_Video();
                $videos = $video->findAll(array('value_id' => $value_id));
                $data = array("galleries" => array());

                foreach($videos as $video) {
                    $data["galleries"][] = array(
                        "id" => $video->getId(),
                        "name" => $video->getName(),
                    );
                }

                $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();
                $data["header_right_button"]["picto_url"] = $this->_getColorizedImage($this->_getImage('pictos/more.png', true), $this->getApplication()->getBlock('subheader')->getColor());

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}