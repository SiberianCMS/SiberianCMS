<?php

class Cms_Mobile_Page_ViewController extends Application_Controller_Mobile_Default
{

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $option = $this->getCurrentOptionValue();
            $page = new Cms_Model_Application_Page();
            $page->find($option->getId(), 'value_id');
            $blocks = $page->getBlocks();
            $data = array("blocks" => array());

            foreach($blocks as $block) {

                $block->unsMobileTemplate()->unsTemplate();
                $block_data = $block->getData();

                switch($block->getType()) {
                    case "text":
                        $block_data["image_url"] = $block->getImageUrl();
                    break;
                    case "image":
                        $library = new Cms_Model_Application_Page_Block_Image_Library();
                        $libraries = $library->findAll(array('library_id' => $block->getLibraryId()), 'image_id ASC', null);
                        $block_data["gallery"] = array();
                        foreach($libraries as $image) {
                            $block_data["gallery"][] = array(
                                "id" => $image->getId(),
                                "url" => $image->getImageFullSize()
                            );
                        }
                    break;
                    case "video":
                        $video = $block->getObject();
                        $block_data["cover_url"] = $video->getImageUrl();
                        $url = $video->getLink();
                        if($video->getTypeId() == "youtube") {
                            $url = "http://www.youtube.com/embed/{$video->getYoutube()}?autoplay=1";
                        }
                        $block_data["url"] = $url;
                    break;
                }


                $data["blocks"][] = $block_data;

            }

            $data["page_title"] = $option->getTabbarName();

            $this->_sendHtml($data);
        }

    }

}