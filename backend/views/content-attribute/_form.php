<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use common\models\Category;


/* @var $this yii\web\View */
/* @var $model common\models\ContentAttribute */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="content-attribute-form">

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

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content_type')->dropDownList(Category::getListType()) ?>

    <?= $form->field($model, 'data_type')->dropDownList($model->datatype) ?>

    <div class="form-group">
        <label class="control-label col-sm-3" for="ads-extra">&nbsp;</label>
        <div class="col-sm-5">
            <?= Html::submitButton($model->isNewRecord ? \Yii::t('app', 'Tạo') : \Yii::t('app', 'Cập nhật'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
