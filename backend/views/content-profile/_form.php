<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ContentProfile */
/* @var $form yii\widgets\ActiveForm */
?>

    <div class="content-profile-form">

        <?php 

        $form = ActiveForm::begin([
            'id' => 'update-content-profile-popup',
            'enableClientValidation' => true,
            'options' => ['enctype' => 'multipart/form-data'],
            'action' => \yii\helpers\Url::to(['content-profile/update', 'id' => $model->id]),
        ]); ?>

        <?= $form->field($model, 'content_id')->hiddenInput()->label(false) ?>

        <?php //echo $form->field($model, 'name')->textInput(['maxlength' => true]); ?>

        <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'type')->dropDownList( \common\models\ContentProfile::$types, ['class' => 'input-circle']) ?>

        <?= $form->field($model, 'status')->dropDownList(
            \common\models\ContentProfile::getListStreamStatus(), ['class' => 'input-circle']
        ) ?>

        <?= $form->field($model, 'progress')->textInput() ?>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? \Yii::t('app', 'Create') : \Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a(\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default', 'data-dismiss'=> 'modal']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>
<?php
$ajaxUpdateSubmit = Yii::$app->urlManager->createUrl(['/content-profile/update', 'id' => $model->id]);
// test modal
$submitFormAjax = <<<JS
$('#update-content-profile-popup').submit(function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    var params = new FormData();

        params.append('ContentProfile[content_id]' , $('#contentprofile-content_id').val());
        params.append('ContentProfile[url]' , $('#contentprofile-url').val());
        params.append('ContentProfile[description]' , $('#contentprofile-description').val());
        params.append('ContentProfile[type]' , $('#contentprofile-type').val());
        params.append('ContentProfile[status]' , $('#contentprofile-status').val());
        params.append('ContentProfile[bitrate]' , $('#contentprofile-bitrate').val());
        params.append('ContentProfile[width]' , $('#contentprofile-width').val());
        params.append('ContentProfile[height]' , $('#contentprofile-height').val());
        params.append('ContentProfile[quality]' , $('#contentprofile-quality').val());


          $.ajax({
          url: '$ajaxUpdateSubmit',
          type: 'POST',
           processData : false,
           contentType : false,

          data: params,
          success: function(response) {
              if(response.success=='true')
              {
              console.log(response);
            $('#content-profile-update').modal('hide');
              toastr.success(response.message);
              }
              else{
               // $('#show-message').html(response.message);
                toastr.error(response.message);
              }

            }

          });
     return false;
});

JS;
$this->registerJs($submitFormAjax, \yii\web\View::POS_HEAD);
?>