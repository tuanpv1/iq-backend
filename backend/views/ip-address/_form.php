<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PriceCard */
/* @var $site common\models\Site */
/* @var $form yii\widgets\ActiveForm */
$showPreview = !$model->isNewRecord && !empty($model->icon);
$js = <<<JS
function isNumberKey(evt)
    {
       var charCode = (evt.which) ? evt.which : event.keyCode;
       if (charCode > 31 && (charCode < 48 || charCode > 57))
          return false;
       return true;
    }
JS;
$this->registerJs($js, \yii\web\View::POS_END);
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
    <?= $form->field($model, 'ip')->textInput(['maxlength' => 45, 'class' => 'form-control']) ?>
    <?= $form->field($model, 'stateprov')->textInput(['maxlength' => 100, 'class' => 'form-control']) ?>
    <?= $form->field($model, 'city')->textInput(['maxlength' => 100, 'class' => 'form-control']) ?>
    <?= $form->field($model, 'country')->textInput(['maxlength' => 100, 'class' => 'form-control']) ?>
</div>
<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton($model->isNewRecord ? Yii::t("app","Lưu") : Yii::t("app","Cập nhật"), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a(Yii::t("app","Quay lại"), ['index'], ['class' => 'btn btn-default']); ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
