<?php

class Event_Mobile_ListController extends Application_Controller_Mobile_Default {

    public function findallAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {
            try {

                $option = $this->getCurrentOptionValue();

                $start_at = new Zend_Date();
                $end_at = new Zend_Date();
                $format = 'y-MM-dd HH:mm:ss';
                $events = $option->getObject()->getEvents();
                $data = array('events' => array());
                foreach($events as $key => $event) {
                    $start_at->set($event->getStartAt(), $format);
                    $end_at->set($event->getEndAt(), $format);
                    $formatted_start_at = $start_at->toString($this->_("MM/dd/y hh:mm a"));
                    $formatted_end_at = $end_at->toString($this->_("MM/dd/y hh:mm a"));

                    $data['events'][] = array(
                        "id" => $key,
                        "title" => $event->getName(),
                        "name" => $event->getName(),
                        "subtitle" => "$formatted_start_at - $formatted_end_at",
                        "description" => $event->getDescription(),
                        "month_name_short" => $start_at->toString(Zend_Date::MONTH_NAME_SHORT),
                        "day" => $start_at->toString('dd'),
                        "start_at" => $formatted_start_at,
                        "end_at" => $formatted_end_at,
                        "url" => $this->getPath("event/mobile_view/index", array('value_id' => $option->getId(), "event_id" => $key))
                    );
                }

                $data['page_title'] = $option->getTabbarName();

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}