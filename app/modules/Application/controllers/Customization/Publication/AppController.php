<?php

class Application_Customization_Publication_AppController extends Application_Controller_Default {

    protected $_startup_path;
    protected $_startup_relative_path;
    protected $_startup_sizes = array(
        "iphone5" => array(
            "bg" => array(640, 1096),
            "bg_icon" => array(260, 260),
            "icon" => array(140, 140),
        ),
        "iphone4" => array(
            "bg" => array(640, 920),
            "bg_icon" => array(260, 260),
            "icon" => array(140, 140),
        ),
        "android" => array(
            "bg" => array(320, 460),
            "bg_icon" => array(130, 130),
            "icon" => array(70, 70),
        ),
    );

    public function preDispatch() {
        parent::preDispatch();
        $admin = $this->getSession()->getAdmin();
        $this->_startup_relative_path = '/startup_image/';
        $this->_screenshots_relative_path = '/screenshots/temp/';
        $this->_startup_path = Application_Model_Application::getBaseImagePath().$this->_startup_relative_path;
        $this->_screenshots_path = Application_Model_Application::getBaseImagePath().$this->_screenshots_relative_path;
    }

    public function indexAction() {
        $this->loadPartials();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $html = array('html' => $this->getLayout()->getPartial('content_editor')->toHtml());
            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function iconAction() {
        $this->getLayout()->setBaseRender('content', 'application/customization/publication/app/icon.phtml', 'admin_view_default');
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function startupAction() {
        $this->getLayout()->setBaseRender('content', 'application/customization/publication/app/startup.phtml', 'admin_view_default');
        $html = array('html' => $this->getLayout()->render());
        $this->getLayout()->setHtml(Zend_Json::encode($html));
    }

    public function saveiconAction() {
        if ($datas = $this->getRequest()->getPost()) {
            $html = '';
            try {
                if (!empty($datas['file'])) {

                    $icon_relative_path = '/icon/';
                    $folder = Application_Model_Application::getBaseImagePath().$icon_relative_path;
                    $datas['dest_folder'] = $folder;
                    $datas['new_name'] = $datas['file'];

                    $uploader = new Core_Model_Lib_Uploader();
                    $file = $uploader->savecrop($datas);
                    $this->getApplication()->setIcon($icon_relative_path.$file)->save();

                    $html = array(
                        'success' => 1,
                        'file' => $file
                    );
                }
                else {
                    $this->getApplication()->setIcon(null)->save();
                }
            } catch (Exception $e) {
                $html = array(
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($html));
        }
    }

    public function savestartupAction() {

        if($datas = $this->getRequest()->getPost()) {

            try {

                $relative_path = '/startup_image/';
                $filetype = $this->getRequest()->getParam('filetype');
                $folder = Application_Model_Application::getBaseImagePath().$relative_path;
                $datas['dest_folder'] = $folder;
                $datas['new_name'] = $datas['file'];

                $uploader = new Core_Model_Lib_Uploader();
                $file = $uploader->savecrop($datas);

                if($filetype == 'retina') {
                    $this->getApplication()->setStartupImageRetina($relative_path.'/'.$file);
                } else {
                    $this->getApplication()->setStartupImage($relative_path.'/'.$file);
                }
                $this->getApplication()->save();

                $datas = array(
                    'success' => 1,
                    'file' => $file
                );

            } catch (Exception $e) {
                $datas = array(
                    'error' => 1,
                    'message' => $e->getMessage()
                );
            }

            $this->getLayout()->setHtml(Zend_Json::encode($datas));
        }
    }

    protected function _createIcon($datas) {

        // Créé l'icône
        $image = imagecreatetruecolor(256, 256);

        // Rempli la couleur de fond
        $rgb = $this->_hex2RGB(000000);
        $background_color = imagecolorallocate($image, $rgb['red'], $rgb['green'], $rgb['blue']);
        imagefill($image, 0, 0, $background_color);
        $targ_w = $targ_h = 256;
        if(!empty($datas['icon']['file'])) {
            //Applique l'image
            $logo_relative_path = '/logo/';
            $folder = Application_Model_Application::getBaseImagePath().$logo_relative_path;
            if (!is_dir($folder))
                mkdir($folder, 0777, true);

            $src = Core_Model_Directory::getTmpDirectory(true).'/'.$datas['icon']['file'];
            $source = imagecreatefromstring(file_get_contents($src));
        }
        $dest = ImageCreateTrueColor($targ_w, $targ_h);
        imagecopyresampled($dest,$image,0,0,0,0,$targ_w,$targ_h,$targ_w,$targ_h);
        if($datas['icon']['file'] != '') {
            imagecopyresampled($dest,$source,0,0,$datas['icon']['x1'],$datas['icon']['y1'],$targ_w,$targ_h,$datas['icon']['w'],$datas['icon']['h']);
        }

        return $dest;
    }

    protected function _hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {

        $hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr);
        $rgbArray = array();

        if (strlen($hexStr) == 6) {
            $colorVal = hexdec($hexStr);
            $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
            $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
            $rgbArray['blue'] = 0xFF & $colorVal;
        } elseif (strlen($hexStr) == 3) {
            $rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
            $rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
            $rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
        } else {
            return false;
        }

        return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray;
    }

}
