<?php

use common\models\Delay;
use common\models\Site;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Delay */

$this->title = Site::findOne($model->site_id)->name;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Độ trễ nội dung theo nhà cung cấp dịch vụ'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <?= $this->title ?>
                </div>
            </div>
            <div class="portlet-body">

            <p>
                <?= Html::a(Yii::t('app','Cập nhật'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a(Yii::t('app','Xóa'), ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => Yii::t('app','Bạn chắc chắn muốn xóa?'),
                        'method' => 'post',
                    ],
                ]) ?>
            </p>

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    [
                        'attribute' => 'site_id',
                        'value' => Site::findOne($model->site_id)->name,
                    ],
                    [
                        'attribute' => 'delay',
                        'value' => $model->delay.Yii::t('app',' Giờ'),
                    ],
                    [
                        'attribute' => 'status',
                        'value' => Delay::listStatus()[$model->status],
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => date('d/m/Y H:i:s',$model->created_at)
                    ],
                    [
                        'attribute' => 'updated_at',
                        'value' => date('d/m/Y H:i:s',$model->updated_at)
                    ],
                ],
            ]) ?>
            </div>
        </div>
    </div>
</div>

