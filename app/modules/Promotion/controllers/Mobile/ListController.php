<?php

class Promotion_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($this->getRequest()->getParam('value_id')) {

            $option = $this->getCurrentOptionValue();
            $promotion_customer = new Promotion_Model_Customer();
            $promotion_customers = $promotion_customer->findAllByValue($option->getId(), $this->getSession()->getCustomerId() | 0);

            $data = array("promotions" => array());

            if($promotion_customers->count() == 0) {
                $data['promotions'][] = array();
            }

            foreach($promotion_customers as $promotion_customer) {
                $data['promotions'][] = array(
                    "id" => $promotion_customer->getPromotionId(),
                    "title" => $promotion_customer->getTitle(),
                    "description" => $promotion_customer->getDescription(),
                    "conditions" => $promotion_customer->getConditions(),
                    "is_unique" => $promotion_customer->getIsUnique(),
                    "end_at" => $promotion_customer->getFormattedEndAt($this->_('MMMM dd y'))
                );
            }

            $data['page_title'] = $option->getTabbarName();

            $this->_sendHtml($data);

        }
    }


    public function useAction() {

        try {
            $customer_id = $this->getSession()->getCustomerId();
            if(!$customer_id) throw new Exception($this->_('You must be logged in to use a discount'));
            $html = array();

            if($data = Zend_Json::decode($this->getRequest()->getRawBody())) {

                if(empty($data['promotion_id'])) {
                    throw new Exception($this->_("An error occurred while saving. Please try again later."));
                }

                $promotion_id = $data['promotion_id'];

                $promotion = new Promotion_Model_Promotion();
                $promotion->find($promotion_id);

                $promotion_customer = new Promotion_Model_Customer();
                $promotion_customer->findLast($promotion_id, $customer_id);

                if(!$promotion_customer->getId()) {
                    $promotion_customer->setPromotionId($promotion_id)
                        ->setCustomerId($customer_id)
                    ;
                }

                if($promotion->getIsUnique() AND $promotion_customer->getId() AND $promotion_customer->getIsUsed()) {
                    $html['remove'] = true;
                    throw new Exception($this->_('You have already use this discount'));
                }
                else {
                    $promotion_customer->setIsUsed(1)->save();
                    $html = array(
                        "success" => 1,
                        "message" => $this->_("This discount is now used"),
                        "remove" => 1
                    );
                }

            }
        }
        catch(Exception $e) {
            $html['error'] = 1;
            $html['message'] = $e->getMessage();
        }

        $this->_sendHtml($html);
    }

}