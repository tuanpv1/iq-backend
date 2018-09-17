<?php

use common\models\ApiCredential;
use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ApiCredential */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t("app","Dánh sách API KEY");
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?=Yii::t("app","Danh sách API KEY")?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= Html::a(Yii::t("app","Tạo client API Key") ,
                        Yii::$app->urlManager->createUrl(['/credential/create']),
                        ['class' => 'btn btn-success']) ?>
                </p>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'client_name',
                            'format' => 'html',
                            'value'=>function ($model, $key, $index, $widget) {
                                return '<a href = "'.\yii\helpers\Url::to(['view', 'id' => $model->id]).'">'.$model->client_name.'</a>';
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'type',
                            'value'=>function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\ApiCredential */
                                return ApiCredential::$api_key_types[$model->type];
                            },
                            'width'=>'150px',
                            'filterType'          => GridView::FILTER_SELECT2,
                            'filter'              => ApiCredential::getListType(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions'  => ['placeholder' => Yii::t("app","Tất cả")],
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'client_api_key',
                        ],
                        [
                            'class'               => '\kartik\grid\DataColumn',
                            'attribute'           => 'created_at',
                            'filterType'          => GridView::FILTER_DATE,
                            'filterWidgetOptions' => [
                                'pluginOptions' => [
                                    'format'     => 'dd-mm-yyyy',
                                    'autoWidget' => true,
                                    'autoclose'  => true,
                                ],
                            ],
                            'width'               => '200px',
                            'value'               => function ($model, $key, $index, $widget) {
                                return date('H:i:s d-m-Y', $model->created_at);
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'class' => '\kartik\grid\DataColumn',
                            'width'=>'200px',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\ApiCredential
                                 */
                                return ApiCredential::getListStatusNameByStatus($model->status);
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => ApiCredential::getListStatus(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "Tất cả"],
                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'template' => '{update} {delete} {view}',
//                            'dropdown' => true,
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
