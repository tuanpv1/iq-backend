<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ContentProvider */
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
        <h3 class="form-section"><?= \Yii::t('app', 'Thông tin nhà cung cấp nội dung') ?></h3>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'cp_name')->textInput(['maxlength' => true, 'class' => 'input-circle'])->label('Tên') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'cp_address')->textarea(['rows' => 6, 'class' => 'input-circle']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'cp_mst')->textInput(['maxlength' => true, 'class' => 'input-circle']) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'status')->dropDownList(\common\models\ContentProvider::getListStatus()) ?>
            </div>
        </div>


        <h3 class="form-section"><?= \Yii::t('app', 'Thông tin người đại diện') ?></h3>
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
                <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email','maxlength' => true, 'class' => 'input-circle'])->label('Email') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => ''.\Yii::t('app', 'Xác nhận mật khẩu'), 'class' => 'input-circle']) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'phone_number')->textInput(['placeholder' => ''.\Yii::t('app', 'Số điện thoại'),'maxlength' => true, 'class' => 'input-circle'])->label('Số điện thoại') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'fullname')->textInput(['placeholder' => ''.\Yii::t('app', 'Họ và tên'),'maxlength' => true, 'class' => 'input-circle'])->label('Họ và tên') ?>
            </div>
        </div>
    </div>
    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?= Html::submitButton(isset($isNewRecord) ? ''.\Yii::t('app', 'Tạo nhà cung cấp') : ''.\Yii::t('app', 'Cập nhật'),
                    ['class' => isset($isNewRecord) ? 'btn btn-success' : 'btn btn-primary']) ?>
                <?= Html::a(''.\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>