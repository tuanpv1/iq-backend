<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class ModalAsset extends AssetBundle
{



    public $sourcePath = '@common/template/metronic';
    public $css = [
        'global/plugins/bootstrap-modal/css/bootstrap-modal-bs3patch.css',
        'global/plugins/bootstrap-modal/css/bootstrap-modal.css',

     ];
    public $js = [
        'global/plugins/bootstrap-modal/js/bootstrap-modalmanager.js',
        'global/plugins/bootstrap-modal/js/bootstrap-modal.js',
        'admin/pages/scripts/ui-extended-modals.js'
    ];
    public $depends = [
        'common\assets\MetronicAdminAsset',
    ];


}
