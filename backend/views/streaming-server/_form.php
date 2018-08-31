<?php

use common\models\Site;
use common\models\StreamingServer;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StreamingServer */
/* @var $form yii\widgets\ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fullSpan' => 8,
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'labelSpan' => 3,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
//    'enableAjaxValidation' => true,
    'enableClientValidation' => true,
]); ?>
<div class="form-body">
    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?php
    $sites = ArrayHelper::map(Site::getSiteList(), "id", "name");
    echo $form->field($model, 'site_ids')->widget(Select2::classname(), [
        'data' => $sites,
        'options' => ['multiple' => true, 'placeholder' => ''.\Yii::t('app', 'Chọn nhà cung cấp dịch vụ ...')],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ]);
    ?>

    <?= $form->field($model, 'ip')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'port')->textInput() ?>

    <?= $form->field($model, 'host')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList(
        StreamingServer::listStatus(), ['disabled' => $primaryCached]
    ) ?>

    <?= $form->field($model, 'content_path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content_api')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content_status_api')->textInput(['maxlength' => true]) ?>

</div>

<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton($model->isNewRecord ? ''.\Yii::t('app', 'Thêm mới') : ''.\Yii::t('app', 'Cập nhật'),
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a(''.\Yii::t('app', 'Quay lại'), ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>


<?php ActiveForm::end(); ?>

