<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\assets;

use yii\web\AssetBundle;
use yii\web\View;

class ToastAsset extends AssetBundle
{

    const POSITION_TOP_RIGHT = 'toast-top-right';
    const POSITION_BOTTOM_RIGHT = 'toast-bottom-right';
    const POSITION_TOP_LEFT = 'toast-top-left';
    const POSITION_BOTTOM_LEFT = 'toast-bottom-left';
    const POSITION_TOP_CENTER = 'toast-top-center';
    const POSITION_BOTTOM_CENTER = 'toast-bottom-center';
    const POSITION_TOP_FULL_WIDTH = 'toast-top-full-width';
    const POSITION_BOTTOM_FULL_WIDTH = 'toast-bottom-full-width';

    public $sourcePath = '@common/template/metronic';
    public $css = [
        'global/plugins/bootstrap-toastr/toastr.min.css',
    ];
    public $js = [
        'global/plugins/bootstrap-toastr/toastr.min.js'
    ];
    public $depends = [
        'common\assets\MetronicAdminAsset',
    ];

    /**
     * Generate js toast config
     * @param View $view
     * @param $options - Options js toast
     * toastr.options = {
        "closeButton": true,
        "debug": false,
        "positionClass": "toast-top-center",
        "onclick": null,
        "showDuration": "1000",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
        }
     */
    public static function config($view, $options)
    {
        $js = '';
        foreach ($options as $option => $value) {
            if (is_string($value)) {
                $js .= 'toastr.options.'.$option.' = "'.$value.'";';
            } else {
                $js .= 'toastr.options.'.$option.' = '.$value.';';
            }

        }
        $view->registerJs($js);
    }
}
