<?php
/**
 * Created by PhpStorm.
 * User: linhpv
 * Date: 5/28/15
 * Time: 10:41 AM
 */
use \kartik\form\ActiveForm;

?>


<?php

/**
 * Xu ly khi form stream value create submit
 */
$formID = 'form-create-content-episode';
$js = <<<JS
// get the form id and set the event
jQuery('#{$formID}').on('beforeSubmit', function(e) {
    \$form = jQuery('#{$formID}');
   $.post(
        \$form.attr("action"), // serialize Yii2 form
        \$form.serialize()
    )
        .done(function(result) {
            if(result.success){
                toastr.success(result.message);
                jQuery.pjax.reload({container:'#list-episode-pjax'});
                $("#create-content-episode-modal").modal("hide");
            }else{
                toastr.error(result.message);
            }
        })
        .fail(function() {
            toastr.error("server error");
        });
    return false;
}).on('submit', function(e){
    e.preventDefault();
});
JS;
$this->registerJs($js);
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Tạo Episode
                </div>
            </div>
            <div class="portlet-body form">
                <?php $form = ActiveForm::begin([
                    'options' => ['enctype' => 'multipart/form-data'],
                    'id' => 'form-create-content-episode',
                    'type' => ActiveForm::TYPE_HORIZONTAL,
                    'action' => \yii\helpers\Url::to(['content/create-episode']),
                    //'enableAjaxValidation' => true,
                    'enableClientValidation' => true,

                ]); ?>

                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($episode, 'display_name')->textInput(['maxlength' => 128, 'class' => 'form-control  input-circle', 'id' => 'content-display_name']) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($episode, 'episode_order')->textInput(['maxlength' => 128, 'class' => 'form-control  input-circle', 'id' => 'content-display_name']) ?>
                    </div>
                </div>
                <?= $form->field($episode, 'type')->hiddenInput()->label(false) ?>
                <?= $form->field($episode, 'site_id')->hiddenInput()->label(false) ?>
                <?= $form->field($episode, 'content_provider_id')->hiddenInput()->label(false) ?>

                <?= $form->field($episode, 'images')->hiddenInput(['id' => 'images_tmp'])->label(false) ?>

                <?= $form->field($episode, 'parent_id')->hiddenInput()->label(false) ?>

                <div class="row">
                    <div class="col-md-12">
                        <?=
                        $form->field($episode, 'thumbnail[]')->widget(\kartik\widgets\FileInput::classname(), [
                            'options' => [
                                'multiple' => true,
                                'accept' => 'image/*'
                            ],
                            'pluginOptions' => [
                                'uploadUrl' => \yii\helpers\Url::to(['/content/upload-file']),
                                'uploadExtraData' => [
                                    'type' => \common\models\Content::IMAGE_TYPE_THUMBNAIL,
                                    'thumbnail_old' => $episode->thumbnail
                                ],

                                'maxFileCount' => 10,


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
                                "fileclear" => "function() {  console.log('delete'); }",
                            ],

                        ]) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?=
                        $form->field($episode, 'screenshoot[]')->widget(\kartik\widgets\FileInput::classname(), [
                            'options' => [
                                'multiple' => true,
                                'accept' => 'image/*'
                            ],
                            'pluginOptions' => [
                                'uploadUrl' => \yii\helpers\Url::to(['/content/upload-file']),
                                'uploadExtraData' => [
                                    'type' => \common\models\Content::IMAGE_TYPE_SCREENSHOOT,
                                    'screenshots_old' => $episode->screenshoot
                                ],

                                'maxFileCount' => 10,

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
                                "fileclear" => "function() {  console.log('delete'); }",
                            ],

                        ]) ?>
                    </div>
                </div>
                <?= \kartik\helpers\Html::submitButton($episode->isNewRecord ? 'Create' : 'Update', ['class' => $episode->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>


                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>