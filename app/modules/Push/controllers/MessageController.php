<?php

class Push_MessageController extends Core_Controller_Default
{
    /**
     * Fetch Messages
     *
     * This gets called by a cron job that runs as often as you want. You might want to set it for every minute.
     *
     * @access public
     */
    public function sendAction() {

        $message = new Push_Model_Message();
        $now = Zend_Date::now()->toString('y-MM-dd HH:mm:ss');
        $errors = array();
        $messages = $message->findAll(array('status IN (?)' => array('queued'), 'send_at IS NULL OR send_at <= ?' => $now, 'send_until IS NULL OR send_until >= ?' => $now), 'created_at DESC');

        if($messages->count() > 0) {
            foreach($messages as $message) {
                try {
                    // Envoi et sauvegarde du message
                    $message->updateStatus('sending');
                    $message->push();
                    $message->updateStatus('delivered');
                    if($message->getErrors()) {
                        $errors[$message->getId()] = $message->getErrors();
                    }
                }
                catch(Exception $e) {
                    $message->updateStatus('failed');
                    $errors[$message->getId()] = $e;
                }
            }
        }

        Zend_Debug::dump('Erreurs :');
        Zend_Debug::dump($errors);

        die('done');
    }

}