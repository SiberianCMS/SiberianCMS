<?php

class Customer_Mobile_Account_LoginController extends Application_Controller_Mobile_Default
{

    public function indexAction() {
        if($this->getSession()->isLoggedIn()) {
            $this->_redirect("customer/mobile_account_edit");
        } else {
            parent::indexAction();
        }
    }

    public function postAction() {

        if($datas = Zend_Json::decode($this->getRequest()->getRawBody())) {

            try {

                if((empty($datas['email']) OR empty($datas['password']))) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }

                $customer = new Customer_Model_Customer();
                $customer->find($datas['email'], 'email');
                $password = $datas['password'];

                if(!$customer->authenticate($password)) {
                    throw new Exception($this->_('Authentication failed. Please check your email and/or your password'));
                }

                $this->getSession()
                    ->resetInstance()
                    ->setCustomer($customer)
                ;

                $html = array('success' => 1, 'customer_id' => $customer->getId());

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);
        }

    }

    public function loginwithfacebookAction() {

        if($access_token = $this->getRequest()->getParam('token')) {

            try {

                // Réinitialise la connexion
                $this->getSession()->resetInstance();

                // Récupération des données du compte Facebook
                $graph_url = "https://graph.facebook.com/me?access_token=".$access_token;
                $user = json_decode(file_get_contents($graph_url));

                if(!$user instanceof stdClass OR !$user->id) {
                    throw new Exception($this->_('An error occurred while connecting to your Facebook account. Please try again later'));
                }
                // Récupère le user_id
                $user_id = $user->id;

                // Charge le client à partir du user_id
                $customer = new Customer_Model_Customer();
                $customer->findBySocialId($user_id, 'facebook');

                // Si le client n'a pas de compte
                if(!$customer->getId()) {

                    // Charge le client à partir de l'adresse email afin d'associer les 2 comptes ensemble
                    if($user->email) {
                        $customer->find(array('email' => $user->email));
                    }

                    // Si l'email n'existe pas en base, on crée le client
                    if(!$customer->getId()) {
                        // Préparation des données du client
                        $customer->setData(array(
                            'civility' => $user->gender == 'male' ? 'm' : 'mme',
                            'firstname' => $user->first_name,
                            'lastname' => $user->last_name,
                            'email' => $user->email
                        ));

                        // Ajoute un mot de passe par défaut
                        $customer->setPassword(uniqid());

                        // Récupèration de l'image de Facebook
                        $social_image = @file_get_contents("http://graph.facebook.com/$user_id/picture?type=large");
                        if($social_image) {

                            $formated_name = Core_Model_Lib_String::format($customer->getName(), true);
                            $image_path = $customer->getBaseImagePath().'/'.$formated_name;

                            // Créer le dossier du client s'il n'existe pas
                            if(!is_dir($customer->getBaseImagePath())) { mkdir($image_path, 0777); }

                            // Créer l'image sur le serveur

                            $image_name = uniqid().'.jpg';
                            $image = fopen($image_path.'/'.$image_name, 'w');

                            fputs($image, $social_image);
                            fclose($image);

                            // Redimensionne l'image
                            Thumbnailer_CreateThumb::createThumbnail($image_path.'/'.$image_name, $image_path.'/'.$image_name, 150, 150, 'jpg', true);

                            // Affecte l'image au client
                            $customer->setImage('/'.$formated_name.'/'.$image_name);
                        }
                    }
                }

                // Affecte les données du réseau social au client
                $customer->setSocialData('facebook', array('id' => $user_id, 'datas' => $access_token));

                // Sauvegarde du nouveau client
                $customer->save();

                // Connexion du client
                $this->getSession()->setCustomer($customer);

                $html = array('success' => 1, 'customer_id' => $customer->getId());

            }
            catch(Exception $e) {
                $html = array('error' => 1, 'message' => $e->getMessage());
            }

            $this->_sendHtml($html);

        }

    }

    public function logoutAction() {

        $this->getSession()->resetInstance();

        $html = array('success' => 1);

        $this->getLayout()->setHtml(Zend_Json::encode($html));

    }
}
