<?php

class Template_BlockController extends Core_Controller_Default {

    public function blankimageAction() {

        $image = imagecreatetruecolor($this->getRequest()->getParam('width', 320), $this->getRequest()->getParam('height', 75));
        $color = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $color);
        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);

        die;

    }

    public function colorizeAction() {

        if(($this->getRequest()->getParam('id') || $this->getRequest()->getParam('url') || $this->getRequest()->getParam('path')) AND $color = $this->getRequest()->getParam('color')) {

            $params = array('id', 'url', 'path', 'color');
            $path = '';
            foreach($params as $param) $id[] = $this->getRequest()->getParam($param);
            $id = md5(implode('+', $id));

            if($image_id = $this->getRequest()->getParam('id')) {
                $image = new Media_Model_Library_Image();
                $image->find($image_id);
                if(!$image->getCanBeColorized()) $color = null;
                $path = $image->getLink();
                $path = Media_Model_Library_Image::getBaseImagePathTo($path);
            } else if($url = $this->getRequest()->getParam('url')) {
                $path = Core_Model_Directory::getTmpDirectory(true).'/'.$url;
            } else if($path = $this->getRequest()->getParam('path')) {
                $path = base64_decode($path);
                if(!Zend_Uri::check($path)) {
                    $path = Core_Model_Directory::getBasePathTo($path);
                    if(!is_file($path)) die;
                }
            }

            $image = new Core_Model_Lib_Image();
            $image->setId($id)
                ->setPath($path)
                ->setColor($color)
                ->colorize()
            ;

            ob_start();
            @imagepng($image->getResources());
            $contents = ob_get_contents();
            ob_end_clean();
            imagedestroy($image->getResources());

            $this->getResponse()->setHeader('Content-Type', 'image/png');
            $this->getLayout()->setHtml($contents);

        }

    }

    public function colorize1Action() {

        if(($this->getRequest()->getParam('id') || $this->getRequest()->getParam('url') || $this->getRequest()->getParam('path')) AND $color = $this->getRequest()->getParam('color')) {
            $cache = Zend_Registry::get('cache');
            $id = $this->getRequest()->getParam('id');
//            $cache_id = 'colorized_image_'.sha1($id.':'.$color);
//            $cache->remove($cache_id);
//            $cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('colorized_image'));die;
//            if (($img = $cache->load($cache_id)) === false) {
                if($this->getRequest()->getParam('id')) {
                    $image = new Media_Model_Library_Image();
                    $image->find($id);
                    $path = $image->getLink();
                    $image_path = Media_Model_Library_Image::getBaseImagePathTo($path);
                } else if($this->getRequest()->getParam('url')) {
                    $url = $this->getRequest()->getParam('url');
                    $path = Core_Model_Directory::getTmpDirectory(true).'/'.$url;
                    $image_path = $path;
                } else if($this->getRequest()->getParam('path')) {
                    $path = $this->getRequest()->getParam('path');
                    $path = Core_Model_Directory::getBasePathTo(base64_decode($path));
                    if(!is_file($path)) die;
                    $image_path = $path;
                }
                $color = $color;
                $rgb = $this->toRgb($color);

                list($width, $height) = getimagesize($image_path);
                $new_img = imagecreatefromstring(file_get_contents($image_path));

                for($x=0; $x<$width; $x++) {
                    for($y=0; $y<$height; $y++) {
                        $colors = imagecolorat($new_img, $x, $y);
                        $current_rgb = imagecolorsforindex($new_img, $colors);
                        $color = imagecolorallocatealpha($new_img, $rgb['red'], $rgb['green'], $rgb['blue'], $current_rgb['alpha']);
                        imagesetpixel($new_img, $x, $y, $color);
                    }
                }

                imagesavealpha($new_img, true);

                ob_start();
                @imagepng($new_img);
                $contents = ob_get_contents();
                ob_end_clean();

                imagedestroy($new_img);
//                $cache->save($contents, $cache_id, array('colorized_image'));

                $img = $contents;

//            }
//            Zend_Debug::dump($img); die;
            $this->getResponse()->setHeader('Content-Type', 'image/png');
            $this->getLayout()->setHtml($img);
            die;

        }

    }

}
