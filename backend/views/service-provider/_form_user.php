<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
if($model->isNewRecord){
    $action_url = \yii\helpers\Url::to(['service-provider/create-user','active' => 2]);
}else {
    $action_url = \yii\helpers\Url::to(['service-provider/update-user','active' => 2, 'id' => $model->id]);
}
?>


<?php $form = ActiveForm::begin([
    'method' => 'post',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fullSpan' => 12,
    'action' => $action_url,
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'showLabels' => true,
        'labelSpan' => 2,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]);
$formId = $form->id;
?>
<div class="form-body">
    <?php if($model->isNewRecord){ ?>
        <?= $form->field($model, 'username')->textInput(['placeholder' => ''.\Yii::t('app', 'Tài khoản'),'maxlength' => 20]) ?>
        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email','maxlength' => 100]) ?>
        <?= $form->field($model, 'fullname')->textInput(['placeholder' => ''.\Yii::t('app', 'Họ và tên'),'maxlength' => 255]) ?>
        <?= $form->field($model, 'phone_number')->textInput(['placeholder' => ''.\Yii::t('app', 'Số điện thoại'),'maxlength' => 255]) ?>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => ''.\Yii::t('app', 'Nhập mật khẩu có độ dài  tối thiểu 6 kí tự')]) ?>
        <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => ''.\Yii::t('app', 'Nhập lại mật khẩu')]) ?>

    <?php }else{ ?>
        <?= $form->field($model, 'username')->textInput(['readonly'=>true]) ?>
        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email','maxlength' => 100]) ?>
        <?= $form->field($model, 'fullname')->textInput(['placeholder' => ''.\Yii::t('app', 'Họ và tên'),'maxlength' => 255]) ?>
        <?= $form->field($model, 'phone_number')->textInput(['placeholder' => ''.\Yii::t('app', 'Số điện thoại'),'maxlength' => 255]) ?>
        <!--        Nếu là chính nó thì không cho thay đổi trạng thái-->
        <?php if($model->id != Yii::$app->user->getId()){ ?>
            <?= $form->field($model, 'status')->dropDownList(User::listStatus()) ?>
        <?php } ?>
    <?php } ?>
    <?= $form->field($model, 'site_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'type')->hiddenInput()->label(false) ?>
</div>

<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton($model->isNewRecord ? ''.\Yii::t('app', 'Lưu lại') : ''.\Yii::t('app', 'Cập nhật'),['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a(''.\Yii::t('app', 'Quay lại'), ['view', 'id' => $model->site_id, 'active' => 2], ['class' => 'btn btn-default', 'data-dismiss'=> 'modal']) ?>
        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>


