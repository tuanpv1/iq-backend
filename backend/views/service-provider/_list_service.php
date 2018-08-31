<?php

use common\models\Service;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ServiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ''.\Yii::t('app', 'Danh sách gói cước');
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Quản lý gói cước') ?></span>
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
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'name',
                            'format' => 'html',
                            'value'=>function ($model, $key, $index, $widget) {
                                return '<a href = "'.\yii\helpers\Url::to(['service/view', 'id' => $model->id]).'">'.$model->name.'</a>';
                            },
                        ],
                        'display_name',
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'period',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model Service */
                                return intval($model->period).' n'.\Yii::t('app', 'ngày');
                            },
                        ],
                        [
                            'attribute' => 'auto_renew',
                            'class' => '\kartik\grid\DataColumn',
                            'width'=>'300px',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\Service
                                 */
                                return $model->auto_renew?Service::serviceAutorenew()[$model->auto_renew]:'';
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => Service::serviceAutorenew(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\Site
                                 */
                                return $model->created_at ? date('d/m/Y', $model->created_at) : '';
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'class' => '\kartik\grid\DataColumn',
                            'width'=>'200px',
                            'format' => 'html',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\Service
                                 */
                                $tempService = $model->tempService;
                                $value = '<span class="label label-'.$model->getStatusClassCss().'">'.Service::getListStatusServiceNameByStatus($model->status).'</span>';
                                if($tempService){
                                    $value .= '<span class="label label-'.$tempService->getStatusClassCss().'"> '.\Yii::t('app', 'Bản nháp').' -'.Service::getListStatusServiceNameByStatus($tempService->status).'</span>';
                                }
                                return $value;
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => Service::getListStatusService(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
