<?php

use common\assets\ToastAsset;
use common\models\Site;
use kartik\form\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = \Yii::t('app', 'Release Static Data');
$this->params['breadcrumbs'][] = $this->title;
?>
<!--<div class="content-attribute-index portlet light">-->
<div class="portlet light bg-inverse">
    <div class="row">
        <div class="col-md-3">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
    </div>

    <?php
    $form = ActiveForm::begin([
        'method' => 'post',
        'action' => ['content/export-data-to-file'],
//            'formConfig' => [
//                'type' => ActiveForm::TYPE_INLINE,
//                'labelSpan' => 3,
//                'deviceSize' => ActiveForm::SIZE_SMALL,
//            ],
    ]);
    $formId = $form->id;
    ?>

    <div class="row">
        <div class="col-md-3">
            <div class="control-label">
                <?= Html::label(\Yii::t('app', "Nhà cung cấp dịch vụ")) ;?>
            </div>
            <?= Html::dropDownList('site_id',$site_id, Site::listSite(), ['id'=>'site-id','class'=> 'form-control']); ?>
        </div>
        <div class="col-md-9">
            <div class="control-label">
                <?= Html::label("&nbsp") ;?>
            </div>
            <?= Html::submitButton(\Yii::t('app', "Xuất dữ liệu"), ['class' => 'btn btn-danger','data-confirm' =>\api\helpers\Message::getConfirmMessage()]) ?>
        </div>
    </div>
    <?php $form->end();?>


    <div class="row">
        <div class="col-md-12">
            <div class="note note-warning" style="margin-top: 10px;">
<!--                <h4 class="block">Thông báo</h4>-->
                <p> - <?= \Yii::t('app', 'Click Xuất dữ liệu để xuất dữ liệu ra file local'); ?></p>
<!--                <p> - B2: Click UpdateVersionKaraokeAPI để tăng version API</p>-->
                <p><?= \Yii::t('app', 'Đây là chức năng quan trọng dùng để release dữ liệu mới cho app Karaoke. Việc export file mất thời gian vui lòng đợi thông báo sau khi thực hiện'); ?></p>
            </div>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                //            'filterModel' => $searchModel,
                'id'=>'grid-content-id',
                'responsive' => true,
                'pjax' => true,
                'hover' => true,
                'columns' => [
                    [
                        'class' => '\kartik\grid\DataColumn',
                        'attribute' => 'site_id',
                        'value' => function ($model, $key, $index, $widget) {
                            /**
                             * @var $model \common\models\ApiVersion
                             */
                            return $model->site->name;
                        },
                    ],
//                    [
//                        'class' => '\kartik\grid\DataColumn',
//                        'attribute' => 'name',
//                    ],

                    [
                        'class' => '\kartik\grid\DataColumn',
                        'attribute' => 'version',
                    ],

                    [
                        'class' => 'kartik\grid\DataColumn', // can be omitted, default
                        'attribute' => 'type',
                        'value' => function ($model, $key, $index, $widget) {
                            /**
                             * @var $model \common\models\ApiVersion
                             */
                            return $model->getTypeName();
                        },
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => function ($model, $key, $index, $widget) {
                            /**
                             * @var $model \common\models\ApiVersion
                             */
                            return date('d/m/Y H:i:s',$model->updated_at) ;
                        },
                    ],
                    [
                        'class' => 'kartik\grid\ActionColumn',
                        'header' => \Yii::t('app', 'Tác động'),
                        'template' => '{up}',
                        'buttons' => [
                            'up' => function ($url, $model, $key){

                                return Html::a(\Yii::t('app', 'Tăng version'), ['update-version-api','type'=>$model->type, 'site_id'=>$model->site_id], ['class' => 'btn btn-danger','data-confirm' => \api\helpers\Message::getConfirmMessage()]);
                            }
                        ]
//                            'dropdown' => true,
                    ],
                ],
            ]); ?>
        </div>
    </div>


</div>
