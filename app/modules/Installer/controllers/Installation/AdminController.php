<?php

class Installer_Installation_AdminController extends Installer_Controller_Installation_Default {

    public function createAction() {

        if($datas = $this->getRequest()->getPost()) {

            $admin = new Admin_Model_Admin();
            try {
                if(empty($datas['email']) OR empty($datas['password']) OR empty($datas['confirm_password'])) {
                    throw new Exception($this->_('Please, fill out all fields'));
                }
                if(!Zend_Validate::is($datas['email'], 'emailAddress')) {
                    throw new Exception($this->_('Please enter a valid email address'));
                }
                if($datas['password'] != $datas['confirm_password']) {
                    throw new Exception($this->_("The entered password confirmation does not match the entered password."));
                }

                $admin->setData($datas)
                    ->setPassword($datas['password'])
                    ->save()
                ;

                $this->getSession()->setAdmin($admin);

                $html = array('success' => 1);

            } catch (Exception $e) {
                $html = array('message' => $e->getMessage());
                $this->getResponse()->setHttpResponseCode(400);
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

    }

}