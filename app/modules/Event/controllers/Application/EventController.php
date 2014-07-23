<?php

class Event_Application_EventController extends  Application_Controller_Default{

    public function editAction() {
        if($datas = $this->getRequest()->getPost()) {

            try {

                $application = $this->getApplication();

                // Test s'il y a un value_id
                if(empty($datas['agenda_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                $event = new Event_Model_Event_Custom();
                $option_value = $this->getCurrentOptionValue();
                $data = array();

                if(!empty($datas['id'])) {
                    $event->find($datas['id']);
                    if($event->getAgendaId() != $datas['agenda_id']) throw new Exception($this->_('An error occurred while saving. Please try again later.'));
                }

                if(!empty($datas['picture'])) {
                    if(substr($datas['picture'],0,1) == '/') {
                        unset($datas['picture']);
                    } else {
                        $application = $this->getApplication();
                        $illus_relative_path = '/feature/event/cover/';
                        $folder = Application_Model_Application::getBaseImagePath().$illus_relative_path;
                        $file = Core_Model_Directory::getTmpDirectory(true).'/'.$datas['picture'];
                        if (!is_dir($folder))
                            mkdir($folder, 0777, true);
                        if(!copy($file, $folder.$datas['picture'])) {
                            throw new exception($this->_("An error occurred while saving your picture. Please try againg later."));
                        } else {
                            $datas['picture'] = $illus_relative_path.$datas['picture'];
                        }
                    }
                }
                else {
                    $datas['picture'] = null;
                }

                if(!empty($datas['rsvp']) AND stripos($datas['rsvp'], 'http') === false) {
                    $datas['rsvp'] = 'http://'.$datas['rsvp'];
                }

                $event->addData($datas)->save();

                $cache = Zend_Registry::get('cache');
                $cache->remove($option_value->getObject()->getCacheId());

                $html = array(
                    'success' => '1',
                    'success_message' => $this->_("Event successfully saved"),
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

    public function formAction() {

        if(!$this->getRequest()->getParam('agenda_id')) {
            throw new Exception($this->_("An error occurred while loading this event"));
        }

        $event = new Event_Model_Event_Custom();
        if($id = $this->getRequest()->getParam('event_id')){
            $event->find($id);
        }
        $html = $this->getLayout()->addPartial('event_custom', 'admin_view_default', 'event/application/edit/custom/edit/event.phtml')
                            ->setEvent($event)
                            ->setOptionValue($this->getCurrentOptionValue())
                            ->setAgendaId($this->getRequest()->getParam('agenda_id'))
                            ->toHtml();

        $this->getLayout()->setHtml($html);
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

    public function deleteAction() {

        if(!$this->getRequest()->getParam('id')) {
            throw new Exception($this->_("An error occurred while loading this event"));
        }

        $id = $this->getRequest()->getParam("id");
        $html = '';

        try {

            $event = new Event_Model_Event_Custom();
            $event->find($id);

            if($event->getAgenda()->getValueId() != $this->getCurrentOptionValue()->getId()) {
                throw new Exception($this->_("An error occurred while deleting the event"));
            }

            $event->delete();

            $html = array(
                'event_id' => $id,
                'success' => 1,
                'success_message' => $this->_('Event successfully deleted'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
            $cache = Zend_Registry::get('cache');
            $cache->remove('AGENDA_OVI_'.sha1($this->getCurrentOptionValue()->getId()));

        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage(),
                'url' => '/event/admin/list'
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }
}