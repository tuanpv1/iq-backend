<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use common\models\User;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
$js = <<<JS
function save_close(){
    jQuery('#close').val(1);
    return false;
}
JS;
$this->registerJs($js, View::POS_END);

?>


<?php $form = ActiveForm::begin([
    'method' => 'post',
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fullSpan' => 12,
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
    <input type="hidden" name="close" id="close" value="0">
    <?php if($model->isNewRecord){ ?>
        <?= $form->field($model, 'username')->textInput(['placeholder' => ''.\Yii::t('app', 'Tài khoản'),'maxlength' => 20]) ?>
        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email','maxlength' => 100]) ?>
        <?= $form->field($model, 'fullname')->textInput(['placeholder' => ''.\Yii::t('app', 'Họ tên'),'maxlength' => 100]) ?>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => ''.\Yii::t('app', 'Nhập mật khẩu có độ dài  tối thiểu 8 kí tự')]) ?>
        <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => ''.\Yii::t('app', 'Nhập lại mật khẩu')]) ?>

    <?php }else{ ?>
        <?= $form->field($model, 'username')->textInput(['readonly'=>true]) ?>
        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email','maxlength' => 100]) ?>
        <?= $form->field($model, 'fullname')->textInput(['placeholder' => ''.\Yii::t('app', 'Họ tên'),'maxlength' => 100]) ?>
<!--        Nếu là chính nó thì không cho thay đổi trạng thái-->
        <?php if($model->id != Yii::$app->user->getId()){ ?>
            <?= $form->field($model, 'status')->dropDownList(User::listStatus()) ?>
        <?php } ?>
    <?php } ?>
</div>

<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
<!--            --><?php //Html::submitButton($model->isNewRecord ? 'Create and Close' : 'Update and Close',['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary', 'onclick' => 'save_close()']) ?>
            <?= Html::submitButton($model->isNewRecord ? ''.\Yii::t('app', 'Thêm mới') : ''.\Yii::t('app', 'Cập nhật'),['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a(''.\Yii::t('app', 'Hủy thao tác'), ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>


