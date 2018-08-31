<?php

use common\helpers\CommonUtils;
use common\models\ReportRevenue;
use common\models\Service;
use common\models\Site;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use common\models\SubscriberTransaction;

/* @var $report \backend\models\ReportUserDailyForm */
/* @var $this yii\web\View */

$this->title = 'Báo cáo nạp tiền';
$this->params['breadcrumbs'][] = $this->title;

$js = <<<JS
    function onchangeTypeTime(){
        var value =$('#typeTime').val();
         if(value ==1){
            $("#date").show();
            $("#month").hide();
        }else if(value ==2){
            $("#date").hide();
            $("#month").show();
        }
    }
    $(document).ready(function () {
        onchangeTypeTime();
    });
JS;
$this->registerJs($js, \yii\web\View::POS_END);
$this->registerJs('onchangeTypeTime()');
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-body">
                <div class="report-user-daily-index">
                    <div class="row form-group">
                        <div class="col-md-12 col-md-offset-0">
                            <?php $form = ActiveForm::begin(
                                ['method' => 'get',
                                    'action' => Url::to(['report/topup']),]
                            ); ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-2">
                                        <?= $form->field($report, 'site_id')->dropDownList( ArrayHelper::merge(['' => 'Tất cả'],Site::listSite()), ['id'=>'site-id']); ?>
                                    </div>

                                    <div class="col-md-2">
                                        <?= $form->field($report, 'white_list')->dropDownList( ArrayHelper::merge(['' => ''.\Yii::t('app', 'Tất cả')],SubscriberTransaction::listWhitelistTypes()), ['id'=>'white_list']); ?>
                                    </div>

                                    <div class="col-md-2">
                                    	<?= $form->field($report, 'channel')->dropDownList( ArrayHelper::merge(['' => 'Tất cả'],SubscriberTransaction::listTopupChannelType()), ['id'=>'channel']); ?>
                                    </div>

                                    <div id="date">
                                        <div class="col-md-2">
                                            <?= $form->field($report, 'from_date')->widget(\kartik\widgets\DatePicker::classname(), [
                                                'options' => ['placeholder' => Yii::t('app','Ngày bắt đầu')],
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
                                                'options' => ['placeholder' => Yii::t('app','Ngày kết thúc')],
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
                                        <?= \yii\helpers\Html::submitButton(Yii::t('app','Xem báo cáo'), ['class' => 'btn btn-primary']) ?>
                                    </div>

                                </div>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <?php if ($dataProvider) { ?>
                        <?php
                        $gridColumns =[
                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'report_date',
                                            	'label' => Yii::t('app','Ngày'),
                                                'width' => '150px',
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\ReportTopup */
                                                    return !empty($model->report_date) ? date('d/m/Y', $model->report_date) : '';
                                                },
                                                'pageSummary' => Yii::t('app','Tổng số')
                                            ],

                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'count',
                                            	'label' => Yii::t('app','Số lượng thuê bao nạp tiền'),
                                                'value' => function ($model) {
                                                    return CommonUtils::formatNumber($model->count);
                                                },
                                                'pageSummary' =>ReportRevenue::sumReport($dataProvider,'count')
//                                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('count')?$dataProvider->query->sum('count'):0)
                                            ],

                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'revenue',
                                            	'label' => Yii::t('app','Số tiền nạp'),
                                                'value' => function ($model) {
                                                    return CommonUtils::formatNumber($model->revenue);
                                                },
                                                'pageSummary' =>ReportRevenue::sumReport($dataProvider,'revenue')
//                                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('revenue')?$dataProvider->query->sum('revenue'):0)
                                            ],
                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'channel',
                                            	'label' => Yii::t('app','Kênh nạp'),
                                                'width' => '150px',
                                                'value' => function ($model) {
                                                	$subTran = new SubscriberTransaction();
                                                	$subTran->channel = $model->channel;
                                                    return $subTran->getChannelName();
                                                },
                                            ],
                                        ]
                        ?>

                        <?php
                            $expMenu = ExportMenu::widget([
                                'dataProvider' => $exportData,
                                'columns' => [
                                				[
                                					'class' => 'kartik\grid\SerialColumn',
                                            		'header' => 'STT'
                            					],
                                				[
	                                                'attribute' => 'transaction_time',
	                                            	'label' => Yii::t('app','Ngày'),
                                					'value' => function ($model) {
                                						return !empty($model->transaction_time) ? date('d/m/Y H:i:s', $model->transaction_time) : '';
                                					},
                                            	],
                                				[
	                                                'attribute' => 'subscriber_id',
	                                            	'label' => Yii::t('app','Tên tài khoản'),
                                					'value' => function ($model) {
                                						return $model->subscriber->username;
                                					},
                                            	],
                                				[
	                                                'attribute' => 'channel',
	                                            	'label' => Yii::t('app','Kênh nạp'),
                                					'value' => function ($model) {
                                						$subTran = new SubscriberTransaction();
                                						$subTran->channel = $model->channel;
                                						return $subTran->getChannelName();
                                					},
                                            	],
                                				[
	                                                'attribute' => 'cost',
	                                            	'label' => Yii::t('app','Thành tiền(VNĐ)')
                                            	]
                                		
                            	],
                                'showConfirmAlert' => false,
                                'fontAwesome' => true,
                                'showColumnSelector' => true,
                                'dropdownOptions' => [
                                    'label' => Yii::t('app','Xuất chi tiết'),
                                    'class' => 'btn btn-default'
                                ],
                                'exportConfig' => [
                                    ExportMenu::FORMAT_CSV => false,
                                    ExportMenu::FORMAT_EXCEL_X => [
                                        'label' => 'Excel',
                                    ],
                                    ExportMenu::FORMAT_HTML => false,
                                    ExportMenu::FORMAT_PDF => false,
                                    ExportMenu::FORMAT_TEXT => false,
                                    ExportMenu::FORMAT_EXCEL => false,
                                ],
                                'target' => ExportMenu::TARGET_SELF,
                                'filename' => "Baocaonaptienchitiet"
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
                                $expMenu,
                                ['content'=>
                                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['topup'], [
                                        'data-pjax'=>0,
                                        'class' => 'btn btn-default',
                                        'title'=>Yii::t('kvgrid', 'Reset Grid')
                                    ])
                                ],
                            ],
                            'export' => false,/*[
                                'label' => Yii::t('app',"Xuất báo cáo"),
                                'fontAwesome' => true,
                                'showConfirmAlert' => false,
                                'target' => GridView::TARGET_BLANK,

                            ],
                            'exportConfig' => [
                                GridView::EXCEL => ['label' => 'Excel','filename' => "baocaonaptien"],
                            ],*/
                        ]); ?>
                    <?php }else{ ?>
                        <div class="portlet-body">
                            <div class="well well-sm">
                                <p><?=Yii::t('app','Không có dữ liệu') ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>