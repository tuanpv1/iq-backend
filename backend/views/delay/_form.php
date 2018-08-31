<?php

use common\models\Delay;
use common\models\Site;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Delay */
/* @var $form yii\widgets\ActiveForm */
?>


                <div class="form-body">
                    <?php $form = ActiveForm::begin([
                        'type' => ActiveForm::TYPE_HORIZONTAL,
                        //'enableAjaxValidation' => true,
                        'enableClientValidation' => true,
                    ]); ?>

                    <?= $form->field($model, 'site_id')->dropDownList(ArrayHelper::map(Site::findAll(['status'=>Site::STATUS_ACTIVE]),'id','name')) ?>

                    <?= $form->field($model, 'delay')->textInput() ?>

                    <?= $form->field($model, 'status')->dropDownList(Delay::listStatus()) ?>

                    <div class="form-group" style="padding-left: 50px">
                        <?= Html::submitButton($model->isNewRecord ? Yii::t('app','Tạo') : Yii::t('app','Cập nhật'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
