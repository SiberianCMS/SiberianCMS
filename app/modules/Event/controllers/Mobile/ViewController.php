<?php

class Event_Mobile_ViewController extends Application_Controller_Mobile_Default {

    public function findAction() {

        if($value_id = $this->getRequest()->getParam('value_id')) {

            $event_id = $this->getRequest()->getParam('event_id');

            if(is_null($event_id)) {
                return $this;
            }

            try {

                $option = $this->getCurrentOptionValue();

                $start_at = new Zend_Date();
                $end_at = new Zend_Date();
                $format = 'y-MM-dd HH:mm:ss';
                $events = $option->getObject()->getEvents();

                if(!empty($events[$event_id])) {

                    $event = $events[$event_id];
                    $data = array('event' => array());

                    $start_at->set($event->getStartAt(), $format);
                    $end_at->set($event->getEndAt(), $format);
                    $formatted_start_at = $start_at->toString($this->_("MM/dd/y hh:mm a"));
                    $formatted_end_at = $end_at->toString($this->_("MM/dd/y hh:mm a"));

                    $data['event'] = array(
                        "id" => $event_id,
                        "description" => $event->getDescription(),
                        "location" => $event->getLocation(),
                        "rsvp" => $event->getRsvp(),
                        "start_at" => $formatted_start_at,
                        "end_at" => $formatted_end_at,
                        "url" => $this->getPath("event/mobile_view/index", array('value_id' => $option->getId(), "event_id" => $event->getId()))
                    );

                    $data["cover"] = array(
                        "title" => $event->getName(),
                        "subtitle" => "$formatted_start_at - $formatted_end_at",
                        "url" => $event->getPicture()
                    );

                    $data['page_title'] = $event->getName();

                } else {
                    throw new Exception("Unable to find this event.");
                }

            }
            catch(Exception $e) {
                $data = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($data);

        }

    }

}