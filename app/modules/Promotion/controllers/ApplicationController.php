<?php

class Promotion_ApplicationController extends Application_Controller_Default
{

    public function formAction() {
        if($this->getRequest()->getParam("id")) {
            $id = $this->getRequest()->getParam("id");
        }
        if($this->getRequest()->getParam("option_value_id")) {
            $value_id = $this->getRequest()->getParam("option_value_id");
        }
        try {
            $promotion = new Promotion_Model_Promotion();
            if(isset($id)) {
                $promotion->find($id);
            }
            $this->getLayout()->setBaseRender('form', 'promotion/application/edit/form.phtml', 'admin_view_default')->setPromotion($promotion)->setValue($value_id);
            $html = array(
                'form' => $this->getLayout()->render(),
                'success' => 1
            );
        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage()
            );
        }
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {
                // Test s'il y a un value_id
                if(empty($datas['value_id'])) throw new Exception($this->_('An error occurred while saving. Please try again later.'));

                // Récupère l'option_value en cours
                $option_value = new Application_Model_Option_Value();
                $option_value->find($datas['value_id']);

                // Instancie une nouvelle promotion
                $promotion = new Promotion_Model_Promotion();

                // Test si l'option des réductions spéciales est activée et payée
                if($id = $this->getRequest()->getParam('id')) {
                    $promotion->find($id);
                    if($promotion->getValueId() AND $promotion->getValueId() != $option_value->getId()) {
                        throw new Exception('An error occurred while saving. Please try again later.');
                    }
                }
                //Vérifs champs
                if(empty($datas['title']) || empty($datas['description']) || empty($datas['title'])) {
                    throw new Exception($this->_('An error occurred while saving your discount. Please fill in all fields'));
                    die;
                }
                if(!isset($datas['is_illimited']) && empty($datas['end_at']))
                {
                    throw new Exception($this->_('An error occurred while saving your discount. Please fill in all fields'));
                    die;
                }
                if(!empty($datas['end_at']))
                {
                    $date_actuelle = new Zend_Date();
                    $date_modif = new Zend_Date($datas['end_at'], 'y-MM-dd');
                    if($date_modif < $date_actuelle) {
                        throw new Exception($this->_('Please select an end date greater than the current date.'));
                        die;
                    }
                }

                if(!empty($datas['is_illimited']))
                {
                    $datas['end_at'] = null;
                }

                $datas['force_validation'] = !empty($datas['force_validation']);
                $datas['is_unique'] = !empty($datas['is_unique']);
                $datas['owner'] = 1;

                $promotion->setData($datas);

                if(isset($datas['available_for']) AND $datas['available_for'] == 'all') $promotion->resetConditions();

                $promotion->save();
                $html = array(
                    'promotion_id' => $promotion->getId(),
                    'success_message' => $this->_('Discount successfully saved'),
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );
            }
            catch(Exception $e) {
                $html = array(
                    'message' => $e->getMessage(),
                    'url' => '/promotion/admin/list'
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));

        }

    }

    public function deletepostAction() {
        $id = $this->getRequest()->getParam("id");
        $html = '';
        try {
            $promotion = new Promotion_Model_Promotion();
            $promotion->find($id)->delete();
            $html = array(
                'promotion_id' => $id,
                'success' => 1,
                'success_message' => $this->_('Discount successfully deleted'),
                'message_timeout' => 2,
                'message_button' => 0,
                'message_loader' => 0
            );
        } catch (Exception $e) {
            $html = array(
                'message' => $e->getMessage(),
                'url' => '/promotion/admin/list'
            );
        }

        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }
}