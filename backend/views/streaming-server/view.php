<?php

use common\models\StreamingServer;
use kartik\detail\DetailView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StreamingServer */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Quản lý địa chỉ phân phối nội dung'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?= Html::a(Yii::t('app', 'Cập nhật'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <?php if ($model->status == StreamingServer::STATUS_INACTIVE) {
        echo Html::a(Yii::t('app', 'Xóa'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Xóa địa chỉ phân phối nội dung này sẽ ảnh hưởng đến việc phân phối nội dung đến nhà cung cấp dịch vụ tương ứng. Bạn có chắc chắn muốn xóa địa chỉ phân phối nội dung này?'),
                'method' => 'post',
            ],
        ]);
    }
    ?>
</p>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Thông tin nội dung') ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'condensed' => true,
                    'hover' => true,
                    'mode' => DetailView::MODE_VIEW,

                    'attributes' => [

                        [
                            'attribute' => 'name',
                            'format' => 'raw',
                            'value' => '<kbd>' . $model->name . '</kbd>',
                            'displayOnly' => true
                        ],
                        [
                            'attribute' => 'site_ids',
                            'label' => ''.\Yii::t('app', 'Nhà cung cấp dịch vụ'),
                            'format' => 'raw',
                            'value' => $model->getSiteNames(),
                        ],
                        'ip',
                        'port',
                        'host',
                        'content_path',
                        'content_api',
                        'content_status_api',
                        [
                            'attribute' => 'status',
                            'label' => ''.\Yii::t('app', 'Trạng thái'),
                            'format' => 'raw',
                            'value' => ($model->status == StreamingServer::STATUS_ACTIVE) ?
                                '<span class="label label-success">' . $model->getStatusName() . '</span>' :
                                '<span class="label label-danger">' . $model->getStatusName() . '</span>',
                            'type' => DetailView::INPUT_SWITCH,
                            'widgetOptions' => [
                                'pluginOptions' => [
                                    'onText' => 'Active',
                                    'offText' => 'Inactive',
                                ]
                            ]
                        ],
                        [                      // the owner name of the model
                            'attribute' => 'created_at',
                            'value' => date('d/m/Y H:i:s', $model->created_at),
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => date('d/m/Y H:i:s', $model->updated_at),
                        ],

                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>


