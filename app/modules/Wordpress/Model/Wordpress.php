<?php

class Wordpress_Model_Wordpress extends Core_Model_Default {

    protected $_category_ids;
    protected $_remote_root_category;
    protected $_remote_category_ids;
    protected $_remote_posts;

    public function __construct($params = array()) {
        parent::__construct($params);
        $this->_db_table = 'Wordpress_Model_Db_Table_Wordpress';
        return $this;
    }

    public function getCategoryIds() {

        if(!$this->_category_ids) {
            $this->_category_ids = array();
            if($this->getId()) {
                $category = new Wordpress_Model_Wordpress_Category();
                $categories = $category->findAll(array('wp_id' => $this->getId()));
                foreach($categories as $category) $this->_category_ids[] = $category->getWpCategoryId();
            }
        }

        return $this->_category_ids;

    }

    public function getRemoteCategoryIds() {

        if(!$this->_remote_category_ids) {
            $root = $this->getRemoteRootCategory();
            $this->_remote_category_ids = $this->_parseCategoryIds($root);
        }

        return $this->_remote_category_ids;

    }

    public function getRemoteRootCategory($url = '', $ids = array()) {

        if(!$this->_remote_root_category) {

            // Instancie la catégorie parent
            $this->_remote_root_category = new Wordpress_Model_Wordpress_Category(array('id' => 0));

            if(empty($url)) $url = $this->getData('url');
            if(empty($url)) return $this->_remote_root_category;

            try {
                // Envoie la requête
                $datas = $this->_sendRequest($url, array('object' => 'categories'));
            }
            catch(Exception $e) {
                $datas = array('status' => -1);
            }

            // Test si les données sont OK
            if($datas['status'] == '1') {

                // Parse les catégories
                foreach($datas['categories'] as $datas) {
                    $category = $this->_parseCategories($datas);
                    $categories[] = $category;
                }
                if(!empty($categories)) {
                    $this->_remote_root_category->setChildren($categories);
                }
            }
        }

        return $this->_remote_root_category;
    }

    public function getRemotePosts($showAll = false, $url = '', $useCache = false) {


        $cache = Zend_Registry::get('cache');
        $cacheId = 'wordpress_cache_'.sha1($this->getId());

//        if(!$this->_remote_posts AND (!$useCache OR ($this->_remote_posts = $cache->load($cacheId)) === false)) {

            $this->_remote_posts = array();

            if($this->getData('url') OR !empty($url)) {

                $category_ids = $this->getCategoryIds();
                $params = array('object' => 'posts');
                if(!$showAll) $params['cat_ids'] = $category_ids;

                // Envoie la requête
                $datas = $this->_sendRequest(!empty($url) ? $url : $this->getData('url'), $params);

                // Test si les données sont OK
                if($datas['status'] == '1') {

                    foreach($datas['posts'] as $post_datas) {

                        $first_picture = '';
                        $first_picture_src = '';

                        if(!empty($post_datas['description'])) {
                            $content = new Dom_SmartDOMDocument();
                            $content->loadHTML($post_datas['description']);
                            $content->encoding = 'utf-8';
    //                            $content->removeChild($content->firstChild);
    //                            $content->replaceChild($content->firstChild->firstChild, $content->firstChild);
                            $description = $content->documentElement;

                            // Traitement des images
                            $imgs = $description->getElementsByTagName('img');

                            if($imgs->length > 0) {

                                foreach($imgs as $img) {

                                    if($img->getAttribute('src')) {
                                        if(empty($first_picture)) {
                                            $first_picture = $img;
                                            $first_picture_src = $src = $this->getUrl('Front/image/crop', array(
                                                'image' => base64_encode($img->getAttribute('src')),
                                                'width' => 640,
                                                'height' => 400
                                            ));
                                        }
                                        else {
                                            $img->setAttribute('onload', 'javascript:setImageSize($(this), true);');
                                            $img->setAttribute('src', $this->getUrl('Front/image/crop', array(
                                                'image' => base64_encode($img->getAttribute('src')),
                                                'width' => 240,
                                                'height' => 180
                                            )));
                                        }
                                    }
                                }

                                if(!empty($first_picture)) {
                                    $first_picture->parentNode->removeChild($first_picture);
                                }

                            }

                            // Traitement des iframes
                            $iframes = $description->getElementsByTagName('iframe');
                            if($iframes->length > 0) {
                                foreach($iframes as $iframe) {
                                    $iframe->setAttribute('width', '100%');
                                    $iframe->removeAttribute('height');
//                                    if($iframe->getAttribute('width')) {}
                                }
                            }

                            $post_datas['description'] = $content->saveHTMLExact();
                            $post_datas['description'] = strip_tags($post_datas['description'], '<div><p><a><img><iframe>');
                        }
                        $post_datas['picture'] = $first_picture_src;

                        $this->_remote_posts[$post_datas['date']] = new Wordpress_Model_Wordpress_Category_Post($post_datas);
                    }

                }

                krsort($this->_remote_posts);
                if($useCache) {
                    $cache->save($this->_remote_posts, $cacheId);
                }
            }
//        }

        return array_splice($this->_remote_posts, 0, 20);

    }

    public function checkModule($url = '') {

        if(!$url) $url = $this->getData('url');
        if(!$url) return false;
        $isOK = true;

        try {

            // Récupère le contenu du site Wordpress
            $client = new Zend_Http_Client($url.'?app-creator-api&object=categories', array(
                'adapter'   => 'Zend_Http_Client_Adapter_Curl',
//                'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
            ));
            $response = $client->request();

            // Test s'il y a une réponse et si le module est installé
            if(!$response OR $response->getStatus() == 404) {
                throw new Exception('');
            }

            // Parse les données JSON
            $datas = Zend_Json::decode($response->getBody());
            // Test si les données sont OK
            if(!is_array($datas) OR empty($datas['status']) OR empty($datas['categories'])) {
                throw new Exception('');
            }
        }
        catch(Exception $e) {
            $isOK = false;
        }

        return $isOK;
    }

    protected function _parseCategories($datas) {

        $category = new Wordpress_Model_Wordpress_Category();
        $is_selected = in_array($datas['id'], $this->getCategoryIds());
        $picture = "";

        // Gestion des enfants
        if(!empty($datas['children'])) {
            $children = array();
            foreach($datas['children'] as $child_datas) {
                $child = $this->_parseCategories($child_datas);
                $children[] = $child;
            }
            current($children)->setIsFirst(true);
            end($children);
            current($children)->setIsLast(true);
            reset($children);
            $category->setChildren($children);

            $datas['is_last_level'] = false;
            unset($datas['children']);
        }
        else {
            $datas['is_last_level'] = true;
        }

        if(!empty($datas['post_ids'])) {
            $category->setPostIds($datas['post_ids']);
            unset($datas['post_ids']);
        }

        $datas['is_selected'] = $is_selected;
        $datas['picture'] = $picture;
        $category->addData($datas);

        return $category;

    }

    protected function _parseCategoryIds($category, $category_ids = array()) {

        $category_ids[] = $category->getId();
        if($category->getChildren()) {
            foreach($category->getChildren() as $child) {
                $category_ids = $this->_parseCategoryIds($child, $category_ids);
            }
        }

        return $category_ids;

    }


    protected function _sendRequest($url, $params = array()) {

        try {
            $params['app-creator-api'] = '1';
            if(!empty($params)) {
                $query = http_build_query($params);
                $url.='?'.$query;
            }

            // Récupère le contenu du site Wordpress
            $client = new Zend_Http_Client($url, array(
                'adapter'   => 'Zend_Http_Client_Adapter_Curl',
//                'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
            ));
            $response = $client->request();
        }
        catch(Exception $e) {
            $response = null;
        }

        if(!$response) {
            throw new Exception($this->_('An error occurred while accessing your Wordpress website. Please, verify the domain name %s', $url));
        }
        // Test si le module est installé
        if($response->getStatus() == 404) {
            throw new Exception($this->_("We are sorry but our Wordpress plugin hasn't been detected on your website. Please be sure it is correctly installed and activated."));
        }


        try {
            // Parse les données JSON
            $datas = Zend_Json::decode($response->getBody());
        }
        catch(Exception $e) {
            $datas = array();
        }

        // Test si les données sont OK
//        Zend_Debug::dump(empty($datas['status']));
//        Zend_Debug::dump(!is_array($datas) OR empty($datas['status']) OR (empty($datas['categories']) AND empty($datas['posts'])));
//        die;
        if(!is_array($datas) OR empty($datas['status']) OR (empty($datas['categories']) AND empty($datas['posts']))) {
            throw new Exception($this->_("We are sorry but no category has been detected on your Wordpress website."));
        }

        return $datas;

    }

}