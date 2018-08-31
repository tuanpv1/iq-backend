<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quản lý group ';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-cogs font-green-sharp"></i>
                        <span class="caption-subject font-green-sharp bold uppercase">Quản lý group</span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse">
                        </a>
                    </div>
                </div>
                <div class="portlet-body">
                    <p>
                        <?php if(!Yii::$app->params['tvod1Only']) echo Html::a("Tạo group ", Yii::$app->urlManager->createUrl(['/kodi-category/create']), ['class' => 'btn btn-success']) ?>
                    </p>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'id'=>'grid-category-id',
//                    'filterModel' => $searchModel,
                        'responsive' => true,
                        'pjax' => true,
                        'hover' => true,
                        'columns' => [
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'display_name',
                                'label' => 'Tên danh mục',
                                'value'=>function ($model, $key, $index, $widget) {
                                    /** @var $model \common\models\KodiCategory */
                                    return $model->display_name;
                                },
                            ],
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'description',
                                'label' => 'Mô tả',
                                'value'=>function ($model, $key, $index, $widget) {
                                    /** @var $model \common\models\KodiCategory */
                                    return $model->description;
                                },
                            ],
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'format'=>'raw',
                                'label'=>'Ảnh đại diện',
                                'attribute' => 'image',
                                'value'=>function ($model, $key, $index, $widget) {
                                    /** @var $model \common\models\KodiCategory */
                                    $cat_image=  Yii::getAlias('@cat_image');
                                    return $model->image ? Html::img('@web/'.$cat_image.'/'.$model->image, ['alt' => 'Thumbnail','width'=>'50','height'=>'50']) : '';
                                },
                            ],
                            [
                                'class' => 'kartik\grid\EditableColumn',
                                'attribute' => 'status',
                                'label'=>'Trạng thái',
                                'refreshGrid' => true,
                                'editableOptions' => function ($model, $key, $index) {
                                    return [
                                        'header' => 'Trạng thái',
                                        'size' => 'md',
                                        'displayValueConfig' => $model->listStatus,
                                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                                        'data' => $model->listStatus,
                                        'placement' => \kartik\popover\PopoverX::ALIGN_LEFT
                                    ];
                                },
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => [0 => 'InActive', 10 => 'Active'],
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => 'Tất cả'],
                            ],
                            [
                                'class' => 'kartik\grid\ActionColumn',
                                'visibleButtons' => [
                                    'delete' => function ($model, $key, $index) {
                                        return $model->status === 10 ? false : true;
                                    }
                                ]
//                            'dropdown' => true,
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

<?php
$urlCategory=Yii::$app->urlManager->createUrl("kodi-category");
Yii::info($urlCategory);
$js=<<<JS

function moveCategory(urlType,id) {
    var url;
    switch (urlType) {
        case 1:
            url = "move-up";
            break;
        case 2:
            url = "move-down";
            break;
        case 3:
            url = "move-back";
            break;
        case 4:
            url = "move-forward";
            break;
    }
    $.ajax({

        type:'GET',
        url: '{$urlCategory}'+'/'+ url,

        data: {'id':id},
        success:function(data) {
            $.pjax.reload({container:'#grid-category-id'});

        }
    });
}
JS;
$this->registerJs($js,$this::POS_HEAD);
