<?php
/**
 * Created by PhpStorm.
 * User: qhuyd
 * Date: 18/07/2016
 * Time: 16:36
 */
namespace common\components;

use FileUpload\FileSystem\FileSystem;

class FragmentFileSystem implements FileSystem {
    /**
     * @see FileSystem
     */
    public function isFile($path) {
        return is_file($path);
    }

    /**
     * @see FileSystem
     */
    public function isDir($path) {
        return is_dir($path);
    }

    /**
     * @see FileSystem
     */
    public function isUploadedFile($path) {
        return is_uploaded_file($path);
    }

    /**
     * {@inheritdoc}
     */
    public function doesFileExist($path)
    {
        return file_exists($path) ? true : false ;
    }

    /**
     * @see FileSystem
     */
    public function moveUploadedFile($from_path, $to_path) {
        return copy($from_path, $to_path) && unlink($from_path);
    }

    /**
     * @see FileSystem
     */
    public function writeToFile($path, $stream, $append = false) {
        return file_put_contents($path, $stream, $append ? \FILE_APPEND : 0);
    }

    /**
     * @see FileSystem
     */
    public function getInputStream() {
        return fopen('php://input', 'r');
    }

    /**
     * @see FileSystem
     */
    public function getFileStream($path) {
        return fopen($path, 'r');
    }

    /**
     * @see FileSystem
     */
    public function unlink($path) {
        return unlink($path);
    }

    /**
     * @see FileSystem
     */
    public function clearStatCache($path) {
        return clearstatcache(true, $path);
    }

    /**
     * @see FileSystem
     */
    public function getFilesize($path) {
        if(!file_exists($path)){
            return 0;
        }
        return filesize($path);
    }

}