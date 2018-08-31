<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ContentViewLog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="content-view-log-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subscriber_id')->textInput() ?>

    <?= $form->field($model, 'content_id')->textInput() ?>

    <?= $form->field($model, 'msisdn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'ip_address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'type')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'user_agent')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'channel')->textInput() ?>

    <?= $form->field($model, 'site_id')->textInput() ?>

    <?= $form->field($model, 'started_at')->textInput() ?>

    <?= $form->field($model, 'stopped_at')->textInput() ?>

    <?= $form->field($model, 'view_date')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? \Yii::t('app', 'Create') : \Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
