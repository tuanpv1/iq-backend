<?php

use common\models\Content;
use common\models\Site;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $report \backend\models\ReportContentProfileForm */
/* @var $this yii\web\View */

$this->title = Yii::t('app','Thống kê phiên bản');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-body">

                <div class="report-user-daily-index">

                    <div class="row form-group">
                        <div class="col-md-12 col-md-offset-0">
                            <?php $form = ActiveForm::begin(
                                    [
                                        'method' => 'post',
                                        'id' => 'report-content-profile-id',
                                        'action' => Url::to(['report/content-profile']),
                                    ]
                                );
                                $formId = $form->id;
                            ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <div id="date">
                                        <div class="col-md-3">
                                            <?= $form->field($report, 'from_date')->widget(\kartik\widgets\DatePicker::classname(), [
                                                'options' => ['placeholder' => 'Ngày bắt đầu'],
                                                'type' => \kartik\widgets\DatePicker::TYPE_INPUT,
                                                'pluginOptions' => [
                                                    'autoclose' => true,
                                                    'todayHighlight' => true,
                                                    'format' => 'dd/mm/yyyy'
                                                ]
                                            ]); ?>

                                        </div>
                                        <div class="col-md-3">
                                            <?= $form->field($report, 'to_date')->widget(\kartik\widgets\DatePicker::classname(), [
                                                'options' => ['placeholder' => 'Ngày kết thúc'],
                                                'type' => \kartik\widgets\DatePicker::TYPE_INPUT,
                                                'pluginOptions' => [
                                                    'autoclose' => true,
                                                    'todayHighlight' => true,
                                                    'format' => 'dd/mm/yyyy'
                                                ]
                                            ]); ?>
                                        </div>

                                        <div class="col-md-3">
                                            <div style="margin-top: 25px"></div>
                                            <?= Html::submitButton('Xem báo cáo', ['class' => 'btn btn-primary']) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <?php if ($dataProvider) { ?>
                        <?php
                            $gridColumns = [
                                                ['class' => 'kartik\grid\SerialColumn' ],
                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'attribute' => 'report_date',
                                                    'width' => '150px',
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\ReportContentProfileForm */
                                                        return !empty($model->report_date) ? date('d-m-Y', $model->report_date) : '';
                                                    },
                                                ],
                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'attribute' => 'total_content_profile',
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\ReportContentProfileForm */
                                                        return $model->total_content_profile;
                                                    },
                                                ],
                                             ];
                            $gridColumnsExport =    [
                                                        [
                                                            'class' => 'kartik\grid\SerialColumn',
                                                            'header' => 'STT'
                                                        ],
                                                        [
                                                            'class' => '\kartik\grid\DataColumn',
                                                            'attribute' => 'created_at',
                                                            'width' => '150px',
                                                            'value' => function ($model) {
                                                                /**  @var $model \common\models\ContentProfile */
                                                                return !empty($model->created_at) ? date('d/m/Y H:i:s', $model->created_at) : '';
                                                            },
                                                        ],
                                                        [
                                                            'class' => '\kartik\grid\DataColumn',
                                                            'attribute' => 'name',
                                                        ],
                                                    ]
                        ?>


                        <?php
                            $expMenu = ExportMenu::widget([
                                'dataProvider' => $dataProviderExport,
                                'columns' => $gridColumnsExport,
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
                            'showPageSummary' => false,
                            'columns' => $gridColumns,
                            'panel' => [
                                'type' => GridView::TYPE_DEFAULT,
                            ],
                            'toolbar' => [
//                                '{export}',
                                $expMenu,
                                ['content'=>
                                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['content'], [
                                        'data-pjax'=>0,
                                        'class' => 'btn btn-default',
                                        'title'=>Yii::t('kvgrid', 'Reset Grid')
                                    ])
                                ],
                            ],
                            'export' => [
                                'label' => "Page",
                                'fontAwesome' => true,
                                'showConfirmAlert' => false,
                                'target' => GridView::TARGET_BLANK,

                            ],

                            'exportConfig' => [
                                GridView::EXCEL => ['label' => 'Excel','filename' => "Report"],
                            ],
                        ]); ?>
                    <?php }else{ ?>
                        <div class="portlet-body">
                            <div class="well well-sm">
                                <p><?= Yii::t('app','Không có dữ liệu')?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>