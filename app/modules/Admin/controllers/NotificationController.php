<?php

class Admin_NotificationController extends Admin_Controller_Default
{

    public function listAction() {
        $notif = new Admin_Model_Notification();
        $notif->update();
        $this->loadPartials();
    }

    public function markasAction() {

        if($notif_id = $this->getRequest()->getPost('notif_id')) {

            try {
                $notification = new Admin_Model_Notification();
                $notification->find($notif_id);
                if(!$notification->getId()) {
                    throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }
                $is_read = (int) $this->getRequest()->getPost('is_read', 0);
                $notification->setIsRead($is_read)->save();
                $html = array('success' => 1, 'nbr_unread' => $notification->countUnread());

            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

}
