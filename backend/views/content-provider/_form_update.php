<?php

use common\models\ContentProvider;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ContentProvider */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fullSpan' => 8,
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'labelSpan' => 3,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
    //'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>
    <div class="form-body">
        <?= $form->field($model, 'cp_name')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
        <?= $form->field($model, 'cp_address')->textarea(['rows' => 6, 'class' => 'input-circle']) ?>
        <?= $form->field($model, 'cp_mst')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
        <?= $form->field($model, 'status')->dropDownList(\common\models\ContentProvider::getListStatus()) ?>

    </div>
    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?= Html::submitButton($model->isNewRecord ? ''.\Yii::t('app', 'Tạo nhà cung cấp') : ''.\Yii::t('app', 'Cập nhật'),
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a(''.\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>