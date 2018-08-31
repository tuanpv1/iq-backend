<?php
use common\assets\ToastAsset;
use common\widgets\MultiFileUpload;
use common\models\ContentProfile;
use kartik\grid\GridView;
use kartik\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\web\View;

/**
 * @var $model \common\models\Content
 * @var $profile \common\models\ContentProfile
 * @var $dataProvider
 */
ToastAsset::register($this);
ToastAsset::config($this, [
    'positionClass' => ToastAsset::POSITION_BOTTOM_RIGHT
]);
//\common\assets\ModalAsset::register($this);
$contentId = $model->id;
$siteId = $model->default_site_id || 5;
$url = \yii\helpers\Url::to(['transcode/search']);
$urlCreate = \yii\helpers\Url::to(['content-profile/create']);
$streamQuality = json_encode(\common\models\ContentProfile::$stream_quality);

$activeStatus = \common\models\ContentProfile::STATUS_ACTIVE;
$cdnType = \common\models\ContentProfile::TYPE_CDN;

$js = <<<JS
$(document).ready(function(){
    var streamQuality = {$streamQuality};
    var contentId = {$contentId};
    var siteId = {$siteId};
    // console.table(streamQuality);

    $('#search_file').keyup(function(){
        var self = this
        clearTimeout(window.delayTimer);
        window.delayTimer = setTimeout(function() {
            var keyword = $(self).val();
            var searchApi = '{$url}&title=' + keyword + '&content_id=' + contentId + '&site_id=' + siteId;
            $.get(searchApi, function(data){
                $('#profile-grid table tbody tr.newRow').remove();

                data.forEach(function(value, index){

                    var newRow = $('<tr/>').addClass("newRow");
                    
                    $('#profile-grid table tbody').append(newRow);

                    newRow.append($('<td/>').text(value.title + ' - URL: ' + value.cdn_id));
                    newRow.append($('<td/>'));
                    newRow.append($('<td/>').text('Active'));
                    newRow.append($('<td/>').text('Cdn'));
                    newRow.append($('<td/>').text(streamQuality[value.type]));
                    newRow.append($('<td/>').append($('<button class="btn btn-default" id="createContentProfile" profile="'+index+'" >ADD PROFILE</button>').val(index)));
                
                    $('button[profile='+index+']').click(function(){
                        var createData = data[$(this).attr('profile')];
                        createProfile(createData, $(this));
                    })
                })
            })
        }, 1000);
    })

    function createProfile(data, btn){
        var createData = {
            "ContentProfile[content_id]": contentId,
            "ContentProfile[name]": data.title,
            "ContentProfile[url]": data.cdn_id,
            "ContentProfile[status]": {$activeStatus},
            "ContentProfile[type]": {$cdnType},
            "ContentProfile[quality]": data.type
        };

        $.post('{$urlCreate}', createData, function(data){
            if(data.success === true){
                btn.text('ADDED');
                btn.prop('disabled', true);
                toastr.success('Success!');
            }else{
                toastr.error(data.message);
            }
        })
    }
})
JS;
$this->registerJs($js, View::POS_END);

?>

<!-- <input type="text" class="form-control input-circle col-md-6" id="search_file"
       placeholder="Tìm nội dung đã transcode ..."></input> -->

<?php
$js = <<<JS
    function loadModalData(url){

console.log(url)
    $("#content-profile-view").find(".modal-body").html('');
      $.ajax({
        type     :'GET',
        cache    : false,
        url  :url,
       success  : function(response) {
        console.log(response.success)
       if(response.success){
                 $("#content-profile-view").find(".modal-body").html(response.data);

            }else{
         }

        }
        });

    }
function loadUpdateModalData(url, model_id){


    $('#content-profile-update').on('hidden.bs.modal', function (event) {
    jQuery.pjax.reload({container:'#profile-grid-pjax'});
    });

    $("#content-profile-update").find(".modal-body").html('');
      $.ajax({
        type     :'GET',
        cache    : false,
        url  :url,
        data : {'id':model_id},
       success  : function(response) {
       if(response.success){
                 $("#content-profile-update").find(".modal-body").html(response.data);

            }else{
         }

        }
        });

}
   function openModal(){
    $('#modal_profile_forms').modal({
        backdrop: "static",
        keyboard:false

    });
   }

   function openModalRaw(){
    $('#modal_profile_forms_raw').modal({
        backdrop: "static",
        keyboard:false

    });
   }

    function deleteProfile(data){
        var allow = confirm("Bạn có chắc chắn muốn xóa profile này không");
        if(allow){
            var url = jQuery(data).attr('href');
            jQuery.post(url)
            .done(function(result) {
                if(result.success == true){
                    toastr.success(result.message);
                    jQuery.pjax.reload({container:'#profile-grid-pjax'});
                }else{
                    toastr.error(result.message);
                }
            })
            .fail(function(xhr, textStatus, errorThrown) {
                console.log(xhr);
                toastr.error(xhr.responseText);
            });
        }
        return false;
    }

    function openNewTab(data , type){
        if({$cdnType} != type){
            alert('Không hỗ trợ định dạng');
            return false;
        }
        window.open(data,'_blank');
        return false;
    }

JS;
$this->registerJs($js, View::POS_END);


/**
 * Xu ly khi form stream value create submit
 */
$formID = 'profile-form';
$formIDRaw = 'profile-form-raw';
$js = <<<JS
// get the form id and set the event
jQuery('#{$formID}').on('beforeSubmit', function(e) {
    \$form = jQuery('#{$formID}');
    var params= new FormData($(this)[0]);
    $.ajax({
        url: \$form.attr("action"),
        type: 'POST',
        //processData : false,
        //contentType : false,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        data: params,
        success: function(result) {
            if(result.success){
                toastr.success(result.message);
                jQuery.pjax.reload({container:'#profile-grid-pjax'});
                $("#modal_profile_forms").modal("hide");
                $("#modal_profile_forms input[type=text]").val('')

                $("#modal_profile_forms_raw").modal("hide");
                $("#modal_profile_forms_raw input[type=text]").val('')
            }else{
                toastr.error(result.message);
            }
        },
        fail: function() {
            toastr.error("server error");
        }

    });
    return false;
}).on('submit', function(e){
    e.preventDefault();
});

jQuery('#{$formIDRaw}').on('beforeSubmit', function(e) {
    \$form = jQuery('#{$formIDRaw}');
    var params= new FormData($(this)[0]);
    $.ajax({
        url: \$form.attr("action"),
        type: 'POST',
        //processData : false,
        //contentType : false,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        data: params,
        success: function(result) {
            if(result.success){
                toastr.success(result.message);
                jQuery.pjax.reload({container:'#profile-grid-pjax'});
                $("#modal_profile_forms").modal("hide");
                $("#modal_profile_forms input[type=text]").val('')

                $("#modal_profile_forms_raw").modal("hide");
                $("#modal_profile_forms_raw input[type=text]").val('')
            }else{
                toastr.error(result.message);
            }
        },
        fail: function() {
            toastr.error("server error");
        }

    });
    return false;
}).on('submit', function(e){
    e.preventDefault();
});
JS;
$this->registerJs($js, View::POS_END);

?>


<!-- start modal-->
<?php
\yii\bootstrap\Modal::begin([
    'header' => 'PROFILE DETAIL',
    'closeButton' => ['label' => 'Cancel'],
    'options' => ['id' => 'content-profile-view'],
    'size' => \yii\bootstrap\Modal::SIZE_DEFAULT
]);
?>
<div class="modal-body">
</div>

<?php \yii\bootstrap\Modal::end(); ?>
<!--end modal create-->


<!-- start modal-->
<?php
\yii\bootstrap\Modal::begin([
    'header' => 'PROFILE UPDATE',
    'closeButton' => ['label' => 'Cancel'],
    'options' => ['id' => 'content-profile-update'],
    'size' => \yii\bootstrap\Modal::SIZE_DEFAULT
]);
?>
<div class="modal-body">


</div>

<?php \yii\bootstrap\Modal::end(); ?>
<!--end modal create-->



<div class="modal fade" id="modal_profile_forms_raw" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Nhập tên file RAW</h4>
            </div>
            <div class="modal-body">

                <?php $form = ActiveForm::begin([
                    'id' => $formIDRaw,
                    'type' => ActiveForm::TYPE_HORIZONTAL,
                    'options' => ['enctype' => 'multipart/form-data'],
                    'action' => \yii\helpers\Url::to(['content-profile/create']),
                    'fullSpan' => 8,
                    'formConfig' => [
                        'type' => ActiveForm::TYPE_HORIZONTAL,
                        'labelSpan' => 3,
                        'deviceSize' => ActiveForm::SIZE_SMALL,
                    ],
                ]); ?>

                <?= Html::activeHiddenInput($profile, 'content_id'); ?>
                <?= Html::activeHiddenInput($profile, 'status', ['value' => ContentProfile::STATUS_RAW]); ?>
                <?= $form->field($profile, 'name')->textInput(['maxlength' => 150, 'class' => 'input-circle'])->label('Tên file RAW') ?>

                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <?= Html::submitButton($profile->isNewRecord ? 'Create' : 'Update',
                            ['class' => $profile->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                        <?= Html::a(\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>


        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="modal_profile_forms" tabindex="-1" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Profile</h4>
            </div>
            <div class="modal-body">

                <?php $form = ActiveForm::begin([
                    'id' => $formID,
                    'type' => ActiveForm::TYPE_HORIZONTAL,
                    'options' => ['enctype' => 'multipart/form-data'],
                    'action' => \yii\helpers\Url::to(['content-profile/create']),
                    'fullSpan' => 8,
                    'formConfig' => [
                        'type' => ActiveForm::TYPE_HORIZONTAL,
                        'labelSpan' => 3,
                        'deviceSize' => ActiveForm::SIZE_SMALL,
                    ],
                ]); ?>

                <?= Html::activeHiddenInput($profile, 'content_id'); ?>
                <?= $form->field($profile, 'url')->textInput(['maxlength' => 150, 'class' => 'input-circle']) ?>
                <?= $form->field($profile, 'width')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
                <?= $form->field($profile, 'height')->textInput(['class' => 'input-circle']) ?>
                <?= $form->field($profile, 'bitrate')->textInput(['class' => 'input-circle']) ?>
                <?= $form->field($profile, 'type')->dropDownList(\common\models\ContentProfile::$createTypes,
                    ['class' => 'input-circle']) ?>
                <?= $form->field($profile, 'quality')->dropDownList(
                    \common\models\ContentProfile::$stream_quality, ['class' => 'input-circle']
                ) ?>

                <div class="row">
                    <div class="col-md-offset-3 col-md-9">
                        <?= Html::submitButton($profile->isNewRecord ? 'Create' : 'Update',
                            ['class' => $profile->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                        <?= Html::a(\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default', 'data-dismiss' => 'modal']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>


        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<?php
if (!Yii::$app->params['tvod1Only']) {
    echo Html::button(\Yii::t('app', 'Tạo profile'),
        [
            //'data-toggle' => 'modal',
            // 'data-backdrop' => "static",
            //'data-keyboard' => "false",
            // 'data-target' => '#modal_profile_forms',
            'class' => 'btn btn-primary',
            'onclick' => 'openModal()'
        ]);
    echo Html::button(\Yii::t('app', 'Thêm file RAW'),
        [
            //'data-toggle' => 'modal',
            // 'data-backdrop' => "static",
            //'data-keyboard' => "false",
            // 'data-target' => '#modal_profile_forms',
            'class' => 'btn btn-warning',
            'onclick' => 'openModalRaw()'
        ]);
}
?>
<?php
$upload_info_url = \yii\helpers\Url::to(['content-profile/upload-info']);
$update_url = Yii::$app->urlManager->createUrl(['/content-profile/update-modal-data']);
$process_add_upload = <<<JS
function (e, data) {
        var that = this;
        $.getJSON('{$upload_info_url}', {id:{$model->id}, file: data.files[0].name}, function (result) {
            var file = result.file;
            data.uploadedBytes = file && file.size;
            $.blueimp.fileupload.prototype
                .options.uploadedBytes = data.uploadedBytes;

        });
    }
JS;

$process_upload_done = <<<JS
function(e, data){
            jQuery("#upload_file_modal").modal("hide");
            if(data.result == false){
                alert('Nội dung đã có bản raw, hãy xóa raw trước khi upload file mới')
                return false
            }

            if(data.result.files.length > 0 ){
                var file = data.result.files[0];
                loadUpdateModalData('$update_url', file.profile_id);
                jQuery("#content-profile-update").modal("show");
            }
        }
JS;


Modal::begin([
    'size' => Modal::SIZE_LARGE,
    'header' => '<h4>Upload Video</h4>',
    'id' => 'upload_file_modal',
    'toggleButton' => ['label' => 'Upload Video', 'class' => 'btn btn-success'],
    'closeButton' => ['label' => 'Cancel']
]);
echo MultiFileUpload::widget([
    'model' => $profile,
    'attribute' => 'url',
    'url' => ['content-profile/upload-video', 'id' => $model->id],
    'gallery' => true,
    'uploadTemplateView' => '@sp/views/content-profile/upload_video',
    'fieldOptions' => [
        'accept' => 'video/*'
    ],
    'clientOptions' => [
        'maxChunkSize' => 10000000,
        'maxNumberOfFiles' => 1,
        'messages' => [
                'uploadedBytes' => 'File đã tồn tại'
        ],
    ],
    'clientEvents' => [
        'fileuploaddone' => $process_upload_done,
        'fileuploadadd' => $process_add_upload
    ]
]);
Modal::end();
?>
<?= GridView::widget([
    'id' => 'profile-grid',
    'dataProvider' => $profileProvider,
    'responsive' => true,
    'pjax' => true,
    'hover' => true,
    'columns' => [
//        [
//            'class' => 'kartik\grid\ExpandRowColumn',
//            'value' => function ($model, $key, $index, $column) {
//                return GridView::ROW_COLLAPSED;
//            },
//            'detail' => function ($model, $key, $index, $column) {
//                return Yii::$app->controller->renderPartial('_expand-profile-details', ['model' => $model]);
//            },
//        ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'url',
            'refreshGrid' => true,
            'editableOptions' => function ($model, $key, $index) {
                /* @var $model \common\models\ContentProfile */
                $model->getProfileUrl();
                return [
                    'header' => 'URL',
                    'size' => 'md',
                    'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                    'formOptions' => [
                        'action' => \yii\helpers\Url::to([
                            'content-profile/editable',
                            'profile_id' => $model->id
                        ]),
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => true,
                    ],
                ];
            },
        ],
        [
            'header' => 'Bitrate (kbps)',
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'bitrate',
            'refreshGrid' => true,
            'editableOptions' => function ($model, $key, $index) {
                /* @var $model \common\models\ContentProfile */
                return [
                    'header' => 'Bitrate (kbps)',
                    'size' => 'md',
                    'inputType' => \kartik\editable\Editable::INPUT_TEXT,
                    'formOptions' => [
                        'action' => \yii\helpers\Url::to([
                            'content-profile/editable',
                            'profile_id' => $model->id
                        ]),
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => true,
                    ],
                ];
            }
        ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'status',
            'label' => \Yii::t('app', 'Trạng thái'),
            'refreshGrid' => true,
            'editableOptions' => function ($model, $key, $index) {
                return [
                    'header' => 'Status',
                    'size' => 'md',
                    'displayValueConfig' => \common\models\ContentProfile::getListStreamStatus(),
                    'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                    'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                    'data' => \common\models\ContentProfile::getListStreamStatus(),
                    'formOptions' => [
                        'action' => \yii\helpers\Url::to([
                            'content-profile/editable',
                            'profile_id' => $model->id
                        ]),
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => true,
                    ],
                ];
            },
        ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'type',
            'label' => \Yii::t('app', 'Loại'),
            'refreshGrid' => true,
            'editableOptions' => function ($model, $key, $index) {
                return [
                    'header' => 'Type',
                    'size' => 'md',
                    'displayValueConfig' => \common\models\ContentProfile::$types,
                    'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                    'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                    'data' => \common\models\ContentProfile::$types,
                    'formOptions' => [
                        'action' => \yii\helpers\Url::to([
                            'content-profile/editable',
                            'profile_id' => $model->id
                        ]),
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => true,
                    ],
                ];
            },
        ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'quality',
            'label' => \Yii::t('app', 'Chất lượng'),
            'refreshGrid' => true,
            'editableOptions' => function ($model, $key, $index) {
                return [
                    'header' => 'Quality',
                    'size' => 'md',
                    'displayValueConfig' => \common\models\ContentProfile::$stream_quality,
                    'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                    'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                    'data' => \common\models\ContentProfile::$stream_quality,
                    'formOptions' => [
                        'action' => \yii\helpers\Url::to([
                            'content-profile/editable',
                            'profile_id' => $model->id
                        ]),
                        'enableClientValidation' => false,
                        'enableAjaxValidation' => true,
                    ],
                ];
            },
            'filterType' => GridView::FILTER_SELECT2,
            'filter' => \common\models\ContentProfile::$stream_quality,
            'filterWidgetOptions' => [
                'pluginOptions' => ['allowClear' => true],
            ],
            'filterInputOptions' => ['placeholder' => \Yii::t('app', 'Tất cả')],
        ],
        [
            'attribute' => '',
            'format' => 'raw',
            'width' => '30%',
            'value' => function ($model, $key, $index, $widget) {

                $urlAjax = Yii::$app->urlManager->createUrl(['/content-profile/view-modal-data', 'id' => $model->id]);
                $urlAjaxUpdate = Yii::$app->urlManager->createUrl(['/content-profile/update-modal-data']);
                /**
                 * @var $model \common\models\Content
                 */
                $res = Html::a('Show details', '#content-profile-view',
                    [
                        'data-toggle' => 'modal',
                        'data-backdrop' => "static",
                        'data-keyboard' => "false",
                        'onclick' => "js:loadModalData('$urlAjax');",
                        'class' => 'btn btn-primary'
                    ]);
                $res .= '&nbsp;&nbsp;';
                // $res .= Html::a(\Yii::t('app', 'Update'), '#content-profile-update',
                //     [
                //         'data-toggle' => 'modal',
                //         'data-backdrop' => "static",
                //         'data-keyboard' => "false",
                //         'onclick' => "js:loadUpdateModalData('$urlAjaxUpdate', $model->id);",
                //         'class' => 'btn btn-info'
                //     ]);
                // $res .= '&nbsp;&nbsp;';
                $urlDelete = \yii\helpers\Url::to(['content-profile/delete', 'id' => $model->id]);
                $res .= Html::a(\Yii::t('app', 'Delete'), $urlDelete,
                    ['onclick' => 'return deleteProfile(this);', 'class' => 'btn btn-danger']);

                $res .= '&nbsp;&nbsp;';
                $urlPlayVideo = \yii\helpers\Url::to(['content-profile/play-video', 'id' => $model->id]);
                $res .= $model->type != ContentProfile::TYPE_RAW ?Html::a(\Yii::t('app', 'Xem'),$urlPlayVideo,
                    ['onclick' => 'return openNewTab(this,'.$model->type.');', 'class' => 'btn btn-success']) : '';

                return $res;
            },


        ],
//        [
//            'class' => 'kartik\grid\ActionColumn',
//            'urlCreator' => function ($action, $model, $key, $index) {
//
//                return \yii\helpers\Url::to([
//                    'content-profile/editable',
//                    'profile_id' => $model->id
//                ]);
//            },
//            'buttons' => [
//                'profile-delete' => function ($url, $model) {
//                    $url = \yii\helpers\Url::to(['content-profile/delete', 'id' => $model->id]);
//                    return Html::a('<i class="glyphicon glyphicon-trash"></i>', $url, ['onclick' => 'return deleteProfile(this);']);
//                }
//            ],
//            'template' => '{profile-delete}'
//        ],
    ],
]); ?>
