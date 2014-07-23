<?php

class Event_Model_Event extends Core_Model_Default {

    const MAX_RESULTS = 5;

    protected $_list = array();
    protected $_tmp_list = array();

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Event_Model_Db_Table_Event';
        return $this;
    }

    public function getEvents($offset = 0) {

        $cache = Zend_Registry::get('cache');
        $id = $this->getCacheId();
        if(($this->_list = $cache->load($id)) === false) {
            $events = $this->findAll(array('value_id' => $this->getValueId()));
            $this->_list = array();
            foreach ($events as $event) {
                if($event->getEventType() == 'ical') {
                    $this->_parseIcalAgenda($event->getData('url'));
                }elseif($event->getEventType() == 'fb'){
                    $this->_parseFBAgenda($event->getData('url'));
                }else{
                    $this->_parseCustomAgenda($event->getId());
                }
            }
            usort($this->_tmp_list, array($this, '_sortByDate'));
            $this->_list = array();
            foreach($this->_tmp_list as $event) {
                $this->_list[] = new Core_Model_Default($event);
            }
            $cache->save($this->_list, $id);

        }

        return array_slice($this->_list, $offset, self::MAX_RESULTS, true);
    }

    public function getCacheId() {
        return 'AGENDA_OVI_' . sha1($this->getValueId() . Core_Model_Language::getCurrentLanguage());
    }

    protected  function _parseIcalAgenda($url) {

        $content = @file_get_contents($url);
        if(!$content) return $this;

        $ical = new Ical_Reader($content);
        foreach ($ical->events() as $key => $event){
            if(strtotime($event['DTSTART']) > strtotime(date("Y-m-d H:i:s", time()))){
                $created_at = null;
                if(!empty($event['CREATED'])) {
                    $timestamp = $ical->iCalDateToUnixTimestamp($event['CREATED']);
                    $created_at = new Zend_Date($timestamp);
                    $created_at = $created_at->toString('y-MM-dd HH:mm:ss');
                }
//                $updated_at = !empty($event['LAST-MODIFIED']) ? date_create($event['LAST-MODIFIED'])->format('Y-m-d H:i:s') : null;
                $this->_tmp_list[] = array(
                    "id"            => $key,
                    "name"          => $event['SUMMARY'],
                    "start_at"      => date_create($event['DTSTART'])->format('Y-m-d H:i:s'),
                    "end_at"        => date_create($event['DTEND'])->format('Y-m-d H:i:s'),
                    "description"   => preg_replace('/\v+|\\\[rn]/','<br/>', $event['DESCRIPTION']),
                    "location"      => isset($event['LOCATION']) ? $event['LOCATION'] : '',
                    "rsvp"          => '',
                    "picture"       => $this->_getNoImage(),
                    "created_at"    => $created_at,
                    "updated_at"    => null
//                    "updated_at"    => $updated_at
                );
            }
        }

        return $this;
    }

    protected  function _parseFBAgenda($username){

        $app_id         = Core_Model_Lib_Facebook::getAppId();
        $app_secret     = Core_Model_Lib_Facebook::getSecretKey();

        $url = 'https://graph.facebook.com/oauth/access_token';
        $url .= '?grant_type=client_credentials';
        $url .= "&client_id=$app_id";
        $url .= "&client_secret=$app_secret";

        $access_token = str_replace('access_token=','',file_get_contents($url));

        $url = "https://graph.facebook.com/$username/events?access_token=$access_token";
        $response = @file_get_contents($url);

        if(!$response) return $this;

        $events = Zend_Json::decode($response);

        if (!empty($events) && !empty($events['data'])){
            foreach ($events['data'] as $key => $event){
                $event_datas = @file_get_contents("https://graph.facebook.com/{$event['id']}?access_token=$access_token");
                if(!$event_datas) continue;
                $description = '';
                if(!$event_datas) continue;

                $event_datas = Zend_Json::decode($event_datas);
                $updated_at = date_create($event_datas['updated_time'])->format('Y-m-d H:i:s');

                if(!empty($event_datas['venue'])) {
                    $address = $event_datas['venue']['street'] . ', ' . $event_datas['venue']['zip'] . ', ' . $event_datas['venue']['city'];
                    if(!empty($event_datas['venue']['state'])) $address .= ', ' . $event_datas['venue']['state'];
                    if(!empty($event_datas['venue']['country'])) $address .= ', ' . $event_datas['venue']['country'];
                }

                $start_at = null;
                if(!empty($event['start_time'])) {
                    $start_at = new Zend_Date($event['start_time'], Zend_Date::ISO_8601);
                    $start_at = $start_at->toString('y-MM-dd HH:mm:ss');
                }

//                isset($event['start_time']) ? $start_at = strtotime($event['start_time']) : $start_at = strtotime(date("Y-m-d H:i:s", time()));
                $this->_tmp_list[] = array(
                    "id"            => $key,
                    "name"          => $event['name'],
                    "start_at"      => $start_at,
                    "end_at"        => date_create(isset($event['end_time']) ? $event['end_time'] : "")->format('Y-m-d H:i:s'),
                    "description"   => !empty($event_datas['description']) ? $event_datas['description'] : null,
                    "location"      => $address,
                    "rsvp"          => '',
                    "picture"       => 'https://graph.facebook.com/'.$event['id'].'/picture?type=large',
                    "created_at"    => null,
                    "updated_at"    => $updated_at

                );
//                }
            }
        }

        return $this;

    }


    protected function _parseCustomAgenda($custom_agenda_id){
        $event = new Event_Model_Event_Custom();
        $custom_events = $event->findAll(array('agenda_id'=> $custom_agenda_id));
        foreach ($custom_events as $custom_event) {
            if(strtotime($custom_event->getEndAt()) > strtotime(date("Y-m-d H:i:s", time()))) {
                $image = $custom_event->getPictureUrl();
                if(!$image) {
                    $image = $this->_getNoImage();;
                }
                $custom_event->setPicture($image);
                $this->_tmp_list[] = $custom_event->getData();
            }
        }

    }

    protected function _getNoImage() {
        return Application_Model_Application::getImagePath().'/placeholder/no-image-event.png';
    }

    protected function _sortByDate($a, $b) {
        return $a['start_at'] > $b['start_at'];
    }

    protected function msort($array, $key, $sort_flags = SORT_REGULAR) {
        if (is_array($array) && count($array) > 0) {
            if (!empty($key)) {
                $mapping = array();
                foreach ($array as $k => $v) {
                    $sort_key = '';
                    if (!is_array($key)) {
                        $sort_key = $v[$key];
                    } else {
                        // @TODO This should be fixed, now it will be sorted as string
                        foreach ($key as $key_key) {
                            $sort_key .= $v[$key_key];
                        }
                        $sort_flags = SORT_STRING;
                    }
                    $mapping[$k] = $sort_key;
                }
                asort($mapping, $sort_flags);
                $sorted = array();
                foreach ($mapping as $k => $v) {
                    $sorted[] = $array[$k];
                }
                return $sorted;
            }
        }
        return $array;
    }

}
