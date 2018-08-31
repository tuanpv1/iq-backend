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
    'fullSpan' => 12,
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'labelSpan' => 3,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]); ?>
    <div class="form-body">
        <h3 class="form-section"><?= \Yii::t('app', 'Thông tin nhà cung cấp dịch vụ') ?></h3>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'input-circle']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'description')->textarea(['rows' => 6, 'class' => 'input-circle']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'website')->textInput(['maxlength' => true, 'class' => 'input-circle']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'currency')->dropDownList(\common\models\Currency::$currencies) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'cp_revernue_percent',['addon' => [
                    'append' => ['content'=>'%']]])->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'status')->dropDownList(\common\models\Site::getListStatus()) ?>
            </div>
        </div>

        <h3 class="form-section"><?= \Yii::t('app', 'Thông tin người quản trị') ?></h3>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'username')->textInput(['placeholder' => ''.\Yii::t('app', 'Tài khoản'),'maxlength' => true, 'class' => 'input-circle']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => ''.\Yii::t('app', 'Nhập mật khẩu có độ dài  tối thiểu 8 ký tự'), 'class' => 'input-circle']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email','maxlength' => true, 'class' => 'input-circle']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => ''.\Yii::t('app', 'Xác nhận mật khẩu'), 'class' => 'input-circle']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'phone_number')->textInput(['placeholder' => ''.\Yii::t('app', 'Số điện thoại'),'maxlength' => true, 'class' => 'input-circle']) ?>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?= Html::submitButton(''.\Yii::t('app', 'Tạo nhà cung cấp'),
                    ['class' => 'btn btn-success']) ?>
                <?= Html::a(''.\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>