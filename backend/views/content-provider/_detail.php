<?php

use common\models\ContentProvider;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ContentProvider */
/* @var $userAdminCp common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= $model->cp_name ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= Html::a(Yii::t('app', ''.\Yii::t('app', 'Cập nhật')), ['update', 'id' => $model->id], [
                        'class' => 'btn btn-primary',
                    ]) ?>
                    <?php if($model->status == ContentProvider::STATUS_ACTIVE){ ?>
                        <?= Html::a(Yii::t('app', ''.\Yii::t('app', 'Tạm dừng')), ['update-status-button', 'id' => $model->id,'status'=>$model->status], [
                            'class' => 'btn btn-warning',
                            'data' => [
                                'confirm' => Yii::t('app', 'Khi tạm dừng CP, tất cả các nội dung thuộc CP sẽ bị tạm dừng, bạn có chắc chắn thực hiện?'),
                            ]
                        ]) ?>
                    <?php }elseif($model->status == ContentProvider::STATUS_INACTIVE){ ?>
                        <?= Html::a(Yii::t('app', ''.\Yii::t('app', 'Kích hoạt')), ['update-status-button', 'id' => $model->id,'status'=>$model->status], [
                            'class' => 'btn btn-success',
                            'data' => [
                                'confirm' => Yii::t('app', 'Bạn có thực sự muốn kích hoạt lại CP?'),
                            ]
                        ]) ?>
                    <?php } ?>
                    <?= Html::a(Yii::t('app', ''.\Yii::t('app', 'Xóa')), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => Yii::t('app', 'Bạn có muốn xóa nhà cung cấp nội dung này không?'),
                            'method' => 'post',
                        ],
                    ]) ?>
                </p>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'cp_name',
                        'cp_address:ntext',
                        'cp_mst',
                        [
                            'attribute' => 'status',
                            'value' => \common\models\ContentProvider::getListStatusNameByStatus($model->status)
                        ],
                        [
                            'label'=> Yii::t('app','Người đại diện'),
                            'attribute' => 'username',
                            'value' => $userAdminCp->username . ' - '. $userAdminCp->fullname
                        ],
                        [
                            'label'=> Yii::t('app','Số điện thoại'),
                            'attribute' => 'phone',
                            'value' => $userAdminCp->phone_number
                        ],
                        [
                            'label'=> Yii::t('app','Email'),
                            'attribute' => 'email',
                            'value' => $userAdminCp->email
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