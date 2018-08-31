<?php

use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\MultilanguageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quản lý ngôn ngữ hệ thống';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase">Quản lý ngôn ngữ hệ thống</span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?php if(!Yii::$app->params['tvod1Only']) echo Html::a("Tạo mới ngôn ngữ ", Yii::$app->urlManager->createUrl(['/multilanguage/create']), ['class' => 'btn btn-success']) ?>
                </p>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'id'=>'grid-category-id',
                    'filterModel' => $searchModel,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'name',
                            'label' => 'Tên ngôn ngữ',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\Multilanguage */
                                return $model->name;
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'format'=>'raw',
                            'label'=>'Ảnh đại diện',
                            'attribute' => 'image',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\Multilanguage */
                                $cat_image=  Yii::getAlias('@cat_image');
                                return $model->image ? Html::img('@web/'.$cat_image.'/'.$model->image, ['alt' => 'Thumbnail','width'=>'50','height'=>'50']) : '';
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'code',
                            'label' => 'Mã',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\Multilanguage */
                                return $model->code;
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'description',
                            'label' => 'Mô tả',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\Multilanguage */
                                return \common\helpers\CUtils::subString($model->description,20);
                            },
                        ],

                        [
                            'class' => 'kartik\grid\EditableColumn',
                            'attribute' => 'status',
                            'label'=>'Trạng thái',
                            'format' => 'html',
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
                            'filter' => [0 => 'Tạm dừng', 10 => 'Kích hoạt'],
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => 'Tất cả'],
                        ],

                        [
                            'class' => 'kartik\grid\ActionColumn',
//                            'dropdown' => true,
                            'visibleButtons' => [
                                'delete' => function ($model, $key, $index) {
                                    return $model->status === 10 ? false : true;
                                }
                            ]
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>