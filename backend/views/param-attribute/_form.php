<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ParamAttribute */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fullSpan' => 8,
    'options' => ['enctype' => 'multipart/form-data'],
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'labelSpan' => 3,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
]); ?>
<div class="form-body">
    <?= $form->field($model, 'type')->hiddenInput(['class' => 'input-circle'])->label(false) ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>

    <?= $form->field($model, 'param')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'type')->dropDownList(
        \common\models\ParamAttribute::getListType(), ['class' => 'input-circle']
    ) ?>

    <?= $form->field($model, 'type_app')->dropDownList(
        \common\models\ParamAttribute::getListTypeApp(), ['class' => 'input-circle']
    ) ?>

    <?= $form->field($model, 'status')->dropDownList(
        \common\models\ParamAttribute::getListStatus(), ['class' => 'input-circle']
    ) ?>

</div>
<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton($model->isNewRecord ? \Yii::t('app', 'Tạo mới') : \Yii::t('app', 'Cập nhật'),
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a(\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
