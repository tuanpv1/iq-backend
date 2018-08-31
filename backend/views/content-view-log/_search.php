<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ContentViewLogSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="content-view-log-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'subscriber_id') ?>

    <?= $form->field($model, 'content_id') ?>

    <?= $form->field($model, 'msisdn') ?>

    <?= $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'ip_address') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'type') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'user_agent') ?>

    <?php // echo $form->field($model, 'channel') ?>

    <?php // echo $form->field($model, 'site_id') ?>

    <?php // echo $form->field($model, 'started_at') ?>

    <?php // echo $form->field($model, 'stopped_at') ?>

    <?php // echo $form->field($model, 'view_date') ?>

    <div class="form-group">
        <?= Html::submitButton(\Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(\Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
