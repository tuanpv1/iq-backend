<?php

use common\models\Delay;
use common\models\Site;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\DelaySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = \Yii::t('app', 'Độ trễ nội dung theo nhà cung cấp dịch vụ');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= $this->title ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">

            <p>
                <?= Html::a(Yii::t('app','Thêm mới'), ['create'], ['class' => 'btn btn-success']) ?>
            </p>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'site_id',
                        'value' => function($model){
                            /** * @var $model Delay */
                            return  Site::findOne($model->site_id)->name;
                        }
                    ],
                    [
                        'attribute' => 'delay',
                        'value' => function($model){
                            /** * @var $model Delay */
                            return  $model->delay.Yii::t('app',' Giờ');
                        }
                    ],
                    [
                        'attribute' => 'status',
                        'class' => '\kartik\grid\DataColumn',
                        'width'=>'200px',
                        'value' => function ($model, $key, $index, $widget) {
                            /** * @var $model Delay */
                            return Delay::listStatus()[$model->status];
                        },
                        'filterType' => GridView::FILTER_SELECT2,
                        'filter' => Delay::listStatus(),
                        'filterWidgetOptions' => [
                            'pluginOptions' => ['allowClear' => true],
                        ],
                        'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                    ],
                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>
            </div>
        </div>
    </div>
</div>

