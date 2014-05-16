<?php

class Push_CertificatController extends Core_Controller_Default {

    public function listAction() {
        $this->loadPartials();
    }

    public function saveAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                if(empty($datas['type'])) {
                    throw new Exception('An error occurred during process. Please try again later.');
                }
                if(empty($datas['path'])) $datas['path'] = null;

                $certificat = new Push_Model_Certificat();
                $certificat->find($datas['type'], 'type');
                if(!$certificat->getId()) {
                    $certificat->setType($datas['type']);
                }
                $certificat->setPath($datas['path'])->save();

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_('Infos successfully saved'),
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

    public function uploadAction() {

        if (!empty($_FILES)) {

            try {

                $path = '/var/apps/iphone/';
                $base_path = Core_Model_Directory::getBasePathTo($path);
                $filename = uniqid().'.pem';

                if(!is_dir($base_path)) mkdir($base_path, 0775, true);

                $adapter = new Zend_File_Transfer_Adapter_Http();
                $adapter->setDestination($base_path);

                $adapter->setValidators(array('Extension' => array('pem', 'case' => false)));
                $adapter->getValidator('Extension')->setMessages(array(
                    'fileExtensionFalse' => $this->_("Extension not allowed, \'%s\' only", '%extension%')
                ));

                $files = $adapter->getFileInfo();

                foreach ($files as $file => $info) {

                    if (!$adapter->isUploaded($file)) {
                        throw new Exception($this->_('An error occurred during process. Please try again later.'));
                    } else if (!$adapter->isValid($file)) {
                        if(count($adapter->getMessages()) == 1) {
                            $erreur_message = $this->_('Error : <br/>');
                        } else {
                            $erreur_message = $this->_('Errors : <br/>');
                        }
                        foreach($adapter->getMessages() as $message) {
                            $erreur_message .= '- '.$message.'<br/>';
                        }
                        throw new Exception($erreur_message);
                    } else {

                        $adapter->addFilter(new Zend_Filter_File_Rename(array(
                            'target' => $base_path . $filename,
                            'overwrite' => true
                        )));

                        $adapter->receive($file);

                    }
                }

                $certificat = new Push_Model_Certificat();
                $certificat->find('ios', 'type');
                if(!$certificat->getId()) {
                    $certificat->setType('ios');
                }
                $certificat->setPath($path.$filename)->save();

                $datas = array(
                    'success' => 1,
                    'files' => 'eeeee',
                    'message_success' => $this->_('Infos successfully saved'),
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