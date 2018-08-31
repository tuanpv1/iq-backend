<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ServiceGroup */
/* @var $form yii\widgets\ActiveForm */
$showPreview = !$model->isNewRecord && !empty($model->icon);
?>



<?php $form = ActiveForm::begin([
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'fullSpan' => 8,
    'options' => ['enctype' => 'multipart/form-data'],
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'labelSpan' => 3,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
    'enableAjaxValidation' => false,
    'enableClientValidation' => true,
]); ?>
<div class="form-body">
    <?= $form->field($model, 'name')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => 200, 'class' => 'input-circle']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 5]) ?>

    <?= $form->field($model, 'status')->dropDownList(\common\models\ServiceGroup::getListStautus()) ?>
    <?= $form->field($model, 'type')->dropDownList(\common\models\Category::getListType()) ?>
    <?= $form->field($model, 'site_id')->hiddenInput()->label(false) ?>
    <?php if ($showPreview) { ?>
        <div class="form-group field-category-icon">
            <div class="col-sm-offset-3 col-sm-5">
                <?php echo Html::img($model->getImageLink(), ['class' => 'file-preview-image']) ?>
            </div>
        </div>
    <?php } ?>

    <?= $form->field($model, 'icon')->widget(\kartik\widgets\FileInput::classname(), [
        'options' => ['multiple' => true, 'accept' => 'image/*'],
        'pluginOptions' => [
            'previewFileType' => 'image',
            'showUpload' => false,

        ]
    ]); ?>
    <div class="well well-lg">
        <div class="row">
            <?= $form->field($model,'list_service_id')->checkboxList(\common\models\ServiceGroup::getCheckBoxListService($model->site_id))?>
        </div>
    </div>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? ''.\Yii::t('app', 'Create') : ''.\Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

</div>
<?php ActiveForm::end(); ?>

