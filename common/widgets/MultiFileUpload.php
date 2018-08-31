<?php
/**
 * Created by PhpStorm.
 * User: qhuy
 * Date: 28/10/2014
 * Time: 10:16
 */

namespace common\widgets;


use common\assets\FileUploadUIAsset;
use dosamigos\fileupload\FileUploadUI;
use yii\helpers\Url;

class MultiFileUpload extends FileUploadUI
{

    /**
     * Xac dinh xem co download list file image ko
     * @var bool
     */
    public $downloadList = false;

    public $clientBinds = [];

    public $clientAdds = [];

    /**
     * Url load list image thumbnail
     * @var string
     */
    public $list_url = '';

    public function registerClientScript()
    {

        parent::registerClientScript();
        $view = $this->getView();
        FileUploadUIAsset::register($view);
        if ($this->downloadList) {
            $listUrl = Url::to($this->list_url);
            $id = $this->options['id'];
            $js = <<<JS
            // Load existing files:
            jQuery('#{$id}').addClass('fileupload-processing');
            jQuery.ajax({
                // Uncomment the following to send cross-domain cookies:
                //xhrFields: {withCredentials: true},
                url: "{$listUrl}",
                dataType: 'json',
                context: $('#{$id}')[0]
            }).always(function () {
                $(this).removeClass('fileupload-processing');
            }).done(function (result) {
                $(this).fileupload('option', 'done')
                    .call(this, $.Event('done'), {result: result});
            });
JS;
            $view->registerJs($js);
            $jsBind = [];
            if (!empty($this->clientBinds)) {
                foreach ($this->clientBinds as $event => $handler) {
                    $jsBind[] = "jQuery('#$id').bind('$event', $handler);";
                }
            }
            $view->registerJs(implode("\n", $jsBind));

        }
    }

} 