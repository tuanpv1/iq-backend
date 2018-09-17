<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ApiCredential */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="service-provider-api-credential-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'site_id') ?>

    <?= $form->field($model, 'client_name') ?>

    <?= $form->field($model, 'type') ?>

    <?= $form->field($model, 'client_api_key') ?>

    <?php // echo $form->field($model, 'client_secret') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'package_name') ?>

    <?php // echo $form->field($model, 'certificate_fingerprint') ?>

    <?php // echo $form->field($model, 'bundle_id') ?>

    <?php // echo $form->field($model, 'appstore_id') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'udpated_at') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t("app","Tìm kiếm"), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t("app","Cài đặt lại"), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
