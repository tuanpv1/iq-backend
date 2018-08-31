<?php

use common\helpers\CommonUtils;
use common\models\ContentProvider;
use common\models\ReportRevenue;
use common\models\Service;
use common\models\Site;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;
use scotthuangzl\googlechart\GoogleChart;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $report \backend\models\ReportUserDailyForm */
/* @var $this yii\web\View */

$this->title = ''.\Yii::t('app', 'Báo cáo doanh thu');
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
                                    'action' => Url::to(['report/revenues']),]
                            ); ?>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="col-md-2">
                                        <?= $form->field($report, 'site_id')->dropDownList( ArrayHelper::merge(['' => ''.\Yii::t('app', 'Tất cả')],Site::listSite()), ['id'=>'site-id']); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field($report, 'cp_id')->dropDownList( ArrayHelper::merge([],ContentProvider::listContentProvider()), ['id'=>'cp-id']); ?>
                                    </div>
                                    <div class="col-md-2">
                                        <?= $form->field($report, 'white_list')->dropDownList( ArrayHelper::merge(['' => ''.\Yii::t('app', 'Tất cả')],\common\models\SubscriberTransaction::listWhitelistTypes()), ['id'=>'white_list']); ?>
                                    </div>

                                    <div class="col-md-2">
                                        <?php
                                        /**
                                         * @var $services \common\models\Service[]
                                         */
                                        $listStatus=[Service::STATUS_ACTIVE,Service::STATUS_PAUSE];
                                        $dataList = [];
                                        if($site_id==''){
                                            $services = Service::find()
                                                ->innerJoin('service_cp_asm','service_cp_asm.service_id=service.id')
                                                ->andWhere(['service_cp_asm.cp_id'=>$cp_id])
                                                ->andWhere(['service.status'=>$listStatus])
                                                ->all();
                                        }else{
                                            $services = Service::find()
                                                ->innerJoin('service_cp_asm','service_cp_asm.service_id=service.id')
                                                ->andWhere(['service_cp_asm.cp_id'=>$cp_id])
                                                ->andWhere(['service.site_id'=>$site_id])
                                                ->andWhere(['service.status'=>$listStatus])
                                                ->all();
                                        }
                                        foreach ($services as $service) {
                                            $dataList[$service->id] = $service->name;
                                        }
                                        echo $form->field($report, 'service_id')->widget(DepDrop::classname(),
                                            [
                                                'data' => $dataList,
                                                'type' => DepDrop::TYPE_SELECT2 ,
                                                'options' => ['id'=>'service-id','placeholder' => ''.\Yii::t('app', 'Tất cả')],
                                                'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                                                'pluginOptions' => [
                                                    'depends' => ['site-id','cp-id'],
                                                    'placeholder'=>Yii::t('app','Tất cả'),
                                                    'url' => Url::to(['/report/find-service-by-site']),
                                                ]
                                            ]);
                                        ?>
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

                                </div>
                            </div>

                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>

                    <?php if ($dataProvider) { ?>
                        <?= \dosamigos\highcharts\HighCharts::widget([
                            'clientOptions' => [
                                'height'=>500,
                                'title' => [
                                    'text' => ''
                                ],
                                'xAxis' => [
                                    'categories' => $dataCharts[0]
                                ],
                                'yAxis' => [
                                    'min' => 0,
                                    'allowDecimals' => false,
                                    'title' => [
                                        'text' => Yii::t('app', '')
                                    ]
                                ],
                                'series' => [
                                    ['name' => Yii::t('app', 'Tổng doanh thu'), 'data' => $dataCharts[1]],
                                    ['name' => Yii::t('app', 'Doanh thu gia hạn'), 'data' => $dataCharts[2]],
                                    ['name' => Yii::t('app', 'Doanh thu đăng ký'), 'data' => $dataCharts[3]],
                                    ['name' => Yii::t('app', 'Doanh thu mua nội dung lẻ'), 'data' => $dataCharts[4]],
                                ]
                            ]
                        ]);
                        ?>
                        <?php
                        $gridColumns =[
                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'report_date',
                                                'width' => '150px',
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\ReportUserDaily */
                //                                        return DateTime::createFromFormat("Y-m-d H:i:s", $model->report_date)->format('d-m-Y');
                                                    return !empty($model->report_date) ? date('d/m/Y', $model->report_date) : '';
                                                },
                                                'pageSummary' => "".\Yii::t('app', 'Tổng số')
                                            ],

                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'total_revenues',
                //                                    'format'=>['decimal'],
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\ReportRevenue */
                                                    return CommonUtils::formatNumber($model->total_revenues);
                                                },
                                                'pageSummary' =>ReportRevenue::sumReport($dataProvider,'total_revenues')
//                                                'pageSummaryOptions'=>['append'=>',000']// thêm đuôi vào giá trị
//                                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('total_revenues')?$dataProvider->query->sum('total_revenues'):0)
                                            ],

                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'renew_revenues',
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\ReportRevenue */
                                                    return CommonUtils::formatNumber($model->renew_revenues);
                                                },
                                                'pageSummary' =>ReportRevenue::sumReport($dataProvider,'renew_revenues')
//                                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('renew_revenues')?$dataProvider->query->sum('renew_revenues'):0)
                                            ],

                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'register_revenues',
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\ReportRevenue */
                                                    return CommonUtils::formatNumber($model->register_revenues);
                                                },
                                                'pageSummary' =>ReportRevenue::sumReport($dataProvider,'register_revenues')
//                                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('register_revenues')?$dataProvider->query->sum('register_revenues'):0)
                                            ],

                                            [
                                                'class' => '\kartik\grid\DataColumn',
                                                'attribute' => 'content_buy_revenues',
                                                'value' => function ($model) {
                                                    /**  @var $model \common\models\ReportRevenue */
                                                    return CommonUtils::formatNumber($model->content_buy_revenues);

                                                },
                                                'pageSummary' =>ReportRevenue::sumReport($dataProvider,'content_buy_revenues')
//                                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('content_buy_revenues')?$dataProvider->query->sum('content_buy_revenues'):0)
                //                                    'pageSummary' => CommonUtils::formatNumber($report->dataProvider->query->sum('content_buy_revenues'))
                                            ],
                                        ]
                        ?>

                        <?php
                            $expMenu = ExportMenu::widget([
                                'dataProvider' => $excelDataProvider,
                                //'columns' => $gridColumns,
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
                                        'label' => ''.\Yii::t('app', 'Excel'),
                                    ],
                                    ExportMenu::FORMAT_HTML => false,
                                    ExportMenu::FORMAT_PDF => false,
                                    ExportMenu::FORMAT_TEXT => false,
                                    ExportMenu::FORMAT_EXCEL => false,
                                ],
                                'target' => ExportMenu::TARGET_SELF,
                                'filename' => Yii::t("app","Baocaodoanhthuchitiet")
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
                                    Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['revenues'], [
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
                                GridView::EXCEL => ['label' => Yii::t("app","Excel"),'filename' => Yii::t("app","Baocaodoanhthu")],
                            ],*/
                        ]); ?>
                    <?php }else{ ?>
                        <div class="portlet-body">
                            <div class="well well-sm">
                                <p><?= \Yii::t('app', 'Không có dữ liệu') ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>