<?php

class Customer_Mobile_Account_RegisterController extends Application_Controller_Mobile_Default
{

    public function postAction() {

        if($datas = Zend_Json::decode($this->getRequest()->getRawBody())) {


            $customer = new Customer_Model_Customer();

            try {

                if(!Zend_Validate::is($datas['email'], 'EmailAddress')) {
                    throw new Exception($this->_('Please enter a valid email address'));
                }

                $dummy = new Customer_Model_Customer();
                $dummy->find($datas['email'], 'email');

                if($dummy->getId()) {
                    throw new Exception($this->_('We are sorry but this address is already used.'));
                }

                if(empty($datas['show_in_social_gaming'])) {
                    $datas['show_in_social_gaming'] = 0;
                }

                if(empty($datas['password'])) {
                    throw new Exception($this->_('Please enter a password'));
                }

                $customer->setData($datas)
                    ->setPassword($datas['password'])
                    ->save()
                ;

                $this->getSession()->setCustomer($customer);

                $this->_sendNewAccountEmail($customer, $datas['password']);

                $html = array('success' => 1, 'customer_id' => $customer->getId());

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

    protected function _sendNewAccountEmail($customer, $password) {

        $admin_email = null;
        $contact = new Contact_Model_Contact();
        $contact_page = $this->getApplication()->getPage('contact');
        $sender = 'no-reply@'.Core_Model_Lib_String::format($this->getApplication()->getName(), true).'.com';

        if($contact_page->getId()) {
            $contact->find($contact_page->getId(), 'value_id');
            $admin_email = $contact->getEmail();
        }

        $layout = $this->getLayout()->loadEmail('customer', 'create_account');
        $layout->getPartial('content_email')->setCustomer($customer)->setPassword($password)->setAdminEmail($admin_email)->setApp($this->getApplication()->getName());
        $content = $layout->render();

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($content);
        $mail->setFrom($sender, $this->getApplication()->getName());
        $mail->addTo($customer->getEmail(), $customer->getName());
        $mail->setSubject($this->_('%s – Account creation', $this->getApplication()->getName()));
        $mail->send();

        return $this;

    }

}
