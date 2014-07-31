<?php

class Wordpress_Mobile_ListController extends Application_Controller_Mobile_Default
{

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {
                $option_value = $this->getCurrentOptionValue();
                $wordpress = $option_value->getObject();

                $posts = $wordpress->getRemotePosts($this->getRequest()->getParam('overview'), null, !$this->getRequest()->getParam('overview'));

                if(count($posts)) {
                    $cover = current($posts);
                    if($cover->getPicture()) {
                        $posts = array_slice($posts, 1, count($posts));
                    } else {
                        $cover = null;
                    }
                }

                $data = array("posts" => array(), "cover" => array());

                foreach($posts as $post) {
                    $data["posts"][] = array(
                        "id" => $post->getId(),
                        "title" => $post->getTitle(),
                        "subtitle" => $post->getShortDescription(),
                        "description" => $post->getDescription(),
                        "picture" => $post->getPicture(),
                        "url" => $this->getPath("wordpress/mobile_view", array("value_id" => $value_id, "post_id" => $post->getId())),
                    );
                }

                $data["cover"] = array(
                    "id" => $cover->getId(),
                    "title" => $cover->getTitle(),
                    "subtitle" => $cover->getFormattedDate(),
                    "description" => $cover->getDescription(),
                    "picture" => $cover->getPicture(),
                    "url" => $this->getPath("wordpress/mobile_view", array("value_id" => $value_id, "post_id" => $cover->getId()))
                );

                $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}