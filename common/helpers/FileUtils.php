<?php
/**
 * Created by PhpStorm.
 * User: linhpv
 * Date: 2/11/15
 * Time: 10:57 AM
 */

namespace common\helpers;


class FileUtils {

    public static function appendToFile($filePath, $txt) {
        return file_put_contents($filePath, $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
    }
} 