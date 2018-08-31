<?php

use common\models\ParamAttribute;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ParamAttributeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = \Yii::t('app', 'Quản lý cấu hình ');
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="row">
        <div class="col-md-12">
            <div class="portlet light">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-cogs font-green-sharp"></i>
                        <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Quản lý '); ?></span>
                    </div>
                    <div class="tools">
                        <a href="javascript:;" class="collapse">
                        </a>
                    </div>
                </div>
                <div class="portlet-body">
                    <p>
                        <?php if(!Yii::$app->params['tvod1Only']) echo Html::a(\Yii::t('app', "Tạo "), Yii::$app->urlManager->createUrl(['/param-attribute/create']), ['class' => 'btn btn-success']) ?>
                    </p>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'id'=>'grid-param-attribute-id',
                        'filterModel' => $searchModel,
                        'responsive' => true,
                        'pjax' => true,
                        'hover' => true,
                        'columns' => [
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'display_name',
                                'label' => \Yii::t('app', 'Tên danh mục con'),
                                'value'=>function ($model, $key, $index, $widget) {
                                    /** @var $model \common\models\ParamAttribute */
                                    return $model->display_name;
                                },
                            ],
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'param',
                                'label' => \Yii::t('app', 'Đường dẫn'),
                                'value'=>function ($model, $key, $index, $widget) {
                                    /** @var $model \common\models\ParamAttribute */
                                    return $model->param;
                                },
                            ],
                            [
                                'class' => 'kartik\grid\EditableColumn',
                                'attribute' => 'status',
                                'refreshGrid' => true,
                                'editableOptions' => function ($model, $key, $index) {
                                    return [
                                        'header' => \Yii::t('app', 'Trạng thái'),
                                        'size' => 'md',
                                        'displayValueConfig' => $model->listStatus,
                                        'inputType' => \kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                                        'data' => $model->listStatus,
                                        'placement' => \kartik\popover\PopoverX::ALIGN_LEFT,
                                        'formOptions' => [
                                            'action' => ['param-attribute/update-status', 'id' => $model->id]
                                        ],
                                    ];
                                },
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => ParamAttribute::getListStatus(),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => \Yii::t('app', 'Tất cả')],
                            ],
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'type',
                                'width' => '200px',
                                'label' => 'Loại danh mục',
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => \common\models\ParamAttribute::getListType(),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => \Yii::t('app', 'Tất cả')],
                                'value' => function ($model, $key, $index) {
                                    /** @var $model \common\models\ParamAttribute */
                                    return $model->getTypeName($model->type);
                                }
                            ],
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'type_app',
                                'width' => '200px',
                                'label' => 'Loại app',
                                'filterType' => GridView::FILTER_SELECT2,
                                'filter' => \common\models\ParamAttribute::getListTypeApp(),
                                'filterWidgetOptions' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'filterInputOptions' => ['placeholder' => \Yii::t('app', 'Tất cả')],
                                'value' => function ($model, $key, $index) {
                                    /** @var $model \common\models\ParamAttribute */
                                    return $model->getTypeAppName($model->type_app);
                                }
                            ],
                            [
                                'class' => 'kartik\grid\ActionColumn',
                                'template' => '{update}{delete}',
                                'buttons'=> [
                                    'delete' => function ($url, $model) {
                                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', Yii::$app->urlManager->createUrl(['param-attribute/delete','id'=>$model->id]), [
                                            'title' => Yii::t('yii', 'Delete'),
                                            'data-confirm' => Yii::t('yii', 'Bạn có chắc chắn xóa mục này?'),
                                            'data-method' => 'post',
                                            'data-pjax' => '0',
                                        ]);
                                    }
                                ],
//                            'dropdown' => true,
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>


