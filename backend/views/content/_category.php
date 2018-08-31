<?php
use common\assets\ToastAsset;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var $cat_selected array
 * @var $model \common\models\Content
 */
ToastAsset::register($this);
ToastAsset::config($this, [
    'positionClass' => ToastAsset::POSITION_BOTTOM_RIGHT
]);
$formID = 'category-tree-form';
$treeID = 'cat-tree';
$js = <<<JS
// get the form id and set the event
jQuery('#{$formID}').on('beforeSubmit', function(e) {
    \$form = jQuery('#{$formID}');
   $.post(
        \$form.attr("action"), // serialize Yii2 form
        {"categories":jQuery("#{$treeID}").jstree("get_selected")}
    )
        .done(function(result) {
            if(result.success){
                toastr.success(result.message);
            }else{
                toastr.error(result.message);
            }
        })
        .fail(function() {
            toastr.error("server error");
        });
    return false;
}).on('submit', function(e){
    e.preventDefault();
});
JS;
$this->registerJs($js);

?>
<?php

$form = ActiveForm::begin([
    'id' => $formID,
    'type' => ActiveForm::TYPE_HORIZONTAL,
    'action' => ['article/update-category', 'id' => $model->id],
    'fullSpan' => 12,
    'formConfig' => [
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'labelSpan' => 4,
        'deviceSize' => ActiveForm::SIZE_SMALL,
    ],
]); ?>
    <div class="form-body">
        <div class="col-md-offset-3 col-md-9">
            <?= \common\widgets\Jstree::widget([
                'options' => ['id' => $treeID],
                'data' => $cat_selected,
                'clientOptions' => [
                    "checkbox" => ["keep_selected_style" => false],
                    "plugins" => ["checkbox"]
                ],
                'eventHandles' => [
                    'changed.jstree' => 'function(e,data) {
                            var i, j, r = [];
                            for(i = 0, j = data.selected.length; i < j; i++) {
                              var item = $("#" + data.selected[i]);
                              r.push(item.attr("id"));
                            }
                            console.log(r.join(", "));
                         }'
                ]
            ]) ?>
        </div>
    </div>
    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?= Html::submitButton('Cập nhật',
                    ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>
<?php ActiveForm::end(); ?>