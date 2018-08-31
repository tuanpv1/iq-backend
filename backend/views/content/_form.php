<?php

use common\helpers\CVietnameseTools;
use common\models\Category;
use common\models\Content;
use common\models\Site;
use kartik\datecontrol\DateControl;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\Content */
/* @var $form yii\widgets\ActiveForm */
?>
<?php


$price_id = Html::getInputId($model, 'price');
$price_download = Html::getInputId($model, 'price_download');
$price_gift = Html::getInputId($model, 'price_gift');
$upload_update_url = \yii\helpers\Url::to(['/content/upload-file', 'id' => $model->id]);
$upload_create_url = \yii\helpers\Url::to(['/content/upload-file']);

$upload_url = $model->isNewRecord ? $upload_create_url : $upload_update_url;

$js = <<<JS
$(document).ready(function() {
    var the_terms = $("#free_id");

    if (the_terms.is(":checked")) {
        $("#pricing_id").attr("disabled", "disabled");
    } else {
        $("#pricing_id").removeAttr("disabled");
    }

    the_terms.click(function() {
        if ($(this).is(":checked")) {
            $("#pricing_id").attr("disabled", "disabled");
        } else {
            $("#pricing_id").removeAttr("disabled");
        }
    });
    // the_terms.click();

    $('button.kv-file-remove').click(function(e){
        console.log(e);
    });

    $('#list-site .checkbox input').click(function(){
        viewUploadSub(this);
    })

    var checkSite = $('#list-site .checkbox input')
    for(var i = 0; i < checkSite.length; i++){
        viewUploadSub(checkSite[i])
    }
    
    setTimeout(function(){
        var selection2 = $('.select2-selection__choice')
        for(var i in selection2){
            if(i < selection2.length){
                var self = selection2[i]
                if($(self).attr('title')){
                    $(self).html($(self).attr('title') + '<span class=\"select2-selection__choice__remove\" role=\"presentation\">×</span>')
                }
            }
        }
    }, 1500)
});

function viewUploadSub(target){
    if($(target).is(':checked')){
        if($(target).parents('.checkbox').find('input[type=file]').length  == 0){
            var uploadSubLabel = 'Upload subtitle '
            var uploadSubBtn = '<div style="float:right;"><label for="'+uploadSubLabel+'">' +uploadSubLabel+'</label> - &nbsp; <input type="file" id="'+uploadSubLabel+'" value="" name="Content[subtitles]['+ $(target).val() +']" style="float:right;"></div>';
            
            $(target).parents('.checkbox').append(uploadSubBtn)
        }
    }else{
        $(target).parents('.checkbox').find('input[type=file]').parent('div').remove()
    }
}
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>


<div class="form-body">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'id' => 'form-create-content',
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableAjaxValidation' => false,
        'enableClientValidation' => false,

    ]); ?>

    <h3 class="form-section">Thông tin nội dung</h3>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'display_name')->textInput(['maxlength' => 128, 'class' => 'form-control  input-circle']) ?>
        </div>
    </div>
    <?php
    echo $parent ? $form->field($model, 'parent_id')->hiddenInput(['value' => $parent])->label(false) : '';
    ?>
    <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'created_user_id')->hiddenInput()->label(false) ?>

    <?php if ($model->type == Category::TYPE_LIVE_CONTENT):
        // echo $form->field($model, 'is_free')->hiddenInput(['value' => 1])->label(false);
        ?>
        <?php if ($parent) {
        $disabled = ['disabled' => 'disabled'];
        echo $form->field($model, 'live_channel')->hiddenInput(['value' => $parent])->label(false);
    } else {
        $disabled = [];
    } ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'live_channel')->dropDownList(\common\models\Content::listLive(), ['class' => 'input', 'options' => [$parent => ['Selected' => 'selected']]]
                    + $disabled
                ) ?>
            </div>
        </div>
        <?php
        $js2 = "
            $('#content-started_at-disp').val($('#content-started_at').val());
            $('#content-ended_at-disp').val($('#content-ended_at').val());
        ";

        $this->registerJs($js2, \yii\web\View::POS_END);

        ?>
        <div class="row">
            <div class="col-md-12">
                <?php
                echo $form->field($model, 'started_at')->widget(DateControl::classname(), [
                    'type' => DateControl::FORMAT_DATETIME,
                    'displayFormat' => 'd-M-y H:i:s',
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php
                echo $form->field($model, 'ended_at')->widget(DateControl::classname(), [
                    'type' => DateControl::FORMAT_DATETIME,
                    'displayFormat' => 'd-M-y H:i:s'
                ]);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">

                <?=
                $form->field($model, 'thumbnail_epg[]')->widget(\kartik\widgets\FileInput::classname(), [
                    'options' => [
                        'multiple' => false,
                        'accept' => 'png,jpg,jpeg,gif,JPEG,JPG,GIF,PNG',
                        'id' => 'thumbnail_epg-preview'
                    ],
                    'language' => 'vi-VN',
                    'pluginOptions' => [
                        'uploadUrl' => $upload_url,
                        'uploadExtraData' => [
                            'type' => \common\models\Content::IMAGE_TYPE_THUMBNAIL_EPG,
                            'thumbnail_epg_old' => $model->thumbnail_epg
                        ],
                        'maxFileCount' => 1,
                        'showUpload' => false,
                        'initialPreview' => $thumbnail_epgPreview,
                        'initialPreviewConfig' => $thumbnail_epgInit,


                    ],
                    'pluginEvents' => [
                        "fileuploaded" => "function(event, data, previewId, index) {
                    var response=data.response;
                    console.log(response.success);
                    console.log(response);
                    if(response.success){
                        console.log(response.output);
                        var current_screenshots=response.output;
                        var old_value_text=$('#images_tmp').val();
                        console.log('xxx'+old_value_text);
                        if(old_value_text !=null && old_value_text !='' && old_value_text !=undefined)
                        {
                            var old_value=jQuery.parseJSON(old_value_text);

                            if(jQuery.isArray(old_value)){
                                console.log(old_value);
                                old_value.push(current_screenshots);

                            }
                        }
                        else{
                            var old_value= [current_screenshots];
                        }
                        $('#images_tmp').val(JSON.stringify(old_value));
                    }
                }",
                        "filedeleted" => "function(event, status, data) {
                    var response = data.responseJSON
                        if(response.success){
                            var deleted = response.result[0]
                            var old_value = jQuery.parseJSON($('#images_tmp').val());

                            if(jQuery.isArray(old_value)){
                                old_value = old_value.filter(function(v){
                                    v = typeof v === 'string'?jQuery.parseJSON(v):v
                                    return v.name !== deleted.name;
                                })
                            }
                            
                            $('#images_tmp').val(JSON.stringify(old_value));
                        }
                }",
                    ],

                ]) ?>
            </div>
        </div>

    <?php endif ?>

    <?php
    if($model->isNewRecord){
        if ($type == \common\models\Category::TYPE_FILM && !$model->parent && !$parent) {
            echo $form->field($model, 'is_series')->checkbox(['label' => 'Phim bộ'])->label(false);
        } elseif ($type == \common\models\Category::TYPE_RADIO && !$model->parent && !$parent) {
            echo $form->field($model, 'is_series')->checkbox(['label' => 'Radio bộ'])->label(false);
        }elseif($type == Category::TYPE_CLIP && !$model->parent && !$parent){
            echo $form->field($model, 'is_series')->checkbox(['label' => 'Clip bộ'])->label(false);
        }elseif($type == Category::TYPE_MUSIC && !$model->parent && !$parent){
            echo $form->field($model, 'is_series')->checkbox(['label' => 'Music bộ'])->label(false);
        }
    }
    if ($type == \common\models\Category::TYPE_RADIO) {
        echo $form->field($model, 'is_live')->checkbox()->label(false);
    }
    ?>
    <?php
    if ($model->parent_id) { ?>
        <div class="col-md-12">
            <?= $form->field($model, 'episode_order')->textInput(['maxlength' => 128, 'class' => 'form-control  input-circle'])->label(Yii::t('app','Thứ tự tập')) ?>
        </div>
    <?php }
    ?>
    <?php
    if (!$model->isNewRecord && $model->type != Content::TYPE_LIVE) {
        echo $form->field($model, 'is_top')->checkbox(['label' => 'Đầu danh sách'])->label(false);
    }
    ?>
    <?php
    if ($type == \common\models\Category::TYPE_LIVE && !$model->parent) {
        echo $form->field($model, 'is_catchup')->checkbox();
    }
    ?>
    
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'cp_id')->dropDownList(
                \common\models\Content::getListCp(), ['class' => 'input-circle']
            )->label(Yii::t('app','Nhà cung cấp nội dung')) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
        <?= $form->field($model, 'activated_at')->widget(DateControl::classname(), [
            'type'          => DateControl::FORMAT_DATETIME,
            'displayFormat' => 'd-M-y H:i',
            'saveFormat' => 'php:U',
            'displayTimezone' => 'Asia/Ho_Chi_Minh',
        ]);
        ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
        <?= $form->field($model, 'expired_at')->widget(DateControl::classname(), [
            'type'          => DateControl::FORMAT_DATETIME,
            'displayFormat' => 'd-M-y H:i',
            'saveFormat' => 'php:U',
            'displayTimezone' => 'Asia/Ho_Chi_Minh',
        ]);
        ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'status')->dropDownList(
                \common\models\Content::getListStatus('filter'), ['class' => 'input-circle']
            ) ?>
        </div>
    </div>
    <?php if ($type == Content::TYPE_LIVE_CONTENT): ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'epg_status')->dropDownList(
                    \common\models\LiveProgram::getListStatus(), ['class' => 'input-circle']
                )->label('Trạng thái EPG') ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'honor')->dropDownList(
                \common\models\Content::getListHonor(), ['class' => 'input-circle']
            ) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'feature_title')->textInput(['maxlength' => 128, 'class' => 'form-control  input-circle']); ?>
        </div>
    </div>
    <?php if ($type == \common\models\Category::TYPE_LIVE): ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'order')->textInput(['maxlength' => 128, 'class' => 'form-control  input-circle']) ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'tags')->textInput(['maxlength' => 128, 'class' => 'form-control  input-circle']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">

            <?php
            $desc =
                Content::find()
                    ->innerJoin('content_related_asm', 'content.id = content_related_asm.content_id')
                    ->innerJoin('content as related', 'related.id = content_related_asm.content_related_id')
                    ->select('related.display_name as related_name, related.id')
                    ->where(['IN', 'content_related_asm.id', $model->related_content])
                    ->all();
            $des = [];
            foreach ($desc as $value) {
                $des[] = ['id' => $value->id, 'display_name' => $value->related_name];
            }
            $des = json_encode($des);

            ?>
            <?php

            echo $form->field($model, 'content_related_asm[]')->widget(\kartik\widgets\Select2::classname(), [
                'options' => [
                    'placeholder' => \Yii::t('app', 'Tìm kiếm nội dung liên quan...'),
                    'multiple' => true,
                    'id' => 'related'
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                    ],
                    'ajax' => [
                        'url' => \yii\helpers\Url::to(['related-list']),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(city) { return city.display_name; }'),
                    'templateSelection' => new JsExpression('function (city) { return city.display_name; }'),
                    'initSelection' => new JsExpression("function (element, callback) {
                            callback($des);
                            $.each($des, function (i, item) {
                                console.log(item)
                                var child = $('<option>', {
                                    value: item.id,
                                    text : item.display_name
                                });
                                element.append(child);
                                child.attr('selected', 'selected');
                            });
                        }")
                ],
                'pluginEvents' => [
                    'change' => "function(e){
                    setTimeout(function(){
                        var selection2 = $('.select2-selection__choice')
                        for(var i in selection2){
                            if(i < selection2.length){
                                var self = selection2[i]
                                if($(self).attr('title')){
                                    $(self).html($(self).attr('title') + '<span class=\"select2-selection__choice__remove\" role=\"presentation\">×</span>')
                                }
                            }
                        }
                    }, 100)
                }"
                ]
            ])->label('Nội dung liên quan');
            ?>
        </div>
    </div>


    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model, 'content')->widget(\dosamigos\ckeditor\CKEditor::className(), [
                'options' => ['rows' => 8],
                'preset' => 'basic'
            ]) ?>
        </div>
    </div>
    <?php if ($type != Category::TYPE_LIVE_CONTENT): ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'short_description')->widget(\dosamigos\ckeditor\CKEditor::className(), [
                    'options' => ['rows' => 6],
                    'preset' => 'basic'
                ]) ?>
            </div>
        </div>

        <h3 class="form-section">Ảnh </h3>

        <div class="row">
            <?php
            //       echo  $form->field($model, 'logo[]')->widget(\kartik\widgets\FileInput::classname(), [
            //            'options' => [
            //                'multiple' => true,
            //                // 'accept' => 'image/*'
            //            ],
            //            'pluginOptions' => [
            //                'uploadUrl' => \yii\helpers\Url::to(['/content/upload-file']),
            //                'uploadExtraData' => [
            //                    'type' => \common\models\Content::IMAGE_TYPE_LOGO,
            //                    'logo_old' => $model->logo
            //                ],
            //
            //                'maxFileCount' => 10,
            //                'overwriteInitial' => false,
            //
            //                'initialPreview' => $logoPreview,
            //                'initialPreviewConfig' => $logoInit,
            //
            //
            //            ],
            //            'pluginEvents' => [
            //                "fileuploaded" => "function(event, data, previewId, index) {
            //                var response=data.response;
            //                console.log(response.success);
            //                console.log(response);
            //                if(response.success){
            //                    console.log(response.output);
            //                    var current_screenshots=response.output;
            //                    var old_value_text=$('#images_tmp').val();
            //                    console.log('xxx'+old_value_text);
            //                    if(old_value_text !=null && old_value_text !='' && old_value_text !=undefined)
            //                    {
            //                        var old_value=jQuery.parseJSON(old_value_text);
            //
            //                        if(jQuery.isArray(old_value)){
            //                            console.log(old_value);
            //                            old_value.push(current_screenshots);
            //
            //                        }
            //                    }
            //                    else{
            //                        var old_value= [current_screenshots];
            //                    }
            //                    $('#images_tmp').val(JSON.stringify(old_value));
            //                 }
            //             }",
            //                "fileclear" => "function() {  console.log('delete'); }",
            //            ],
            //
            //        ]);
            ?>
        </div>
        <div class="row">
            <div class="col-md-12">

                <?=
                $form->field($model, 'thumbnail[]')->widget(\kartik\widgets\FileInput::classname(), [
                    'options' => [
                        'multiple' => false,
                        'id' => 'content-thumbnail',
                    ],
                    'pluginOptions' => [
                        'uploadUrl' => $upload_url,
                        'uploadExtraData' => [
                            'type' => \common\models\Content::IMAGE_TYPE_THUMBNAIL,
                            'thumbnail_old' => $model->thumbnail
                        ],
                        'language' => 'vi-VN',
                        'showUpload' => false,
                        'showUploadedThumbs' => false,
                        'initialPreview' => $thumbnailPreview,
                        'initialPreviewConfig' => $thumbnailInit,
                        'maxFileSize' => 1024 * 1024 * 10,
                    ],
                    'pluginEvents' => [
                        "fileuploaded" => "function(event, data, previewId, index) {
                    var response=data.response;
                    if(response.success){
                        var current_screenshots=response.output;
                        var old_value_text=$('#images_tmp').val();
                        if(old_value_text !=null && old_value_text !='' && old_value_text !=undefined)
                        {
                            var old_value=jQuery.parseJSON(old_value_text);

                            if(jQuery.isArray(old_value)){
                                old_value = old_value.filter(function(v){
                                    v = jQuery.parseJSON(v)
                                    console.log(typeof v.type, v.type);
                                    return v.type !== '2';
                                })
                                old_value.push(current_screenshots);
                            }
                        }
                        else{
                            var old_value= [current_screenshots];
                        }
                        $('#images_tmp').val(JSON.stringify(old_value));
                    }
                }",
                        "filedeleted" => "function(event, status, data) {
                    var response = data.responseJSON
                    if(response.success){
                        var deleted = response.result[0]
                        var old_value = jQuery.parseJSON($('#images_tmp').val());

                        if(jQuery.isArray(old_value)){
                            old_value = old_value.filter(function(v){
                                v = typeof v === 'string'?jQuery.parseJSON(v):v
                                return v.name !== deleted.name;
                            })
                        }

                        $('#images_tmp').val(JSON.stringify(old_value));
                    }
                }",
                    ],

                ]) ?>

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?=
                $form->field($model, 'screenshoot[]')->widget(\kartik\widgets\FileInput::classname(), [
                    'options' => [
                        'multiple' => true,
                        // 'accept' => 'image/png,image/jpg,image/jpeg,image/gif',
                        'id' => 'content-screenshoot'
                    ],
                    'pluginOptions' => [
                        'uploadUrl' => $upload_url,
                        'uploadExtraData' => [
                            'type' => \common\models\Content::IMAGE_TYPE_SCREENSHOOT,
                            'screenshots_old' => $model->screenshoot
                        ],
                        'maxFileCount' => 10,
                        'showUpload' => false,
                        'initialPreview' => $screenshootPreview,
                        'initialPreviewConfig' => $screenshootInit,
                        'maxFileSize' => 1024 * 1024 * 10,
                    ],
                    'pluginEvents' => [
                        "fileuploaded" => "function(event, data, previewId, index) {
                        var response=data.response;
                        if(response.success){
                            var current_screenshots=response.output;
                            var old_value_text=$('#images_tmp').val();
                            if(old_value_text !=null && old_value_text !='' && old_value_text !=undefined)
                            {
                                var old_value=jQuery.parseJSON(old_value_text);

                                if(jQuery.isArray(old_value)){
                                    old_value.push(current_screenshots);

                                }
                            }
                            else{
                                var old_value= [current_screenshots];
                            }
                            $('#images_tmp').val(JSON.stringify(old_value));
                         }
                     }",
                        "filedeleted" => "function(event, status, data) {
                        var response = data.responseJSON
                        if(response.success){
                            var deleted = response.result[0]
                            var old_value = jQuery.parseJSON($('#images_tmp').val());

                            if(jQuery.isArray(old_value)){
                                old_value = old_value.filter(function(v){
                                    v = typeof v === 'string'?jQuery.parseJSON(v):v
                                    return v.name !== deleted.name;
                                })
                            }
                            
                            $('#images_tmp').val(JSON.stringify(old_value));
                        }
                    }",
                    ],

                ]) ?>

                <?= $form->field($model, 'assignment_sites')->checkboxList(Site::getSiteList(null, ['id', 'name'], $model->id), ['id' => 'list-site']) ?>
                <?php
                // var_dump($model->readonlyAssignment_sites);die;
                $jsAs = "";
                foreach ($model->readonlyAssignment_sites as $readonlyAssignment_site) if ($model->type != \common\models\Content::TYPE_NEWS && $model->type != \common\models\Content::TYPE_LIVE && $model->is_series == \common\models\Content::IS_MOVIES) {
                    $jsAs .= "var a = $('#list-site').find('input[value=$readonlyAssignment_site]').click(function() { $(this).prop('checked', true) ; return false; }); a.parent('label').css('font-weight','Bold');";
                }

                $this->registerJs($jsAs, \yii\web\View::POS_END);


                ?>
                <?= $form->field($model, 'default_site_id')->dropDownList(Site::getSiteList(null, ['id', 'name'])) ?>
            </div>
        </div>

        <!--    --><?php //if (!$model->parent): ?>

        <div class="row">

            <div class="form-group field-content-price">
                <label class="control-label col-md-2" for="content-price">Danh mục</label>

                <div class="col-md-10">
                    <?= \common\widgets\Jstree::widget([
                        'clientOptions' => [
                            "checkbox" => ["keep_selected_style" => false],
                            "plugins" => ["checkbox"]
                        ],
                        'type' => $model->type,
                        'sp_id' => $site_id,
                        'cp_id' => true,
                        'data' => isset($selectedCats) ? $selectedCats : [],
                        'eventHandles' => [
                            'changed.jstree' => "function(e,data) {
                            jQuery('#list-cat-id').val('');
                            var i, j, r = [];
                            var catIds='';
                            for(i = 0, j = data.selected.length; i < j; i++) {
                                var item = $(\"#\" + data.selected[i]);
                                var value = item.attr(\"id\");
                                if(i==j-1){
                                    catIds += value;
                                } else{
                                    catIds += value +',';

                                }
                            }
                            jQuery(\"#default_category_id\").val(data.selected[0])
                            jQuery(\"#list-cat-id\").val(catIds);
                            console.log(jQuery(\"#list-cat-id\").val());
                         }"
                        ]
                    ]) ?>
                </div>
                <div class="col-md-offset-2 col-md-10"></div>
                <div class="col-md-offset-2 col-md-10">
                    <div class="help-block"></div>
                </div>
            </div>
        </div>
        <!--    --><?php //endif; ?>
        <?= $form->field($model, 'list_cat_id')->hiddenInput(['id' => 'list-cat-id'])->label(false) ?>
        <?= $form->field($model, 'default_category_id')->hiddenInput(['id' => 'default_category_id'])->label(false) ?>


    <?php endif; ?>
    <?php
    // $actors =  ArrayHelper::map(ActorDirector::find()->andWhere(['status'=>ActorDirector::STATUS_ACTIVE,
    //     'content_type'=>ActorDirector::TYPE_VIDEO,
    //     'type'=>ActorDirector::TYPE_ACTOR])->all(), 'id', 'name');
    // $directors =  ArrayHelper::map(ActorDirector::find()->andWhere(['status'=>ActorDirector::STATUS_ACTIVE,
    //     'content_type'=>ActorDirector::TYPE_VIDEO,
    //     'type'=>ActorDirector::TYPE_DIRECTOR])->all(), 'id', 'name');
    $content_directors = json_encode($model->content_directors ? $model->content_directors : []);
    $content_actors = json_encode($model->content_actors ? $model->content_actors : []);
    ?>
    <?php if ($model->type == Content::TYPE_VIDEO || $model->type == Content::TYPE_KARAOKE): ?>
        <?= $form->field($model, 'content_directors[]')->widget(\kartik\widgets\Select2::classname(), [
            'options' => [
                'multiple' => true,
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                ],
                'ajax' => [
                    'url' => \yii\helpers\Url::to(['find-directors', 'type' => $type ? $type : $model->type]),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) {return markup; }'),
                'templateResult' => new JsExpression('function(d) { return d.name; }'),
                'templateSelection' => new JsExpression('function (d) { return d.name; }'),
                'initSelection' => new JsExpression("function (element, callback) {
                            $.each($content_directors, function (i, item) {
                                var child = $('<option>', {
                                    value: item.id,
                                    text : item.name
                                });
                                element.append(child);
                                child.attr('selected', 'selected'); 
                            });
                            console.log('re')
                            callback($content_directors);
                        }")
            ],
            'pluginEvents' => [
                'change' => "function(e){
                    setTimeout(function(){
                        var selection2 = $('.select2-selection__choice')
                        for(var i in selection2){
                            if(i < selection2.length){
                                var self = selection2[i]
                                if($(self).attr('title')){
                                    $(self).html($(self).attr('title') + '<span class=\"select2-selection__choice__remove\" role=\"presentation\">×</span>')
                                }
                            }
                        }
                    }, 100)
                }"
            ]
        ]);
        ?>
        <?= $form->field($model, 'content_actors[]')->widget(\kartik\widgets\Select2::classname(), [
            'options' => [
                'multiple' => true,
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'minimumInputLength' => 3,
                'language' => [
                    'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                ],
                'ajax' => [
                    'url' => \yii\helpers\Url::to(['find-actors', 'type' => $type ? $type : $model->type]),
                    'dataType' => 'json',
                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateResult' => new JsExpression('function(d) { return d.name; }'),
                'templateSelection' => new JsExpression('function (d) { return d.name; }'),
                'initSelection' => new JsExpression("function (element, callback) {
                            callback($content_actors);
                            $.each($content_actors, function (i, item) {
                                var child = $('<option>', {
                                    value: item.id,
                                    text : item.name
                                });
                                element.append(child);
                                child.attr('selected', 'selected');
                            });
                        }")
            ],
            'pluginEvents' => [
                'change' => "function(e){
                    setTimeout(function(){
                        var selection2 = $('.select2-selection__choice')
                        for(var i in selection2){
                            if(i < selection2.length){
                                var self = selection2[i]
                                if($(self).attr('title')){
                                    $(self).html($(self).attr('title') + '<span class=\"select2-selection__choice__remove\" role=\"presentation\">×</span>')
                                }
                            }
                        }
                    }, 100)
                }"
            ]
        ]);
        ?>
    <?php endif; ?>
    <?php foreach ($model->extraAttr as $extra): ?>
        <?php
        $js2 = "
            $('input[id=content-'+$('#content-contentattr-$extra->id').attr('targetid')+']').val($('#content-contentattr-$extra->id').val());
            $('#content-contentattr-$extra->id').keypress(function(){
                var target = $(this).attr('targetid');
                $('input[id=content-'+target+']').val($(this).val());
            });
        ";

        $this->registerJs($js2, \yii\web\View::POS_END);

        ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, "contentAttr[$extra->id]")->textInput(['maxlength' => 128, 'class' => 'form-control', 'targetid' => CVietnameseTools::makeSearchableStr($extra->name)])->label($extra->name) ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?= $form->field($model, 'images')->hiddenInput(['id' => 'images_tmp'])->label(false) ?>
    <?= Html::submitButton($model->isNewRecord ? \Yii::t('app', 'Tạo') : \Yii::t('app', 'Cập nhật'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>


    <?php ActiveForm::end(); ?>

</div>
