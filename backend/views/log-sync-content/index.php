<?php

use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel common\models\LogSyncContentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Lịch sử phân phối nội dung');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= Yii::t('app', 'Lịch sử phân phối nội dung') ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <div style="margin: 25px 0 25px 0">
                    <?= $this->render('_search', [
                        'model' => $model,
                    ]) ?>
                </div>
                <br><br>
                <br><br>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'id' => 'grid-category-id',
                    'filterModel' => $searchModel,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\SerialColumn',
                            'header' => 'STT',
                            'width' => '5%'
                        ],
                        [
                            'format' => 'raw',
                            'width' => '15%',
                            'label' => Yii::t('app', 'Tên nội dung'),
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'content_id',
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\LogSyncContent */
                                return Html::a(\common\models\Content::findOne($model->content_id)->display_name, ['content/view', 'id' => $model->content_id], ['class' => 'label label-primary']);

                            },
                        ],
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'site_id',
                            'label' => Yii::t('app', 'Thị trường'),
                            'width' => '15%',
                            'format' => 'html',
                            'value' => function ($model) {
                                /* @var $model \common\models\LogSyncContent */
                                return $model->getSiteName($model->site_id);
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => \common\models\LogSyncContent::listSite(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'cp',
                            'label' => Yii::t('app', 'CP'),
                            'width' => '15%',
                            'format' => 'html',
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => \common\models\LogSyncContent::getListCp(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'type',
                            'label' => Yii::t('app', 'Thể loại'),
                            'width' => '15%',
                            'format' => 'html',
                            'value' => function ($model) {
                                /* @var $model \common\models\LogSyncContent */
                                return $model->getTypeName(\common\models\Content::findOne($model->content_id)->type);
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => \common\models\LogSyncContent::listType(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'sync_status',
                            'label' => Yii::t('app', 'Trạng thái phân phối'),
                            'format' => 'html',
                            'value' => function ($model) {
                                /* @var $model \common\models\LogSyncContent */
                                $model1 = \common\models\Content::findOne(['id' => $model->content_id]);
                                if ($model1->type != \common\models\Content::TYPE_NEWS && $model1->type != \common\models\Content::TYPE_LIVE) {
                                    return $model->getStatusName();
                                }
                                return '';
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => \common\models\LogSyncContent::$_status,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'class' => 'kartik\grid\DataColumn',
                            'attribute' => 'content_status',
                            'label' => Yii::t('app', 'Trạng thái nội dung'),
                            'format' => 'html',
                            'value' => function ($model) {
                                /* @var $model \common\models\LogSyncContent */
                                return $model->getContentName();
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => \common\models\LogSyncContent::$_content,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => Yii::t('app', 'Tất cả')],
                        ],
                        [
                            'format' => 'raw',
                            'width' => '15%',
                            'label' => Yii::t('app', 'Số lần retry'),
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'retry',
                        ],
                        [
                            'format' => 'raw',
                            'width' => '15%',
                            'label' => Yii::t('app', 'Đường dẫn'),
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'link',
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\LogSyncContent */
                                $model1 = \common\models\Content::findOne(['id' => $model->content_id]);
                                if ($model1->type != \common\models\Content::TYPE_NEWS && $model1->type != \common\models\Content::TYPE_LIVE) {
                                    if ($model->content_status == \common\models\LogSyncContent::CONTENT_STATUS_NO_PROFILE) {
                                        $link = \yii\helpers\Url::toRoute(['content/view', 'id' => $model->content_id, 'active' => 8]);
                                        return Html::a($link, ['content/view', 'id' => $model->content_id, 'active' => 8], ['class' => 'label label-primary', 'target' => '_blank']);
                                    } else {
                                        $link = \yii\helpers\Url::toRoute(['content/view', 'id' => $model->content_id, 'active' => 7]);
                                        return Html::a($link, ['content/view', 'id' => $model->content_id, 'active' => 7], ['class' => 'label label-primary']);
                                    }
                                }

                            },
                        ],
//                        [
//                            'class' => 'kartik\grid\ActionColumn',
//                            'header' => 'Tác động',
//                            'template' => '{transfer}',
//                            'buttons' => [
//                                'transfer' => function ($url, $model) {
//                                    $log = \common\models\LogSyncContent::findOne($model->id);
//                                    if ($log->sync_status != \common\models\ContentSiteAsm::STATUS_ACTIVE) {
//                                        return Html::a('<span class="glyphicon glyphicon-star"></span>', Yii::$app->urlManager->createUrl(['log-sync-content/add-content-to-site', 'id' => $model->id]), [
//                                            'title' => Yii::t('yii', 'Phân phối'),
//                                            'data-confirm' => Yii::t('yii', 'Bạn có muốn phân phối lại?'),
//                                            'data-method' => 'post',
//                                            'data-pjax' => '0',
//                                        ]);
//                                    } else {
//                                        return false;
//                                    }
//                                }
//                            ],
//
////                            'dropdown' => true,
//                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>