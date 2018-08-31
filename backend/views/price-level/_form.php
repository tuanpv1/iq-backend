<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PriceLevel */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fullSpan' => 12,
    'action' => ['price-level/create'],
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'labelSpan' => 3,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]); ?>
<div class="form-body">
    <?= $form->field($model, 'price')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 3, 'class' => 'input-circle']) ?>
    <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>

</div>
<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton($model->isNewRecord ? 'Tạo giá mua lẻ' : 'Cập nhật',
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a('Quay lại', ['index'], ['class' => 'btn btn-default', 'data-dismiss'=> 'modal']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
