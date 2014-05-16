<?php

class Push_ApplicationController extends Application_Controller_Default
{

    public function editpostAction() {

        if($datas = $this->getRequest()->getPost()) {

            $html = '';

            try {

                $message = new Push_Model_Message();
                $sendNow = false;
                $inputs = array('send_at', 'send_until');

                foreach($inputs as $input) {
                    if(empty($datas[$input.'_a_specific_datetime'])) {
                        $datas[$input] = null;
                    }
                    else if(empty($datas[$input])) {
                        throw new Exception($this->_('Please, enter a valid date'));
                    }
                    else {
                        $date = new Zend_Date($datas[$input]);
                        $datas[$input] = $date->toString('y-MM-dd HH:mm:ss');
                    }
                }

                if(empty($datas['send_at'])) {
                    $sendNow = true;
                    $datas['send_at'] = Zend_Date::now()->toString('y-MM-dd HH:mm:ss');
                }

                if(!empty($datas['send_until']) AND $datas['send_at'] > $datas['send_until']) {
                    throw new Exception($this->_("The duration limit must be higher than the sent date"));
                }

                $message->setData($datas)->save();

                if($sendNow) {
                    $cmd = 'wget "'.$this->getUrl('push/message/send').'"> /dev/null 2>/dev/null &';
                    shell_exec($cmd);
                }

                $html = array(
                    'success' => 1,
                    'message_timeout' => 2,
                    'message_button' => 0,
                    'message_loader' => 0
                );

                if($sendNow) $html['success_message'] = $this->_('Your message has been saved successfully and will be sent in a few minutes');
                else $html['success_message'] = $this->_('Your message has been saved successfully and will be sent at the entered date');

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

}