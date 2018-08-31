<?php

use kartik\widgets\ActiveForm;
use kartik\widgets\FileInput;
use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\KodiCategory */
/* @var $form yii\widgets\ActiveForm */
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
    'enableClientValidation' => false,
]); ?>
<div class="form-body">

    <?= $form->field($model, 'display_name')->textInput(['maxlength' => 250, 'class' => 'input-circle']) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>



    <?= $form->field($model, 'status')->dropDownList(
        \common\models\KodiCategory::getListStatus(), ['class' => 'input-circle']
    ) ?>

    <div class="row">

        <div class="form-group field-content-price">
            <label class="control-label col-md-2" for="content-price">Group</label>

            <div class="col-md-10">
                <?= \common\widgets\Jstree::widget([
                    'clientOptions' => [
                        "checkbox" => ["keep_selected_style" => false],
                        "plugins" => ["checkbox"]
                    ],
                    'type' => 100,
                    'sp_id' => $site_id,
                    'cp_id' => true,
                    'data' => isset($selectedCats) ? $selectedCats : [],
                    'eventHandles' => [
                        'changed.jstree' => "function(e,data) {
                            jQuery('#list-cat-id').val('');
                            var i, j, r = [];
                            var catIds='';
                            for(i = 0, j = data.selected.length; i < j; i++) {
                                var item = $(\"#\" + data.selected[i]);
                                var value = item.attr(\"id\");
                                if(i==j-1){
                                    catIds += value;
                                } else{
                                    catIds += value +',';

                                }
                            }
                            jQuery(\"#list-cat-id\").val(catIds);
                            console.log(jQuery(\"#list-cat-id\").val());
                         }"
                    ]
                ]) ?>
            </div>
            <div class="col-md-offset-2 col-md-10"></div>
            <div class="col-md-offset-2 col-md-10">
                <div class="help-block"></div>
            </div>
        </div>
    </div>
    <?= $form->field($model, 'list_cat_id')->hiddenInput(['id' => 'list-cat-id'])->label(false) ?>
</div>
<div class="form-actions">
    <div class="row">
        <div class="col-md-offset-3 col-md-9">
            <?= Html::submitButton($model->isNewRecord ? 'Tạo Item' : 'Cập nhật',
                ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
            <?= Html::a('Quay lại', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
