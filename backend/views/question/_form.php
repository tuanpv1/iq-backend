<?php

use common\models\Program;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Question */
/* @var $form yii\widgets\ActiveForm */

?>

<div class="form-body">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'fullSpan' => 12,
        'formConfig' => [
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'showLabels' => true,
            'labelSpan' => 2,
            'deviceSize' => ActiveForm::SIZE_SMALL,
        ],
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]); ?>

    <?= $form->field($model, 'program_id')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(
                Program::find()
                    ->andWhere(['status' => Program::STATUS_ACTIVE])
                    ->all(), 'id', 'name'),
            'options' => ['placeholder' => 'Chọn chương trình'],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]
    ) ?>

    <?= $form->field($model, 'question')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\Question::getListStatus()) ?>
    <?= $form->field($model, 'level')->textInput() ?>


    <div class="col-md-12" id="answer_input">
        <?php for ($i = 1; $i <= $model->number_answers; $i++) { ?>
            <div class="col-md-8">
                <?= $form->field($model, 'answers[' . $i . ']')->textInput(); ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'is_correct[' . $i . ']')->checkbox(); ?>
            </div>
        <?php } ?>
    </div>

    <div class="form-group text-center">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
