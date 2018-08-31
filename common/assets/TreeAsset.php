<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class TreeAsset extends AssetBundle
{



    public $sourcePath = '@common/template/metronic';
    public $css = [
        'global/plugins/jstree/dist/themes/default/style.min.css',

     ];
    public $js = [
        'global/plugins/jstree/dist/jstree.min.js',
//        'admin/pages/scripts/ui-tree.js',
    ];
    public $depends = [
        'common\assets\MetronicAdminAsset',
    ];


}
