<?php
namespace common\assets;

use yii\web\AssetBundle;

class CKEditorAsset extends AssetBundle
{
    public $sourcePath = '@common/template/metronic';
    public $css = [
    ];
    public $js = [
        'global/plugins/ckeditor/ckeditor.js',
    ];
    public $depends = [
        'common\assets\MetronicAdminAsset',
    ];
} 