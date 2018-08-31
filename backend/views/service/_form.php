<?php

use common\models\Service;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Service */
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
    <?= $form->field($model, 'display_name')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6, 'class' => 'input-circle']) ?>
    <?= $form->field($model, 'price', ['addon' => [
        'append' => ['content' => 'VND']]])->dropDownList(Service::getListPrices()) ?>
    <?= $form->field($model, 'period', ['addon' => [
        'append' => ['content' => 'ngày']]])->textInput(['maxlength' => 200])->hint('' . \Yii::t('app', 'Chu kỳ thực hiện gia hạn của gói cước')) ?>
    <?= $form->field($model, 'max_daily_retry', ['addon' => [
        'append' => ['content' => 'lượt/ngày']]])->textInput(['maxlength' => 200])->hint('' . \Yii::t('app', 'Số lần gia hạn tối đa trong ngày của gói cước')) ?>
    <?= $form->field($model, 'max_day_failure_before_cancel', ['addon' => [
        'append' => ['content' => 'ngày']]])->textInput(['maxlength' => 200])->hint('' . \Yii::t('app', 'Số ngày gia hạn tối đa của gói cước trước khi hủy gói cước của thuê bao')) ?>
    <?= $form->field($model, 'free_days', ['addon' => [
        'append' => ['content' => 'ngày']]])->textInput(['maxlength' => 200])->hint('' . \Yii::t('app', 'Số ngày miễn phí khi đăng ký lần đầu')) ?>
    <?= $form->field($model, 'auto_renew')->dropDownList(Service::serviceAutorenew()) ?>

    <?= $form->field($model, 'site_id')->hiddenInput()->label(false) ?>

</div>
<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton($model->isNewRecord ? '' . \Yii::t('app', 'Tạo nhà cung cấp') : '' . \Yii::t('app', 'Cập nhật'),
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?php
            if ($model->isNewRecord) {
                echo Html::a('' . \Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default']);
            } else {
                $rootService = $model->rootService;
                if ($rootService && $rootService->status > Service::STATUS_INACTIVE) {
                    echo Html::a('' . \Yii::t('app', 'Quay lại'), ['view', 'id' => $rootService->id], ['class' => 'btn btn-default']);
                } else {
                    echo Html::a('' . \Yii::t('app', 'Quay lại'), ['view', 'id' => $model->id], ['class' => 'btn btn-default']);
                }
            }
            ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
