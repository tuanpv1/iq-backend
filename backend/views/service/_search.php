<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ServiceSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="service-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'site_id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'display_name') ?>

    <?= $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'free_download_count') ?>

    <?php // echo $form->field($model, 'free_duration') ?>

    <?php // echo $form->field($model, 'free_view_count') ?>

    <?php // echo $form->field($model, 'free_gift_count') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'period') ?>

    <?php // echo $form->field($model, 'auto_renew') ?>

    <?php // echo $form->field($model, 'free_days') ?>

    <?php // echo $form->field($model, 'max_daily_retry') ?>

    <?php // echo $form->field($model, 'max_day_failure_before_cancel') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
