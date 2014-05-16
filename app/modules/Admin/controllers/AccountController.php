<?php

class Admin_AccountController extends Admin_Controller_Default
{

    public function listAction() {
        $this->loadPartials();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {

        $current_admin = $this->getSession()->getAdmin();
        $admin_id = $this->getRequest()->getParam('admin_id');
        $admin = new Admin_Model_Admin();

        if($current_admin->getParentId()) {
            $admin = $this->getSession()->getAdmin();
        }
        else if($admin_id = $this->getRequest()->getParam('admin_id')) {

            $admin->find($admin_id);
            if(!$admin->getId() OR $current_admin->getParentId()) {
                $this->getSession()->addError($this->_('This administrator does not exist'));
                $this->_redirect('admin/account/list');
                return $this;
            }
        }

        $this->loadPartials();
        $this->getLayout()->getPartial('content')->setCurrentAdmin($admin);
    }

    public function savepostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $admin = new Admin_Model_Admin();
            $current_admin = $this->getSession()->getAdmin();
            $check_email_admin = new Admin_Model_Admin();
            $html = '';

            try {

                if(!empty($datas['admin_id'])) {
                    $admin->find($datas['admin_id']);
                    if(!$admin->getId() OR $current_admin->getParentId() AND $admin->getId() != $current_admin->getId()) {
                        throw new Exception($this->_('An error occurred while saving your account. Please try again later.'));
                    }
                }
                if(empty($datas['email'])) {
                    throw new Exception($this->_('The email is required'));
                }
                $isNew = (bool) !$admin->getId();

                $check_email_admin->find($datas['email'], 'email');
                if($check_email_admin->getId() AND $check_email_admin->getId() != $admin->getId()) {
                    throw new Exception($this->_('This email address is already used'));
                }


                if(isset($datas['password'])) {
                    if($datas['password'] != $datas['confirm_password']) {
                        throw new Exception($this->_('Your password does not match the entered password.'));
                    }
                    if(!empty($datas['old_password']) AND !$admin->isSamePassword($datas['old_password'])) {
                        throw new Exception($this->_("The old password does not match the entered password."));
                    }
                    if(!empty($datas['password'])) {
                        $admin->setPassword($datas['password']);
                        unset($datas['password']);
                    }
                } else if($isNew) {
                    throw new Exception($this->_('The password is required'));
                }

                if($isNew) {
                    $datas['parent_id'] = $current_admin->getId();
                }

                $admin->addData($datas)
                    ->save()
                ;

                $html = array('success' => 1);
                if($current_admin->getParentId()) {
                    $html = array_merge($html, array(
                        'success_message' => $this->_('The account has been successfully saved'),
                        'message_timeout' => false,
                        'message_button' => false,
                        'message_loader' => 1
                    ));
                } else {
                    $this->getSession()->addSuccess($this->_('The account has been successfully saved'));
                }
            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);

        }


    }

    public function deleteAction() {

        if($admin_id = $this->getRequest()->getParam('admin_id') AND !$this->getSession()->getAdmin()->getParentId()) {

            try {

                $admin = new Admin_Model_Admin();
                $admin->find($admin_id);

                if(!$admin->getId()) {
                    throw new Exception($this->_("This administrator does not exist"));
                } else if(!$admin->getParentId()) {
                    throw new Exception($this->_("You can't delete the main account"));
                }

                $admin->delete();

                $html = array(
                    'success' => 1,
                    'admin_id' => $admin_id
                );

            }
            catch(Exception $e) {
                $html = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->_sendHtml($html);

        }

    }

    public function loginAction() {
        $this->loadPartials();
    }

    public function loginpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $this->getSession()->resetInstance();
            $canBeLoggedIn = false;

            try {

                if(empty($datas['email']) OR empty($datas['password'])) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }
                $admin = new Admin_Model_Admin();
                $admin->findByEmail($datas['email']);

                if($admin->authenticate($datas['password'])) {
                    $this->getSession()
                        ->setAdmin($admin)
                    ;
                }

                if(!$this->getSession()->isLoggedIn()) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }

                $notif = new Admin_Model_Notification();
                $notif->update();

            }
            catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('/');
        return $this;

    }

    public function forgotpasswordpostAction() {

        if($datas = $this->getRequest()->getPost() AND !$this->getSession()->isLoggedIn('admin') AND !$this->getSession()->isLoggedIn('pos')) {

            try {

                if(empty($datas['email'])) {
                    throw new Exception($this->_('Please enter your email address'));
                }

                $admin = new Admin_Model_Admin();
                $admin->findByEmail($datas['email']);

                if(!$admin->getId()) {
                    throw new Exception($this->_("Your email address does not exist"));
                }

                $password = Core_Model_Lib_String::generate(8);

                $admin->setPassword($password)->save();

                $layout = $this->getLayout()->loadEmail('admin', 'forgot_password');
                $subject = $this->_('%s – Your new password', 'Siberian');
                $from_email = 'no-reply@siberiancms.com';
                $from_name = 'Siberian CMS';
                $layout->getPartial('content_email')->setPassword($password);

                $content = $layout->render();

                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($content);
                $mail->setFrom($from_email, $from_name);
                $mail->addTo($admin->getEmail(), $admin->getName());
                $mail->setSubject($subject);
                $mail->send();

                $this->getSession()->addSuccess($this->_('Your new password has been sent to the entered email address'));

            }
            catch(Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('/');
        return $this;

    }

    public function logoutAction() {
        $this->getSession()->resetInstance();
        $this->_redirect('');
        return $this;
    }

}
