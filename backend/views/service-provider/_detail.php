<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Site */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= $model->name ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= Html::a(Yii::t('app', ''.\Yii::t('app', 'Cập nhật')), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t('app', ''.\Yii::t('app', 'Xóa')), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => Yii::t('app', 'Bạn có muốn tạm ngừng thị trường dịch vụ này không?'),
                            'method' => 'post',
                        ],
                    ]) ?>
                </p>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'name',
                        'description:ntext',
                        [
                            'attribute' => 'cp_revernue_percent',
                            'value' => $model->cp_revernue_percent . '%'
                        ],
                        'website',
                        [
                            'attribute'=>'default_service_id',
                            'format'=>'raw',
                            'value'=>Html::a(($model->defaultService)?$model->defaultService->name:"N/A", ($model->defaultService)?\yii\helpers\Url::to(['service/view','id' => $model->defaultService->id]):"#", ['class'=>'kv-author-link']),
                        ],
                        [
                            'attribute'=>'default_price_content_id',
                            'format'=>'raw',
                            'value'=>Html::a(($model->defaultContentPrice)?"".\Yii::t('app', 'Chi tiết'):"N/A", ($model->defaultContentPrice)?\yii\helpers\Url::to(['pricing/view','id' => $model->defaultContentPrice->id]):"#", ['class'=>'kv-author-link']),
                        ],
                        [
                            'attribute'=>'currency',
                            'format'=>'raw',
                        ],
                        [
                            'attribute' => 'status',
                            'value' => \common\models\Site::getListStatusNameByStatus($model->status)
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