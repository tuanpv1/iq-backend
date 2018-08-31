<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ContentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="content-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'display_name') ?>

    <?= $form->field($model, 'ascii_name') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'tags') ?>

    <?php // echo $form->field($model, 'short_description') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'content') ?>

    <?php // echo $form->field($model, 'duration') ?>

    <?php // echo $form->field($model, 'urls') ?>

    <?php // echo $form->field($model, 'version_code') ?>

    <?php // echo $form->field($model, 'version') ?>

    <?php // echo $form->field($model, 'view_count') ?>

    <?php // echo $form->field($model, 'download_count') ?>

    <?php // echo $form->field($model, 'like_count') ?>

    <?php // echo $form->field($model, 'dislike_count') ?>

    <?php // echo $form->field($model, 'rating') ?>

    <?php // echo $form->field($model, 'rating_count') ?>

    <?php // echo $form->field($model, 'comment_count') ?>

    <?php // echo $form->field($model, 'favorite_count') ?>

    <?php // echo $form->field($model, 'is_free') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'price_download') ?>

    <?php // echo $form->field($model, 'price_gift') ?>

    <?php // echo $form->field($model, 'images') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'honor') ?>

    <?php // echo $form->field($model, 'content_provider_id') ?>

    <?php // echo $form->field($model, 'approved_at') ?>

    <?php // echo $form->field($model, 'site_id') ?>

    <?php // echo $form->field($model, 'admin_note') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
