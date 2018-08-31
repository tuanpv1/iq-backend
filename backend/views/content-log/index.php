<?php

use common\models\UserActivity;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ''.\Yii::t('app', 'Content Log');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= $this->title  ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class'=>'kartik\grid\ExpandRowColumn',
                            'width'=>'50px',
                            'value'=>function ($model, $key, $index, $column) {
                                return GridView::ROW_COLLAPSED;
                            },
                            'detail'=>function ($model, $key, $index, $column) {
                                return Yii::$app->controller->renderPartial('_expand-activity-detail', ['model'=>$model]);
                            },
                            'headerOptions'=>['class'=>'kartik-sheet-style'] ,
                            'expandOneOnly'=>true
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'username',
                            'format' => 'html',
                            'value'=>function ($model, $key, $index, $widget) {
                                return Html::a($model->username, null,['class'=>'label label-primary']);
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'ip_address',
                            'width'=>'200px',
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'action',
                            'width'=>'200px',
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'target_type',
                            'width'=>'200px',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\UserActivity */
                                return  UserActivity::actionTargets()[$model->target_type] ;
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => UserActivity::actionTargets(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'target_id',
                            'width'=>'200px',
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'created_at',
                            'width'=>'200px',
                            'filterType' => GridView::FILTER_DATE,
                            'filterWidgetOptions' => [
                                'pluginOptions' => [
                                    'format'     => 'yyyy-mm-dd',
                                    'autoWidget' => true,
                                    'autoclose'  => true,
                                    'todayBtn'   => true,
                                ],
                            ],
                            'value' => function ($model, $key, $index, $widget) {
                                return date('d-m-Y H:i:s', $model->created_at);
                            }
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
