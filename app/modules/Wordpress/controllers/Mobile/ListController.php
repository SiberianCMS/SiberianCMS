<?php

class Wordpress_Mobile_ListController extends Application_Controller_Mobile_Default
{

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            try {
                $option_value = $this->getCurrentOptionValue();
                $wordpress = $option_value->getObject();

                $posts = $wordpress->getRemotePosts($this->isOverview(), null, !$this->isOverview());
                $cover = null;

                if(count($posts)) {
                    foreach($posts as $k => $post) {
                        if(!$post->getIsHidden()) {
                            $cover = $post;
                        }
                    }

                    if($cover AND $cover->getPicture()) {
                        $cover->setIsHidden(true);
                    } else {
                        $cover = null;
                    }
                }

                $data = array("posts" => array(), "cover" => array());

                foreach($posts as $post) {
                    $data["posts"][] = array(
                        "id" => $post->getId(),
                        "title" => $post->getTitle(),
                        "subtitle" => html_entity_decode($post->getShortDescription(), ENT_NOQUOTES, "UTF-8"),
                        "description" => $post->getDescription(),
                        "picture" => $post->getPicture(),
                        "date" => $post->getFormattedDate(),
                        "is_hidden" => !!$post->getIsHidden(),
                        "url" => $this->getPath("wordpress/mobile_view", array("value_id" => $value_id, "post_id" => $post->getId())),
                    );

                }

                if($cover) {
                    $data["cover"] = array(
                        "id" => $cover->getId(),
                        "title" => $cover->getTitle(),
                        "subtitle" => html_entity_decode($post->getShortDescription(), ENT_NOQUOTES, "UTF-8"),
                        "description" => $cover->getDescription(),
                        "picture" => $cover->getPicture(),
                        "date" => $post->getFormattedDate(),
                        "is_hidden" => false,
                        "url" => $this->getPath("wordpress/mobile_view", array("value_id" => $value_id, "post_id" => $cover->getId()))
                    );
                }

                $data["page_title"] = $this->getCurrentOptionValue()->getTabbarName();

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}