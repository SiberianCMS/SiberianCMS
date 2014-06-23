<?php

class Core_Model_Directory
{
    protected static $_path;

    protected static $_base_path;

    protected static $_design_path;

    public static function getPathTo($path = '') {
        if(substr($path, 0, 1) !== '/') $path = '/'.$path;
        return self::$_path.$path;
    }

    public static function getBasePathTo($path = '') {
        if(substr($path, 0, 1) !== '/') $path = '/'.$path;
        return self::$_base_path.$path;
    }

    public static function getDesignPath($base = false) {
        return $base ? self::getBasePathTo(self::$_design_path) : self::getPathTo(self::$_design_path);
    }

    public static function getSessionDirectory($base = false) {
        return $base ? self::getBasePathTo('/var/session') : self::getPathTo('/var/session');
    }

    public static function getTmpDirectory($base = false) {
        return $base ? self::getBasePathTo('/var/tmp') : self::getPathTo('/var/tmp');
    }

    public static function getCacheDirectory($base = false) {
        return $base ? self::getBasePathTo('/var/cache') : self::getPathTo('/var/cache');
    }

    public static function getImageCacheDirectory($base = false) {
        return $base ? self::getBasePathTo('/var/cache/images') : self::getPathTo('/var/cache/images');
    }

    public static function setPath($path = '') {
        self::$_path = $path;
    }

    public static function setBasePath($path = '') {
        self::$_base_path = $path;
    }

    public static function setDesignPath($path = '') {
        self::$_design_path = $path;
    }

    public static function delete($dir) {

        $dir = new DirectoryIterator($dir);

        foreach($dir as $file) {
            if($file->isDot()) continue;
            else if($file->isDir()) {
                self::delete($file->getRealPath());
            } else if($file->isFile()) {
                unlink($file->getRealPath());
            }
        }

        $dir->rewind();
        rmdir($dir->getRealPath());

    }

    public static function move($src, $dst) {

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src), RecursiveIteratorIterator::SELF_FIRST);

        foreach($files as $file) {

            $basepath = $dst.str_replace($src, '', $file->getPath());
            if(!is_dir($basepath)) {
                mkdir($basepath, 0775, true);
            }

            copy($file->getRealpath(), $basepath.'/'.$file->getFilename());

        }

        self::delete($src);

    }

    public static function duplicate($src, $dst) {

        $src = new DirectoryIterator($src);

        foreach($src as $file) {
            if($file->isDot()) continue;
            else if($file->isDir()) {
                mkdir($dst.'/'.$file->getFileName());
                self::duplicate($file->getRealPath(), $dst.'/'.$file->getFileName());
            } else if($file->isFile()) {
                copy($file->getRealPath(), $dst.'/'.$file->getFileName());
            }
        }

    }

    public static function zip($source, $destination) {

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        if (is_dir($source) === true) {
            // 4096 <-> RecursiveDirectoryIterator::SKIP_DOTS
            // RecursiveDirectoryIterator::SKIP_DOTS doesn't exist in PHP < 5.3
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, 4096), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {

                $basepath = $file->getRealpath();
                $path = str_replace($source.'/', '', $basepath);
                if ($file->isDir()) {
                    $zip->addEmptyDir($path);
                }
                else if($file->isFile()) {
                    $zip->addFromString($path, file_get_contents($basepath));
                }
            }
        }

        $zip->close();

        return $zip;

    }

}