<?php

use common\models\Site;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \common\models\SiteSearch */

$this->title = ''.\Yii::t('app', 'Quản lý Nhà cung cấp dịch vụ');
$this->params['breadcrumbs'][] = $this->title;
$js              = <<<JS
function loadModalData(url){
    $("#streaming-server-view").find(".modal-body").html('');
    $.ajax({
        type     :'GET',
        cache    : false,
        url  :url,
        success  : function(response) {
            $("#streaming-server-view").find(".modal-body").html(response);
        }
    });
}

JS;
$this->registerJs($js, View::POS_END);
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?=  \Yii::t('app', 'Quản lý Nhà cung cấp dịch vụ')?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= Html::a("".\Yii::t('app', 'Tạo Nhà cung cấp dịch vụ') ,
                        Yii::$app->urlManager->createUrl(['/service-provider/create']),
                        ['class' => 'btn btn-success']) ?>
                </p>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsive' => true,
//                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'name',
                            'format' => 'html',
                            'value'=>function ($model, $key, $index, $widget) {
                                return '<a href = "'.\yii\helpers\Url::to(['view', 'id' => $model->id]).'">'.$model->name.'</a>';
                            },
                        ],
                        'description:ntext',
                        'currency',
                        [
                            'attribute' => 'created_at',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\Site
                                 */
                                return $model->created_at ? date('d/m/Y H:i:s', $model->created_at) : '';
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'class' => '\kartik\grid\DataColumn',
                            'width'=>'200px',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\Site
                                 */
                                return \common\models\Site::getListStatusNameByStatus($model->status);
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => Site::getListStatus(),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
//                        [
//                            'class' => '\kartik\grid\DataColumn',
//                            'label' => 'Access SP portal',
//                            'format' => 'raw',
//                            'value'=>function ($model, $key, $index, $widget) {
//                                return '<a href = "'.\yii\helpers\Url::to(['login-as-sp', 'id' => $model->id]).'" target="_blank">Login as SP Admin</a>';
//                            },
//                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'header' => ''.\Yii::t('app', 'Tác động'),
                            'template' => '{update} {delete} {view} {transfer}',
                            'buttons' => [
                                'transfer' => function ($url, $model, $key){
                                    $modalStreamingServer = Yii::$app->urlManager->createUrl(['/content/modal-streaming-server', 'site_id' => $model->id]);
                                    return Html::a('<span class="glyphicon glyphicon-star"></span>', '#streaming-server-view', ['title' => ''.\Yii::t('app', 'Phân phối nội dung'), 'data-toggle' => 'modal', 'data-backdrop' => "static", 'data-keyboard' => "false", 'onclick' => "js:loadModalData('$modalStreamingServer');"]);
                                }
                            ]
//                            'dropdown' => true,
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
<?php
\yii\bootstrap\Modal::begin([
    'header'      => 'Chọn máy chủ',
    'closeButton' => ['label' => 'Cancel'],
    'options'     => ['id' => 'streaming-server-view'],
    'size'        => \yii\bootstrap\Modal::SIZE_DEFAULT,
]);
?>

<?php \yii\bootstrap\Modal::end();?>