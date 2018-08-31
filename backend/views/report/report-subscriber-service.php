<?php

use common\helpers\CommonUtils;
use common\models\ContentProvider;
use common\models\ReportSubscriberService;
use common\models\Service;
use common\models\Site;
use common\models\Subscriber;
use common\models\SubscriberServiceAsm;
use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $report \backend\models\ReportUserDailyForm */
/* @var $this yii\web\View */

$this->title = ''.\Yii::t('app', 'Thống kê gói cước');
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
                                    'action' => Url::to(['report/report-service-subscriber']),]
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
                                        <?= $form->field($report, 'white_list')->dropDownList( ArrayHelper::merge(['' => ''.\Yii::t('app', 'Tất cả')],ReportSubscriberService::listWhitelistTypes()), ['id'=>'white_list']); ?>
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
                                                    'placeholder'=>'Tất cả',
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

                                    <div class="col-md-4">
                                        <div style="margin-top: 25px"></div>
                                        <?= \yii\helpers\Html::submitButton(''.\Yii::t('app', 'Xem báo cáo'), ['class' => 'btn btn-primary']) ?>
                                    <?php ActiveForm::end(); ?>
                                    <?php if ($dataProvider) { ?>

                                        <?=
                                        // Chú ý: Xuất file khác so với view
                                        ExportMenu::widget([
                                            'dataProvider' => $dataProviderDetail,
                                            'columns' => [
                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'label' => Yii::t('app','Ngày'),
                                                    'width' => '150px',
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\SubscriberTransaction */
                                                        return date('d/m/Y H:i:s', $model->transaction_time);
                                                    },
                                                ],

                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'label' => Yii::t('app','Tên tài khoản'),
                                                    //                                    'format'=>['decimal'],
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\SubscriberTransaction */
                                                        return Subscriber::findOne(['id'=>$model->subscriber_id])->username;
                                                    },
                                                ],

                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'label' => Yii::t('app','Gói cước'),
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\SubscriberTransaction */
                                                        return Service::findOne(['id'=>$model->service_id])->display_name;
                                                    },
                                                ],

                                                [
                                                    'class' => '\kartik\grid\DataColumn',
                                                    'label' => Yii::t('app','Tình trạng'),
                                                    'value' => function ($model) {
                                                        /**  @var $model \common\models\SubscriberTransaction */
                                                        return $model->getTypeName();
                                                    },
                                                ],
                                            ],
                                            'showConfirmAlert' => false,
                                            'fontAwesome' => true,
                                            'showColumnSelector' => false,
                                            'dropdownOptions' => [
                                                'label' => 'Xuất dữ liệu',
                                                'class' => 'btn btn-primary'
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
                                            'filename' => "Report_Service_Subscriber"
                                        ])
                                        ?>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                        <?php $gridColumns =[
                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'report_date',
                                'width' => '150px',
                                'value' => function ($model) {
                                    /**  @var $model \common\models\ReportSubscriberService */
                                    return DateTime::createFromFormat("Y-m-d H:i:s", $model->report_date)->format('d-m-Y');
                                },
                                'pageSummary' => "".\Yii::t('app', 'Tổng số')
                            ],

                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'subscriber_register',
                                //                                    'format'=>['decimal'],
                                'value' => function ($model) {
                                    /**  @var $model \common\models\ReportSubscriberService */
                                    return $model->subscriber_register;
                                },
                                'pageSummary' => true,
//                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('subscriber_register')?$dataProvider->query->sum('subscriber_register'):0)
                            ],

                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'subscriber_retry',
                                'value' => function ($model) {
                                    /**  @var $model \common\models\ReportSubscriberService */
                                    return $model->subscriber_retry;
                                },
                                'pageSummary' => true,
//                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('subscriber_retry')?$dataProvider->query->sum('subscriber_retry'):0)
                            ],

                            [
                                'class' => '\kartik\grid\DataColumn',
                                'attribute' => 'subscriber_expired',
                                'value' => function ($model) {
                                    /**  @var $model \common\models\ReportSubscriberService */
                                    return $model->subscriber_expired;
                                },
                                'pageSummary' => true,
//                                'pageSummary' => CommonUtils::formatNumber($dataProvider->query->sum('subscriber_expired')?$dataProvider->query->sum('subscriber_expired'):0)
                            ],
                        ]
                        ?>

                        <?=
                            GridView::widget([
                            'dataProvider' => $dataProvider,
                            'responsive' => true,
                            'pjax' => true,
                            'hover' => true,
                            'showPageSummary' => true,
                            'columns' => $gridColumns,
                            ]);
                        ?>
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