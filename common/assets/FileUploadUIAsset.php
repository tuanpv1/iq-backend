<?php

namespace common\assets;

use yii\web\AssetBundle;

/**
 * FileUploadUIAsset
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class FileUploadUIAsset extends AssetBundle
{
    public $sourcePath = '@bower/blueimp-file-upload';
    public $css = [
        'css/jquery.fileupload-ui.css'
    ];
    public $js = [
    ];
    public $depends = [
        'dosamigos\fileupload\FileUploadUIAsset',
    ];
}
