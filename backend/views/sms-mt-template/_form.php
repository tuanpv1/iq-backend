<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\SmsMtTemplate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="form-body">
    <div class="note note-info">
        <h3><?= \Yii::t('app', 'Danh sách tham số có sẵn') ?></h3>
        <p><?php echo $params;?></p>
    </div>


    <div class="form-group">
        <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'code_name')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'content')->textarea(['rows' => 5]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'params')->textInput(['maxlength' => true]) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'type')->dropDownList(\common\models\SmsMtTemplate::getListType()) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?= $form->field($model, 'status')->dropDownList(\common\models\SmsMtTemplate::getListStatus()) ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>
