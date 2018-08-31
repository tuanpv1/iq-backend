<?php
use common\models\Content;
use common\models\ContentSiteAsm;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

?>
<?php

$changeStatusURL = Yii::$app->urlManager->createUrl(['content/change-content-site-status']);
$comfirmReTransfer = \Yii::t('app', 'Bạn có chắc chắn muốn phân phối lại không?');
$js              = <<<JS
function loadModalDataStream(url, self, popup = false){
    if(popup){
        var c = confirm("$comfirmReTransfer")
        if(!c){
            return
        }
    }

    $("#streaming-server-view").find(".modal-body").html('');

    if($(self).parent().find('#force_download').is(':checked')){
        url += '&force_download=true'
    }

    $.ajax({
        type     : 'GET',
        cache    : false,
        url      : url,
        success  : function(response) {
            $("#streaming-server-view").find(".modal-body").html(response);
        }
    });
}

function loadModalDataStream_(url, self, popup = false){
    if(popup){
        var c = confirm("$comfirmReTransfer")
        if(!c){
            return
        }
    }

    $("#streaming-server-view").find(".modal-body").html('');

        url += '&force_download=true'

    $.ajax({
        type     : 'GET',
        cache    : false,
        url      : url,
        success  : function(response) {
            $("#streaming-server-view").find(".modal-body").html(response);
        }
    });
}


function checkDownload(self){
    if($(self).parent().find('#force_download_').is(':checked')){
         document.getElementById("tranfer_again_").style.visibility = "visible";
    }else{
        document.getElementById("tranfer_again_").style.visibility = "hidden";
    }
}

$('select[name=change-status]').change(function(){
    var site_id = $(this).attr('site_id'),
        content_id = $(this).attr('content_id'),
        status = $(this).val(),
        url = ['{$changeStatusURL}', site_id, content_id].join('&');

    $.post(url, {site_id: site_id, content_id:content_id, status: status}, function(res){
        if(res.success)
            jQuery.pjax.reload({container:'#list-content-site'});
    })
})

JS;
$this->registerJs($js, View::POS_END);

echo GridView::widget([
    'dataProvider' => $contentSiteProvider,
    'responsive'   => true,
    'id'           => 'list-content-site',
    'pjax'         => true,
    'hover'        => true,
    'columns'      => [
        [
            'class'  => '\kartik\grid\DataColumn',
            'format' => 'raw',
            'label'  => \Yii::t('app', 'Nhà cung cấp dịch vụ'),
            'value'  => function ($model, $key, $index, $widget) {
                return $model->site_name;
            },
        ],
        [
            'class'  => '\kartik\grid\DataColumn',
            'format' => 'raw',
            'label'  => \Yii::t('app', 'Trạng thái phân phối'),
            'value'  => function ($model, $key, $index, $widget) {
                /** @var $model \common\models\Content */
                return ContentSiteAsm::getStatusNameByStatus($model->content_site_asm_status);
            },
        ],
        [
            'class'  => '\kartik\grid\DataColumn',
            'format' => 'raw',
            'label'  => \Yii::t('app', 'Tác động'),
            'value'  => function ($model, $key, $index, $widget) use ($id, $default_site_id) {

                $modalStreamingServer = Yii::$app->urlManager->createUrl(['/content/modal-streaming-server', 'site_id' => $model->site_id, 'content_id' => $id]);
                $reTransferBtn_        = $model->site_id != $default_site_id ? Html::a(\Yii::t('app', 'Phân phối ghi đè'), '#streaming-server-view',
                        ['class'        => 'btn btn-danger',
                            'data-toggle'   => 'modal',
                            'id'=>'tranfer_again_',
                            'data-backdrop' => "static",
                            'data-keyboard' => "false",
                            'onclick'       => "js:loadModalDataStream_('$modalStreamingServer', this, true);",
                        ]): '';

                $reTransferBtn        = $model->site_id != $default_site_id ? Html::a(\Yii::t('app', 'Phân phối lại'), '#streaming-server-view',
                    ['class'        => 'btn btn-danger',
                        'data-toggle'   => 'modal',
                        'id'=>'tranfer_again',
                        'data-backdrop' => "static",
                        'data-keyboard' => "false",
                        'onclick'       => "js:loadModalDataStream_('$modalStreamingServer', this, true);",
                    ]) : '';

                $display = [
                    ContentSiteAsm::STATUS_NOT_TRANSFER   => Html::a('Phân phối', '#streaming-server-view', ['class' => 'btn btn-primary', 'data-toggle' => 'modal', 'data-backdrop' => "static", 'data-keyboard' => "false", 'onclick' => "js:loadModalDataStream('$modalStreamingServer', this);"]),
                    ContentSiteAsm::STATUS_TRANSFERING    => '',
                    ContentSiteAsm::STATUS_TRANSFER_ERROR => $reTransferBtn,
                    ContentSiteAsm::STATUS_ACTIVE         => Html::dropDownList('change-status', null, [
                        ContentSiteAsm::STATUS_ACTIVE    => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_ACTIVE),
                        ContentSiteAsm::STATUS_INACTIVE  => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_INACTIVE),
                        ContentSiteAsm::STATUS_INVISIBLE => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_INVISIBLE),
                    ], ['class' => 'btn btn-default', 'site_id' => $model->site_id, 'content_id' => $id]) . $reTransferBtn_,
                    ContentSiteAsm::STATUS_INACTIVE       => Html::dropDownList('change-status', null, [
                        ContentSiteAsm::STATUS_INACTIVE  => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_INACTIVE),
                        ContentSiteAsm::STATUS_ACTIVE    => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_ACTIVE),
                        ContentSiteAsm::STATUS_INVISIBLE => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_INVISIBLE),
                    ], ['class' => 'btn btn-default', 'site_id' => $model->site_id, 'content_id' => $id]) ,
                    ContentSiteAsm::STATUS_INVISIBLE      => Html::dropDownList('change-status', null, [
                        ContentSiteAsm::STATUS_INVISIBLE => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_INVISIBLE),
                        ContentSiteAsm::STATUS_ACTIVE    => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_ACTIVE),
                        ContentSiteAsm::STATUS_INACTIVE  => ContentSiteAsm::getStatusNameByStatus(ContentSiteAsm::STATUS_INACTIVE),
                    ], ['class' => 'btn btn-default', 'site_id' => $model->site_id, 'content_id' => $id]) ,
                ];

                return $display[$model->content_site_asm_status];

            },
        ],

    ],
]);

\yii\bootstrap\Modal::begin([
    'header'      => \Yii::t('app', 'Chọn máy chủ'),
    'closeButton' => ['label' => 'Cancel'],
    'options'     => ['id' => 'streaming-server-view'],
    'size'        => \yii\bootstrap\Modal::SIZE_DEFAULT,
]);
?>

<?php \yii\bootstrap\Modal::end();?>
