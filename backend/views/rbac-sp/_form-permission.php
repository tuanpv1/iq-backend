<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AuthItem */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput() ?>
    <?= $form->field($model, 'description')->textInput() ?>

    <?= $form->field($model, 'data')->textInput() ?>
    <?= $form->field($model, 'rule_name')->dropDownList(\common\models\AuthRule::getAllRule()) ?>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? ''.\Yii::t('app', 'Tạo') : ''.\Yii::t('app', 'Cập nhật'),
            ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>