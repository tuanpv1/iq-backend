<?php

use kartik\widgets\FileInput;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Multilanguage */
/* @var $form yii\widgets\ActiveForm */

$showPreview = !$model->isNewRecord && !empty($model->image);

?>

<?php $form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fullSpan' => 8,
    'options' => ['enctype' => 'multipart/form-data'],
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'labelSpan' => 3,
        'deviceSize' => ActiveForm::SIZE_TINY,
    ],
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
]); ?>
    <div class="form-body">

        <?= $form->field($model, 'name')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
        <?php if ($showPreview) { ?>
            <div class="form-group field-category-icon">
                <div class="col-sm-offset-3 col-sm-5">
                    <?php echo Html::img($model->getImageLink(), ['class' => 'file-preview-image']) ?>
                </div>
            </div>
        <?php } ?>
        <?= $form->field($model, 'image')->widget(FileInput::classname(), [
            'options' => ['multiple' => true, 'accept' => 'image/*'],
            'pluginOptions' => [
                'previewFileType' => 'image',
                'showUpload' => false,
                'showPreview' => (!$showPreview) ? true : false,
                'browseLabel' => '',
                'removeLabel' => '',
                'overwriteInitial' => true
            ]
        ]); ?>
        <?= $form->field($model, 'code')->textInput(['maxlength' => 50, 'class' => 'input-circle']) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
        <?= $form->field($model, 'status')->dropDownList(
            \common\models\Multilanguage::getListStatus(), ['class' => 'input-circle']
        ) ?>
        <?= $form->field($model, 'file_be')->widget(FileInput::classname(), [
            'options' => ['multiple' => true, 'accept' => '*'],
            'pluginOptions' => [
                'previewFileType' => 'any',
                'showUpload' => false,
                'showPreview' => false,
                'browseLabel' => '',
                'removeLabel' => '',
                'overwriteInitial'=>true
            ]
        ]); ?>
        <?= $form->field($model, 'file_box')->widget(FileInput::classname(), [
            'options' => ['multiple' => true, 'accept' => '*'],
            'pluginOptions' => [
                'previewFileType' => 'any',
                'showUpload' => false,
                'showPreview' => false,
                'browseLabel' => '',
                'removeLabel' => '',
                'overwriteInitial'=>true
            ]
        ]); ?>
        <?= $form->field($model,'is_default')->checkbox()?>

    </div>


    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?= Html::submitButton($model->isNewRecord ? 'Tạo mới' : 'Cập nhật',
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a('Quay lại', ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>