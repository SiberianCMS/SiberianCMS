<?php

class Contact_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {
                $application = $this->getApplication();

                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving your contact informations.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                $html = '';
                $contact = new Contact_Model_Contact();
                $contact->find($option_value->getId(), 'value_id');

                if(!empty($datas['file'])) {
                    $relative_path = '/contact/cover/';
                    $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                    $file = Core_Model_Directory::getTmpDirectory(true).'/'.$datas['file'];

                    if(!is_dir($folder)) mkdir($folder, 0777, true);
                    if(!copy($file, $folder.$datas['file'])) {
                        throw new exception($this->_('An error occurred while saving your picture. Please try againg later.'));
                    } else {
                        $datas['cover'] = $relative_path.$datas['file'];
                    }
                }
                else if(!empty($datas['remove_cover'])) {
                    $datas['cover'] = null;
                }

                $contact->setData($datas)->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Informations successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'message_button' => 1,
                    'message_loader' => 1
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function cropAction() {

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