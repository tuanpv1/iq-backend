<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class ICheckAsset extends AssetBundle
{



    public $sourcePath = '@common/template/metronic';
    public $css = [
        'global/plugins/icheck/skins/all.css',

     ];
    public $js = [
        'global/plugins/icheck/icheck.min.js',
    ];
    public $depends = [
        'common\assets\MetronicAdminAsset',
    ];


}
