<?php

use common\models\StreamingServer;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel common\models\StreamingServerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $site common\models\Site */

$this->title = ''.\Yii::t('app', 'Danh sách địa chỉ phân phối nội dung');
$this->params['breadcrumbs'][] = $this->title;

$js = <<<JS
function loadPrimaryStreamingServer(){
    $("input[name='kvradio'][type='radio'][value='" + $site->primary_streaming_server_id + "']").prop("checked", true);
}

loadPrimaryStreamingServer();

$("#frm-update-primary-server").on('submit', function(evt) {
  var serverId = $("input[name='kvradio']:checked").val()
  if (!serverId) {
    alert('Bạn phải chọn địa chỉ phân phối nội dung chính!')
    return false;
  }
});
JS;
$this->registerJs($js, View::POS_READY);
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Danh sách địa chỉ phân phối nội dung') ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'frm-update-primary-server',
                    'method' => 'post',
                    'action' => Url::to(['service-provider/update-primary-server']),
                ]);

                echo '<input type="hidden" name="site_id" value="' . $site->id . '">';
                ?>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\RadioColumn',
                            'header' => 'Cache chính',
                        ],
                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            //                'vAlign' => 'middle',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\StreamingServer
                                 */
                                $action = "streaming-server/view";
                                $res = Html::a('<kbd>' . $model->name . '</kbd>', [$action, 'id' => $model->id]);
                                return $res;

                            },
                        ],
                        'ip',
                        'host',
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'status',
                            'width' => '120px',
                            'format' => 'raw',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\StreamingServer
                                 */
                                if ($model->status == StreamingServer::STATUS_ACTIVE) {
                                    return '<span class="label label-success">' . $model->getStatusName() . '</span>';
                                } else {
                                    return '<span class="label label-danger">' . $model->getStatusName() . '</span>';
                                }

                            },
                            'filter' => StreamingServer::listStatus(),
                            'filterType' => GridView::FILTER_SELECT2,
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
                        ],
                    ],
                ]); ?>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <?= Html::submitButton(''.\Yii::t('app', 'Cập nhật'),['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
