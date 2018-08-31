<?php

use common\models\ServiceProvider;
use kartik\widgets\ActiveForm;
use kartik\widgets\FileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Site */
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
        <?= $form->field($model, 'name')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 6, 'class' => 'input-circle']) ?>
        <?= $form->field($model, 'website')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
        <?= $form->field($model, 'cp_revernue_percent',['addon' => [
            'append' => ['content'=>'%']]])->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
        <?= $form->field($model, 'currency')->dropDownList(\common\models\Currency::$currencies) ?>
        <?= $form->field($model, 'status')->dropDownList(\common\models\Site::getListStatus()) ?>

    </div>
    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?= Html::submitButton($model->isNewRecord ? ''.\Yii::t('app', 'Tạo Thị trường') : ''.\Yii::t('app', 'Cập nhật'),
                    ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a(''.\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>