<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ContentRelatedAsm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="content-related-asm-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'content_id')->textInput() ?>

    <?= $form->field($model, 'content_related_id')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
