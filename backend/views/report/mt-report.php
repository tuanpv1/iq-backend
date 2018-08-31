<?php

use common\models\Site;
use common\models\SmsMessage;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\widgets\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $report \backend\models\VoucherReportForm */
/* @var $subscriber_provider_id int */
/* @var $this yii\web\View */

$this->title = Yii::t('app','Báo cáo MT');
$this->params['breadcrumbs'][] = Yii::t('app','Báo cáo MT');
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-body">

                <div class="report-user-daily-index">

                    <div class="row form-group">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="col-md-12 col-md-offset-0">
                                <?php $form = ActiveForm::begin(
                                    ['method' => 'get',
                                        'action' => Url::to(['report/mt']),]
                                ); ?>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="col-md-2">
                                            <?= $form->field($report, 'msisdn')->textInput(); ?>
                                        </div>

                                        <div id="date">
                                            <div class="col-md-2">
                                                <?= $form->field($report, 'from_date')->widget(\kartik\widgets\DatePicker::classname(), [
                                                    'options' => ['placeholder' => ''.\Yii::t('app', 'Ngày bắt đầu')],
                                                    'type' => \kartik\widgets\DatePicker::TYPE_INPUT,
                                                    'pluginOptions' => [
                                                        'autoclose' => true,
                                                        'todayHighlight' => true,
                                                        'format' => 'dd/mm/yyyy'
                                                    ]
                                                ]); ?>

                                            </div>
                                            <div class="col-md-2">
                                                <?= $form->field($report, 'to_date')->widget(\kartik\widgets\DatePicker::classname(), [
                                                    'options' => ['placeholder' => ''.\Yii::t('app', 'Ngày kết thúc')],
                                                    'type' => \kartik\widgets\DatePicker::TYPE_INPUT,
                                                    'pluginOptions' => [
                                                        'autoclose' => true,
                                                        'todayHighlight' => true,
                                                        'format' => 'dd/mm/yyyy'
                                                    ]
                                                ]); ?>
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div style="margin-top: 25px"></div>
                                            <?= \yii\helpers\Html::submitButton(''.\Yii::t('app', 'Xem báo cáo'), ['class' => 'btn btn-primary']) ?>

                                        </div>
                                        <?php ActiveForm::end(); ?>
                                        <?php if ($dataProvider) { ?>
                                        <?php
                                        $gridColumns = [
                                            ['class' => 'kartik\grid\SerialColumn'],
                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'subscriber',
                                                'label' => Yii::t('app','Số điện thoại'),
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\SmsMessage */
                                                    return SmsMessage::getPhone($model->msisdn);
                                                },
                                            ],

                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'sent_at',
                                                'label' => Yii::t('app','Thời gian gửi'),
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\SmsMessage */
                                                    return date('H:i:s d/m/Y',$model->sent_at);
                                                },
                                            ],
                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'message',
                                                'label' => Yii::t('app','Nội dung MT'),
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\SmsMessage */
                                                    return $model->message;
                                                },
                                            ],
                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'status',
                                                'label' => Yii::t('app','Trạng thái'),
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\SmsMessage */
                                                    return $model->getStatusName();
                                                },
                                            ],
                                        ]
                                        ?>
                                        <div class="col-md-2">
                                            <div style="margin-top: 25px"></div>
                                            <?php
                                            echo $expMenu = ExportMenu::widget([
                                                'dataProvider' => $dataProvider,
                                                'columns' => $gridColumns,
                                                'showConfirmAlert' => false,
                                                'fontAwesome' => true,
                                                'showColumnSelector' => false,
                                                'autoWidth'=>true,
                                                'dropdownOptions' => [
                                                    'label' => 'Xuất dữ liệu',
                                                    'class' => 'btn btn-primary'
                                                ],
                                                'exportConfig' => [
                                                    ExportMenu::FORMAT_CSV => false,
                                                    ExportMenu::FORMAT_EXCEL_X => false,
                                                    ExportMenu::FORMAT_HTML => false,
                                                    ExportMenu::FORMAT_PDF => false,
                                                    ExportMenu::FORMAT_TEXT => false,
                                                    ExportMenu::FORMAT_EXCEL => [
                                                        'label' => 'Excel',
                                                    ],
                                                ],
                                                'target' => ExportMenu::TARGET_SELF,
                                                'filename' => "Report_Mt"
                                            ])
                                            ?>

                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'responsive' => true,
                            'pjax' => true,
                            'hover' => true,
                            'showPageSummary' => false,
                            'columns' => $gridColumns,
                        ]); ?>
                    <?php }else{ ?>
                        <div class="portlet-body">
                            <div class="well well-sm">
                                <p><?= Yii::t('app','Không có dữ liệu') ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>