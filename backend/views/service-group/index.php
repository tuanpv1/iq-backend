<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ServiceGroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ''.\Yii::t('app', 'Service Groups');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Danh sách nhóm gói cước') ?></span>
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
                            'label' => ''.\Yii::t('app', 'Mã nhóm gói cước'),
                            'format' => 'html',
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\ServiceGroup */

                                return Html::a($model->name, ['/service-group/view', 'id' => $model->id], ['class' => 'label label-primary']);
                            },
                        ],
                        [
                            'attribute' => 'display_name',
                            'label' => ''.\Yii::t('app', 'Tên hiển thị')
                        ],
                        [
                            'attribute' => 'description',
                            'label' => ''.\Yii::t('app', 'Mô tả')
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
