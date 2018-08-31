<?php

use common\models\PriceCard;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\PriceCardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $site common\models\Site */

$this->title = Yii::t("app","Danh sách mức nạp tiền");
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?=Yii::t("app","Quản lý mức nạp")?></span>
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
                    'pjax' => false,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => 'STT',
                        ],
                        [

                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'price',
//                            'width'=>'20%',
                            'value'=>function ($model, $key, $index, $widget){
                                /** @var $model PriceCard */
                                /** @var $site \common\models\Site */
                                return number_format($model->price,0,",",".").' '.PriceCard::getCurrency($model->site_id);
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'site_id',
                            'width' => '20%',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => PriceCard::getListSp(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => \Yii::t('app', 'Tất cả')],
                            'value' => function ($model, $key, $index) {
                                /** @var $model \common\models\PriceCard */
                                return $model->getNameSP($model->site_id);
                            }
                        ],
                        [
                            'attribute' => 'created_at',
                            'width' => '20%',
                            'filterType' => GridView::FILTER_DATE,
                            'filterWidgetOptions' => [
                                'pluginOptions'=>[
                                    'format' => 'dd-mm-yyyy',
                                ]
                            ],
                            'value' => function($model){
                                return date('d-m-Y H:i:s', $model->created_at);
                            }
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'status',
                            'width' => '15%',
                            'format' => 'html',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\PriceCard
                                 */
                                return PriceCard::getListStatusNameByStatus($model->status);
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => PriceCard::getListStatus(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => Yii::t("app","Tất cả")],
                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'width' => '15%',
                            'template' => '{view}',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
