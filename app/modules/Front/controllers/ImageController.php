<?php

class ImageController extends Core_Controller_Default
{

    protected $_image_type;
    protected $_image_ext;

    /**
     * @usage Zend_Debug::dump($this->getUrl('front/image/crop', array(
                'image' => base64_encode('http://www.mobistadium.com/wp-content/uploads/2012/11/fitness.jpg'),
                'width' => 640,
                'height' => 400
            )));
    */
    public function cropAction() {

//        Zend_Debug::dump(base64_encode('http://www.mobistadium.com/wp-content/uploads/2012/09/fitness1.jpg'));
//        die;

        // aHR0cDovL3d3dy5tb2Jpc3RhZGl1bS5jb20vd3AtY29udGVudC91cGxvYWRzLzIwMTIvMDkvZml0bmVzczEuanBn
        // aHR0cDovL3d3dy5tb2Jpc3RhZGl1bS5jb20vd3AtY29udGVudC91cGxvYWRzLzIwMTIvMDkvZml0bmVzcy1oZi5qcGc
        // aHR0cDovL3d3dy5tb2Jpc3RhZGl1bS5jb20vd3AtY29udGVudC91cGxvYWRzLzIwMTIvMDkvQnJhZC1QaXR0LUZpZ2h0LUNsdWItMy5qcGc
        $datas = $this->getRequest()->getParams();
        $default_url = '';

        try {
            if(!empty($datas['image'])) $default_url = $datas['image'];
            else die;

            if(empty($datas['width']) OR empty($datas['height'])) throw new Exception('');

            $image = base64_decode($datas['image']);
            $expected_width = $datas['width'];
            $expected_height = $datas['height'];

            $this->_setFileType($image);

            $image_id = 'wordpress_image_'.sha1($image).'-'.$expected_width.'x'.$expected_height;
            $tmp_file = Core_Model_Directory::getTmpDirectory(true).'/'.$image_id.'.'.$this->_image_ext;
            if(!is_dir(Core_Model_Directory::getImageCacheDirectory(true))) mkdir(Core_Model_Directory::getImageCacheDirectory(true), 0777);
            $image_path = Core_Model_Directory::getImageCacheDirectory(true).'/'.$image_id.'.'.$this->_image_ext;
            $dest_file = Core_Model_Directory::getBasePathTo($image_path);

//            if(file_exists($dest_file)) unlink($dest_file);
            if (!file_exists($dest_file) OR !@getimagesize($dest_file)) {

                $image_url = $datas['image'];
                $new_img = imagecreatefromstring(file_get_contents($image));
                $this->_saveImage($new_img, $tmp_file);
                imagedestroy($new_img);
                list($width, $height) = getimagesize($tmp_file);

                $new_width = $width;
                $new_height = $height;
                $ratio_width =  $expected_width / $width;
                $ratio_height = $expected_height / $height;

                if($ratio_height > $ratio_width) {
                    $new_width *= $ratio_height;
                    $new_height *= $ratio_height;
                }
                else {
                    $new_width *= $ratio_width;
                    $new_height *= $ratio_width;
                }

                Thumbnailer_CreateThumb::createThumbnail($tmp_file, $dest_file, $new_width, $new_height, $this->_image_ext, false, array('resizeUp' => true));
                Thumbnailer_CreateThumb::crop($dest_file, $dest_file, 0, 0, $expected_width, $expected_height, true);

                if(!file_exists($dest_file) OR !@getimagesize($dest_file)) throw new Exception('');
            }

            $image_url = $dest_file;

        }
        catch(Exception $e) {
            $image_url = '';
        }

        if(!empty($image_url)) {
            $this->_showImage($image_url);
        }

        die;
    }

    protected function _setFileType($url) {
        $this->_image_type = exif_imagetype($url);

        switch($this->_image_type) {
            case IMAGETYPE_GIF: $this->_image_ext = 'gif'; break;
            case IMAGETYPE_JPEG: $this->_image_ext = 'jpg'; break;
            case IMAGETYPE_PNG: $this->_image_ext = 'png'; break;
            case IMAGETYPE_BMP: $this->_image_ext = 'bmp'; break;
            default: throw new Exception('Unknown Image Type');
        }

        return $this;

    }

    protected function _saveImage($resource, $dest = null) {

        switch($this->_image_type) {
            case IMAGETYPE_GIF: imagegif($resource, $dest); break;
            case IMAGETYPE_JPEG: imagejpeg($resource, $dest); break;
            case IMAGETYPE_PNG: imagepng($resource, $dest); break;
            case IMAGETYPE_WBMP:
            case IMAGETYPE_BMP: imagewbmp($resource, $dest); break;
        }

    }

    protected function _showImage($img) {

        $this->getResponse()
            ->setHeader('Content-type', 'image/'.$this->_image_ext)
            ->setBody($this->_saveImage(imagecreatefromstring(file_get_contents($img))))
            ->sendResponse();
        ;

    }

}



