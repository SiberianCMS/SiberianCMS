<?php

class Socialgaming_Admin_GameController extends Admin_Controller_Default {

    public function indexAction() {
        $this->_forward('list');
    }

    public function listAction() {
        $this->loadPartials();
    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function editAction() {
        $this->loadPartials();
        $game = new Socialgaming_Model_Game();
        if ($id = $this->getRequest()->getParam('id')) {
            $game->find($id);
        }
        $this->getLayout()->getPartial('content')->setGame($game);
    }

    public function saveAction() {

        if ($datas = $this->getRequest()->getPost()) {

            $game = new Socialgaming_Model_Game();
            $current_game = new Socialgaming_Model_Game();
            $current_game->findCurrent($this->getSession()->getAdminId());

            $next_game = new Socialgaming_Model_Game();
            $next_game->findNext($this->getSession()->getAdminId());

            try {

                // Sauvegarde le jeu
                $datas['admin_id'] = $this->getSession()->getAdminId();
                if($current_game->getId()) {
                    $next_game->setData($datas)->save();

                    // Si le jeu n'a pas de date de fin
                    if(!$current_game->getEndAt()) {
                        // Met à jour la date de fin du jeu en cours
                        $current_game->setEndAt()->save();
                    }

                }
                else {
                    $current_game->setData($datas)->save();
                }

                $this->getSession()->addSuccess('Le jeu a été sauvegardé avec succès');
                $this->_redirect('socialgaming/admin_game/list');
            } catch (Exception $e) {
                $this->getSession()->addError('Une erreur est survenue lors de la sauvegarde');
                $this->_redirect($this->getRequest()->getHeader('referer'));
            }
        }

        return $this;
    }

    public function stopcurrentgameAction() {

        if ($datas = $this->getRequest()->isXmlHttpRequest()) {

            $game = new Socialgaming_Model_Game();
            $current_game = new Socialgaming_Model_Game();
            $current_game->findCurrent($this->getSession()->getAdminId());

            try {

                if(!$current_game->getId()) {
                    throw new Exception('Une erreur est survenue lors de la sauvegarde');
                }

                $message = '';

                // Met à jour la date de fin du jeu en cours
                if($current_game->getEndAt()) {
                    $current_game->setData('end_at', null);
                }
                else {
                    $current_game->setEndAt();
                    $message = 'Votre jeu se terminera le '.$current_game->getFormattedEndAt('dd/MM/yyyy');
                }

                $current_game->save();

                $html = array(
                    'success_message' => 'Sauvegarde effectuée avec succès.<br />'.$message,
                    'message_button' => 0,
                    'message_loader' => 0,
                    'message_timeout' => 3
                );

            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }

        return $this;
    }

    public function deleteAction() {

        if ($id = $this->getRequest()->getParam('id')) {
            $game = new Socialgaming_Model_Game();
            try {
                $game->find($id);
                if($game->getAdminId() == $this->getSession()->getAdminId()) {
                    $game->setIsDeleted(1)->save();
                    $this->getSession()->addSuccess('Le jeu a été supprimé avec succès');
                }
                else {
                    throw new Exception('Une erreur est survenue lors de la suppression du jeu');
                }
            } catch (Exception $e) {
                $this->getSession()->addError($e->getMessage());
            }

            $this->_redirect('socialgaming/admin_game/list');
        }

        return $this;
    }

}

?>
