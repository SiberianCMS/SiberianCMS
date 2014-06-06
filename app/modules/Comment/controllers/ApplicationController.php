<?php

class Comment_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {
        $html = '';
        if($datas = $this->getRequest()->getPost()) {
            try {
                if(!empty($datas['text'])) {

                    $comment = new Comment_Model_Comment();
                    $img_src = Core_Model_Directory::getTmpDirectory(true).'/';
                    $image = '';
                    if(empty($datas['image'])) {
                        $datas['image'] = null;
                    } else if(file_exists($img_src.$datas['image'])) {
                        $img_src = $img_src.$datas['image'];
                        $relativePath = '/feature/'.$this->getCurrentOptionValue()->getId();
                        $img_dst = Application_Model_Application::getBaseImagePath().$relativePath;
                        if(!is_dir($img_dst)) mkdir($img_dst, 0777, true);
                        $img_dst .= '/'.$datas['image'];
                        @rename($img_src, $img_dst);
                        if(!file_exists($img_dst)) throw new Exception($this->_('An error occurred while saving your picture. Please try againg later.'));
                        $datas['image'] = $relativePath.'/'.$datas['image'];
                        $image = Application_Model_Application::getImagePath().'/';
                        $image .= $datas['image'];
                    }

                    $comment->setData($datas)
                        ->save()
                    ;

                    $url = array('comment/admin/edit');
                    if($pos_id) $url[] = 'pos_id/'.$pos_id;

                    $html = array(
                        'success' => '1',
                        'success_message' => $this->_('Information successfully saved'),
                        'image' => $image,
                        'message_timeout' => 2,
                        'message_button' => 0,
                        'message_loader' => 0
                    );

                }
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function deleteAction() {
        $html = '';
        if($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->delete();
                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Information successfully deleted'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function hideAction() {
        $html = '';
        if($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->setisVisible(0)->save();
                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Information successfully hidden'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function showAction() {
        $html = '';
        if($id = $this->getRequest()->getParam('id')) {
            try {
                $comment = new Comment_Model_Comment();
                $comment->find($id)->setisVisible(1)->save();
                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Information successfully shown'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            } catch (Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function validatecropAction() {
        if($datas = $this->getRequest()->getPost()) {
            try {
                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);
                $datas = array(
                    'success' => 1,
                    'file' => $file,
                    'message_success' => 'Enregistrement réussi',
                    'message_button' => 0,
                    'message_timeout' => 2,
                );
            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }
            $this->getLayout()->setHtml(Zend_Json::encode($datas));
         }
    }

}
