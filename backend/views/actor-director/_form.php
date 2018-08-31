<?php

use common\models\ActorDirector;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\FileInput;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\ActorDirector */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-body">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'fullSpan' => 8,
        'options' => ['enctype' => 'multipart/form-data'],
        'formConfig' => [
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'labelSpan' => 3,
            'deviceSize' => ActiveForm::SIZE_SMALL,
        ],
        'enableClientValidation' => true,
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 200]) ?>
    <?php if($model->isNewRecord){?>
        <?= $form->field($model, 'image')->widget(FileInput::classname(), [
            'options' => ['accept' => ActorDirector::LIST_EXTENSION],
            'pluginOptions' => [
                'showPreview' => true,
                'overwriteInitial'=>false,
                'showRemove' => false,
                'showUpload' => false
            ]
        ]); ?>
    <?php }else{?>
        <?= $form->field($model, 'image')->widget(FileInput::classname(), [
            'options' => ['accept' => ActorDirector::LIST_EXTENSION],
            'pluginOptions' => [
                'previewFileType' => 'any',
                'initialPreview'=>[
                    Html::img(Url::to(Url::to($model->getImage())) , ['class'=>'file-preview-image', 'alt'=>$model->image , 'title'=>$model->image]),
                ],
                'showPreview' => true,
                'initialCaption'=>$model->getImage(),
                'overwriteInitial'=>true,
                'showRemove' => false,
                'showUpload' => false
            ]
        ]); ?>
    <?php }?>

    <?= $form->field($model, 'type')->dropDownList(ActorDirector::listType($content_type))?>

    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?= Html::submitButton($model->isNewRecord ? \Yii::t('app', 'Thêm mới') : \Yii::t('app', 'Cập nhật'),
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a(\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
