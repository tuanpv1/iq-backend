<?php

use common\models\Service;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Service */
/* @var $sp_id int */
/* @var $model common\models\Service */
/* @var $type int */
/* @var $selectedCats Array */
/* @var $readOnly Int */

$treeID = 'music_category_tree_'.$model->id;
$buttonID = 'update_cate_music_'.$model->id;

if ($readOnly) {
    $js = <<<JS
    function handleMusicCategoryUpdate{$treeID}(e){
        e.preventDefault();

        var
            link = $(e.target),
            callUrl = link.attr('href'),
            catSelected = jQuery("#{$treeID}").jstree("get_selected"),
            ajaxRequest;

        ajaxRequest = $.ajax({
            method: "POST",
            dataType: "json",
            url: callUrl,
            data: {categories:catSelected, id:$model->id}
        });

        ajaxRequest.done(function( data ) {
          alert(data.message);
        });

        ajaxRequest.fail(function( jqXHR, textStatus ) {
          alert( "Request failed: " + textStatus );
        });

    }
JS;
    $this->registerJs($js, \yii\web\View::POS_END);

    $this->registerJs("$('#{$buttonID}').click(handleMusicCategoryUpdate$treeID);", \yii\web\View::POS_READY);
}

?>

<div class="portlet-body">
    <?= \common\widgets\Jstree::widget([
        'clientOptions' => [
            "checkbox" => ["keep_selected_style" => false],
            "plugins" => ["checkbox"]
        ],
        'type' => $type,
        'id' => $treeID,
        'sp_id' => $sp_id,
        'data' => $selectedCats,
    ]) ?>
</div>
<?php if($readOnly){ ?>
    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <?=
                Html::a(
                    ''.\Yii::t('app', 'Cập nhật'),
                    ['service/update-service-category-asm', 'id' => $model->id, 'type' => $type],
                    array(
                        'class' => 'btn btn-primary',
                        'id' => $buttonID,
                    )); ?>
            </div>
        </div>
    </div>
<?php } ?>
