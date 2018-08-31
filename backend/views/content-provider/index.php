<?php

use common\models\ContentProvider;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \common\models\ContentProviderSearch */

$this->title = '' . \Yii::t('app', 'Quản lý Nhà cung cấp nội dung');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                            class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Quản lý Nhà cung cấp nội dung') ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= Html::a("" . \Yii::t('app', 'Tạo mới CP'),
                        Yii::$app->urlManager->createUrl(['/content-provider/create']),
                        ['class' => 'btn btn-success']) ?>
                </p>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsive' => true,
//                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'cp_name',
                            'format' => 'html',
                            'value' => function ($model, $key, $index, $widget) {
                                return '<a href = "' . \yii\helpers\Url::to(['view', 'id' => $model->id]) . '">' . $model->cp_name . '</a>';
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'username',
                            'label' => 'Người đại diện',
                            'format' => 'html',
                            'value' => function ($model, $key, $index, $widget) {
                                return \common\models\User::findOne(['cp_id' => $model->id, 'is_admin_cp' => ContentProvider::IS_ADMIN_CP])->username;
                            },
                        ],
                        [
                            'format' => 'raw',
                            'class' => '\kartik\grid\DataColumn',
                            'width' => '15%',
                            'label' => 'Ngày tạo',
                            'filterType' => GridView::FILTER_DATE,
                            'attribute' => 'created_at',
                            'value' => function ($model) {
                                return date('d-m-Y H:i:s', $model->created_at);
                            }
                        ],
                        [
                            'attribute' => 'status',
                            'class' => '\kartik\grid\DataColumn',
                            'width'=>'200px',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\ContentProvider
                                 */
                                return \common\models\ContentProvider::getListStatusNameByStatus($model->status);
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => ContentProvider::getListStatus(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'width' => '200px',
                            'header' => '' . \Yii::t('app', 'Tác động'),
                            'template' => '{view} {update} {in-active} {delete}',
                            'buttons'=>[
                                'view' => function ($url,$model) {
                                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::toRoute(['content-provider/view','id'=>$model->id]), [
                                        'title' => ''.\Yii::t('app', 'Thông tin CP '.$model->cp_name),
                                    ]);

                                },
                                'update' => function ($url,$model) {
                                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Url::toRoute(['content-provider/update','id'=>$model->id]), [
                                        'title' => ''.\Yii::t('app', 'Cập nhật thông tin CP '.$model->cp_name),
                                    ]);
                                },
                                'in-active' => function ($url,$model) {
                                    return Html::a('<span class="glyphicon glyphicon-refresh"></span>',
                                        Url::toRoute([
                                            'content-provider/update-status-button',
                                            'id'=>$model->id,
                                            'status'=>$model->status,
                                        ]), [
                                        'title' => ''.\Yii::t('app', $model->status == ContentProvider::STATUS_ACTIVE?'Tạm dừng '.$model->cp_name:'Kích hoạt '.$model->cp_name),
                                        'data-confirm' => $model->status == ContentProvider::STATUS_ACTIVE?
                                            Yii::t('app', 'Khi tạm dừng CP, tất cả các nội dung thuộc CP sẽ bị tạm dừng, bạn có chắc chắn thực hiện?'):
                                            Yii::t('app', 'Bạn có thực sự muốn kích hoạt lại CP?')
                                    ]);
                                },
                                'delete' => function ($url,$model) {
                                    if($model->id != Yii::$app->user->getId()){
                                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['content-provider/delete','id'=>$model->id]), [
                                            'title' => ''.\Yii::t('app', 'Xóa CP '.$model->cp_name),
                                            'data' => [
                                                'confirm'=>Yii::t('app', 'Bạn chắc chắn muốn xóa CP này?'),
                                                'method' => 'post',
                                            ]
                                        ]);
                                    }
                                }
                            ]
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
