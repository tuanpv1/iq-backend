<?php

use common\models\City;
use common\models\Site;
use common\models\Subscriber;

use kartik\export\ExportMenu;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use kartik\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use common\helpers\CUtils;


/* @var $report \backend\models\ReportSubscriberNumberForm */
/* @var $this yii\web\View */

$this->title = 'Báo cáo số lượng thuê bao';
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
                'action' => Url::to(['report/subscriber-number']),]
        ); ?>

        <div class="row">

            <div class="col-md-12">

                <div class="col-md-2">
                    <?= $form->field($report, 'site_id')->dropDownList( ArrayHelper::merge(['' => 'Tất cả'],Site::listSite()), ['id'=>'site-id']); ?>
                </div>

                <div class="col-md-2">
                    <?php
                    $dataList = [];
                    $city = City::findAll(['site_id'=>$site_id]);
                    /** @var  $item  City*/
                    foreach($city as $item){
                        $dataList[$item->code] = $item->name;
                    }
                    echo $form->field($report, 'city')->widget(DepDrop::classname(),
                        [
                            'data' => $dataList,
                            'type' => DepDrop::TYPE_SELECT2 ,
//                            'options' => ['id'=>'id','placeholder' => Yii::t('app','Tất cả'),'disabled' =>'disabled'],
                            'options' => ['id'=>'id','placeholder' => Yii::t('app','Tất cả')],
                            'select2Options' => ['pluginOptions' => ['allowClear' => true]],
                            'pluginOptions' => [
                                'depends' => ['site-id'],
                                'placeholder'=>Yii::t('app','Tất cả'),
                                'url' => Url::to(['/report/find-city-by-site']),
                            ]
                        ]);
                    ?>
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
    $gridColumns = [
        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'report_date',
            'width' => '150px',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return !empty($model->report_date) ? date('d/m/Y', $model->report_date) : '';
            },
            'pageSummary' => Yii::t('app',"Tổng số")
        ],
        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'total_subscriber',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return $model->total_subscriber?$model->total_subscriber:0;
            },


        ],
        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'subscriber_active',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return $model->subscriber_active?$model->subscriber_active:0;
            },

        ],
        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'total_subscriber_destroy',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return $model->total_subscriber_destroy?$model->total_subscriber_destroy:0;
            },


        ],
        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'subscriber_destroy',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return $model->subscriber_destroy?$model->subscriber_destroy:0;
            },
            'pageSummary' => true,
//                            'pageSummary' => $dataProvider->query->sum('subscriber_destroy')?$dataProvider->query->sum('subscriber_destroy'):0

        ],
        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'subscriber_register',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return $model->subscriber_register?$model->subscriber_register:0;
            },
            'pageSummary' => true,
//                            'pageSummary' => $dataProvider->query->sum('subscriber_register')?$dataProvider->query->sum('subscriber_register'):0
        ],
        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'subscriber_register_smb',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return $model->subscriber_register_smb?$model->subscriber_register_smb:0;
            },
            'pageSummary' => true,
//                            'pageSummary' => $dataProvider->query->sum('subscriber_register_smb')?$dataProvider->query->sum('subscriber_register_smb'):0

        ],

        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'subscriber_register_apps',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return $model->subscriber_register_apps?$model->subscriber_register_apps:0;
            },
            'pageSummary' => true,
//                            'pageSummary' => $dataProvider->query->sum('subscriber_register_apps')?$dataProvider->query->sum('subscriber_register_apps'):0

        ],

        [
            'class' => '\kartik\grid\DataColumn',
            'attribute' => 'subscriber_register_web',
            'value' => function ($model) {
                /**  @var $model \common\models\ReportSubscriberNumber */
                return $model->subscriber_register_web?$model->subscriber_register_web:0;
            },
            'pageSummary' => true,
//                            'pageSummary' => $dataProvider->query->sum('subscriber_register_web')?$dataProvider->query->sum('subscriber_register_web'):0

        ],
    ]
    ?>
    <?php
    $expMenu = ExportMenu::widget([
        'dataProvider' => $dataProviderDetail,
        'columns' => [
            [
                'class' => '\kartik\grid\SerialColumn',
                'header'=>Yii::t('app','STT')
            ],
            [
                'class' => '\kartik\grid\DataColumn',
                'label' => Yii::t('app','Ngày'),
                'attribute' => 'register_at',
                'value' => function ($model) {
                    /**  @var $model \common\models\Subscriber */
                    return !empty($model->register_at) ? date('d/m/Y h:m:s', $model->register_at) : '';
                },
            ],
            [
                'class' => '\kartik\grid\DataColumn',
                'label' => Yii::t('app','Tên tài khoản'),
                'attribute' => 'username',
            ],
            [
                'class' => '\kartik\grid\DataColumn',
                'label' => Yii::t('app','Kênh đăng ký'),
                'attribute' => 'channel',
                'value' => function ($model) {
                    /**  @var $model \common\models\Subscriber */
                    return $model->channel?Subscriber::getChannelName($model->channel):'';
                },
            ],
            [
                'class' => '\kartik\grid\DataColumn',
                'label' => Yii::t('app','Nơi đăng ký'),
                'attribute' => 'city',
                'value' => function ($model) {
                    /**  @var $model \common\models\Subscriber */
                    return $model->city ? $model->city:Yii::t('app','Đang cập nhật');
                },
            ],
        ],
        'showConfirmAlert' => false,
        'fontAwesome' => true,
        'showColumnSelector' => true,
        'dropdownOptions' => [
            'label' => Yii::t('app','All'),
            'class' => 'btn btn-default'
        ],
        'exportConfig' => [
            ExportMenu::FORMAT_CSV => false,
            ExportMenu::FORMAT_EXCEL_X => [
                'label' => Yii::t('app','Excel')
            ],
            ExportMenu::FORMAT_HTML => false,
            ExportMenu::FORMAT_PDF => false,
            ExportMenu::FORMAT_TEXT => false,
            ExportMenu::FORMAT_EXCEL => false,
        ],
        'target' => ExportMenu::TARGET_SELF,
        'filename' => "Report"
    ])
    ?>
    <?=
    GridView::widget([
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
                Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['subscriber-number'], [
                    'data-pjax'=>0,
                    'class' => 'btn btn-default',
                    'title'=>Yii::t('kvgrid', 'Reset Grid')
                ])
            ],
        ],
        'export' => [
            'label' => Yii::t('app',"Page"),
            'fontAwesome' => true,
            'showConfirmAlert' => false,
            'target' => GridView::TARGET_BLANK,

        ],
        'exportConfig' => [
            GridView::EXCEL => ['label' => Yii::t('app','Excel'),'filename' => "Report"],
        ],
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