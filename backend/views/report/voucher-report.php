<?php

use common\models\Site;
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

$this->title = Yii::t('app','Báo cáo doanh thu nạp thẻ');
$this->params['breadcrumbs'][] = Yii::t('app','Báo cáo doanh thu nạp thẻ');
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
                                    [
                                        'method' => 'get',
                                        'action' => Url::to(['report/voucher-report']),
//                                        'enableAjaxValidation' => true,
//                                        'enableClientValidation' => false,
                                    ]
                                ); ?>

                                <div class="row">
                                    <div class="col-md-12">

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

                                    </div>
                                </div>

                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($dataProvider) { ?>
                        <?php
                        $gridColumns = [
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'report_date',
                                'width' => '150px',
                                'value' => function ($model) {
                                    /**  @var $model \common\models\ReportVoucher */
                                        return DateTime::createFromFormat("Y-m-d H:i:s", $model->report_date)->format('d-m-Y');
//                                    return !empty($model->report_date) ? date('d/m/Y', $model->report_date) : '';
                                },
                                'pageSummary' => Yii::t('app',"Tổng số")
                            ],
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'total_voucher_created',
                                'value' => function ($model) {
                                    /**  @var $model \common\models\ReportVoucher */
                                    return $model->total_voucher_created?$model->total_voucher_created:0;
                                },
                                    'pageSummary' => $dataProvider->query->sum('total_voucher_created') ? $dataProvider->query->sum('total_voucher_created') : 0
                            ],

                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'revenues_voucher',
                                'value' => function ($model) {
                                    /**  @var $model \common\models\ReportVoucher */
                                    return $model->revenues_voucher?$model->revenues_voucher:0;
                                },
                                    'pageSummary' => $dataProvider->query->sum('revenues_voucher') ? $dataProvider->query->sum('revenues_voucher') : 0
                            ],

                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'total_revenues',
                                'value' => function ($model) {
                                    /**  @var $model \common\models\ReportVoucher */
                                    return $model->total_revenues?$model->total_revenues:0;
                                },
                                'pageSummary' => $dataProvider->query->sum('total_revenues')?$dataProvider->query->sum('total_revenues'):0
                            ],
                        ]
                        ?>

                        <?php
                        $expMenu = ExportMenu::widget([
                            'dataProvider' => $dataProvider,
                            'columns' => $gridColumns,
                            'showConfirmAlert' => false,
                            'fontAwesome' => true,
                            'showColumnSelector' => true,
                            'dropdownOptions' => [
                                'label' => 'All',
                                'class' => 'btn btn-default'
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
                            'filename' => "Report"
                        ])
                        ?>

                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'responsive' => true,
                            'pjax' => true,
                            'hover' => true,
                            'showPageSummary' => true,
                            'columns' => $gridColumns,
                            'panel' => [
                                'type' => GridView::TYPE_DEFAULT,
                            ],
                            'toolbar' => [
                                '{export}',
//                                $expMenu,
//                                ['content'=>
//                                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['subscriber-daily'], [
//                                        'data-pjax'=>0,
//                                        'class' => 'btn btn-default',
//                                        'title'=>Yii::t('kvgrid', 'Reset Grid')
//                                    ])
//                                ],
                            ],
                            'export' => [
                                'label' => Yii::t('app',"Xuất dữ liệu"),
                                'fontAwesome' => true,
                                'showConfirmAlert' => false,
                                'target' => GridView::TARGET_BLANK,

                            ],
                            'exportConfig' => [
                                GridView::EXCEL => ['label' => 'Excel','filename' => "Report_Vouchers"],
                            ],
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