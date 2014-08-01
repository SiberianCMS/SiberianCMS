<?php

class Comment_Mobile_ViewController extends Application_Controller_Mobile_Default {


    public function indexAction() {
        $this->forward('index', 'index', 'Front', $this->getRequest()->getParams());
    }

    public function templateAction() {
        $this->loadPartials($this->getFullActionName('_').'_l'.$this->_layout_id, false);
    }

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id') AND $comment_id = $this->getRequest()->getParam('news_id')) {
            $application = $this->getApplication();
            $comment = new Comment_Model_Comment();
            $comment->find($comment_id);

            if($comment->getId() AND $comment->getValueId() == $value_id) {

                $color = $application->getBlock('background')->getColor();

                $data = array(
                    "id" => $comment->getId(),
                    "author" => $application->getName(),
                    "message" => $comment->getText(),
                    "picture" => $comment->getImageUrl(),
                    "icon" => $application->getIcon(74),
                    "can_comment" => true,
                    "created_at" => $comment->getCreatedAt(),
                    "number_of_likes" => count($comment->getLikes())
                );

                $this->_sendHtml($data);
            }

        }

    }

    public function addlikeAction() {

        if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                $customer_id = $this->getSession()->getCustomerId();
                $ip = md5($_SERVER['REMOTE_ADDR']);
                $ua = md5($_SERVER['HTTP_USER_AGENT']);
                $like = new Comment_Model_Like();

                if(!$like->findByIp($data['news_id'], $customer_id, $ip, $ua)) {

                    $like->setCommentId($data['news_id'])
                        ->setCustomerId($customer_id)
                        ->setCustomerIp($ip)
                        ->setAdminAgent($ua)
                    ;

                    $like->save();

                    $message = $this->_('Your like has been successfully added');
                    $html = array('success' => 1, 'message' => $message);

                } else {
                    throw new Exception($this->_("You can't like more than once the same news"));
                }

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }

    }

}