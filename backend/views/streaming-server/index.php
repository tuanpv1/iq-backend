<?php

use common\models\Site;
use common\models\StreamingServer;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StreamingServerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Quản lý địa chỉ phân phối nội dung');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Quản lý địa chỉ phân phối nội dung') ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">

                <?= Html::a(Yii::t('app','Tạo địa chỉ phân phối nội dung'),
                    Yii::$app->urlManager->createUrl(['/streaming-server/create']),
                    ['class' => 'btn btn-success']) ?>
                <br>
                <br>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            //                'vAlign' => 'middle',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\StreamingServer
                                 */
                                $action = "streaming-server/view";
                                $res = Html::a('<kbd>' . $model->name . '</kbd>', [$action, 'id' => $model->id]);
                                return $res;

                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'site_ids',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\StreamingServer
                                 */
                                return $model->getSiteNames();
                            },
                            'filter' => ArrayHelper::map(Site::getSiteList(), "id", "name"),
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                        'ip',
                        'host',
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'status',
                            'width' => '120px',
                            'format' => 'raw',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\StreamingServer
                                 */
                                if ($model->status == StreamingServer::STATUS_ACTIVE) {
                                    return '<span class="label label-success">' . $model->getStatusName() . '</span>';
                                } else {
                                    return '<span class="label label-danger">' . $model->getStatusName() . '</span>';
                                }

                            },
                            'filter' => StreamingServer::listStatus(),
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'header' => ''.\Yii::t('app', 'Tác động'),
                            'template' => '{view}  {update} {customDelete}',
                            'buttons' => [
                                /**
                                 * @var $model \common\models\StreamingServer
                                 */
                                'customDelete' => function ($url, $model) {
                                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['delete', 'id' => $model->id],
                                        [
                                            'data' => [
                                                'confirm' => Yii::t('app', 'Xóa địa chỉ phân phối nội dung này sẽ ảnh hưởng đến việc phân phối nội dung đến nhà cung cấp dịch vụ tương ứng. Bạn có chắc chắn muốn xóa địa chỉ phân phối nội dung này?'),
                                                'method' => 'post',
                                                'pjax' => '0',
                                            ],
                                        ]);
                                },
                            ],
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>
