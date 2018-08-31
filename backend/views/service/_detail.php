<?php

use common\models\Service;
use common\widgets\BEServiceCycle;
use kartik\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Service */
/* @var $form yii\widgets\ActiveForm */
$modal_suspend = 'modal_suspend_'.$model->id;
$model_note = new \backend\models\AdminNoteForm();
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Thông tin gói cước') ?></span>(<?= '<span class="label label-'.$model->getStatusClassCss().'">'.Service::getListStatusService()[$model->status].'</span>' ?>)
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <div class="caption">
                    <i class="fa fa-comment-o font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Ghi chú gói cước') ?></span>
                </div>
                <div class="well">
                    <?= $model->admin_note ?>
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'name',
                        'display_name',
                        'description:ntext',
                        [
                            'attribute' => 'price',
                            'value' => $model->pricing->priceInfo,
                            'label' => Yii::t('app','Đơn giá / tháng')
                        ],
//                        [
//                            'attribute' => 'period',
//                            'value' => $model->period . ' '.\Yii::t('app', 'ngày')
//                        ],
                        [
                            'attribute' => 'auto_renew',
                            'value' => \common\models\Service::getListAutorenewsServiceName($model->auto_renew)
                        ],
//                        [
//                            'attribute' => 'free_days',
//                            'value' => $model->free_days . ' '.\Yii::t('app', 'ngày')
//                        ],
//                        [
//                            'attribute' => 'max_day_failure_before_cancel',
//                            'value' => $model->max_day_failure_before_cancel . ' '.\Yii::t('app', 'ngày')
//                        ],
//                        [
//                            'attribute' => 'max_daily_retry',
//                            'value' => $model->max_daily_retry . ' '.\Yii::t('app', 'lượt/ngày')
//                        ],
                        // [
                        //     'label' => 'Full type services',
                        //     'value' => $model->getFullTypeServices()
                        // ],
                        [
                            'format' => 'raw',
                            'attribute' => 'status',
                            'value' => '<span class="label label-'.$model->getStatusClassCss().'">'.Service::getListStatusServiceNameByStatus($model->status).'</span>'
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => date('d/m/Y H:i:s',$model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => date('d/m/Y H:i:s',$model->updated_at)
                        ],
                    ],
                ]) ?>
            </div>

        </div>
    </div>
</div>
