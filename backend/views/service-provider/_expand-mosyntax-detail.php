<?php

/* @var $this yii\web\View */
use common\models\SmsMoSyntax;
use kartik\detail\DetailView;
use yii\helpers\Html;

/* @var $model common\models\Service */
?>
<div class="caption">
    <i class="fa fa-comment-o font-white-sharp"></i>
    <span class="caption-subject font-white-sharp bold uppercase"><?= \Yii::t('app', 'Ghi chú') ?></span>
</div>
<div class="well" style="color: #000000">
    <?= $model->admin_note ?>
</div>
<?= DetailView::widget([
    'model' => $model,
    'attributes' => [
        'syntax',
        'description:ntext',
        [
            'attribute' => 'type',
            'value' => isset(SmsMoSyntax::$mo_types[$model->type])?SmsMoSyntax::$mo_types[$model->type]:'N/A'
        ],
        [
            'attribute' => 'service_id',
            'format'=>'html',
            'value' =>$model->service?  Html::a($model->service->display_name, ['/service/view', 'id' => $model->service_id],['class'=>'label label-primary']):''
        ],
        [
            'attribute' => 'status',
            'value' => SmsMoSyntax::getMoStatusNameByStatus($model->status)?SmsMoSyntax::getMoStatusNameByStatus($model->status):'N/A'
        ],
        [
            'attribute' => 'created_at',
            'value' => date('d/m/Y', $model->created_at)
        ],
        [
            'attribute' => 'updated_at',
            'value' => date('d/m/Y', $model->updated_at)
        ],
    ],
]) ?>
