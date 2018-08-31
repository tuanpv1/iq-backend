<?php

use yii\helpers\Html;
use kartik\detail\DetailView;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\User */


?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= $model->username ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body form">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        ['attribute'=>'username', 'format'=>'raw', 'value'=>'<kbd>'.$model->username.'</kbd>', 'displayOnly'=>true],
                        'email:email',
                        'fullname',
                        'phone_number',
                        [
                            'attribute'=>'status',
                            'label'=>''.\Yii::t('app', 'Trạng thái'),
                            'format'=>'raw',
                            'value'=>($model->status ==User::STATUS_ACTIVE)  ?
                                '<span class="label label-success">'.$model->getStatusName().'</span>' :
                                '<span class="label label-danger">'.$model->getStatusName().'</span>',
                            'type'=>DetailView::INPUT_SWITCH,
                            'widgetOptions' => [
                                'pluginOptions' => [
                                    'onText' => 'Active',
                                    'offText' => 'Delete',
                                ]
                            ]
                        ],
                        [                      // the owner name of the model
                            'attribute'=>'created_at',
                            'label' => ''.\Yii::t('app', 'Ngày tạo'),
                            'value' => date('d/m/Y H:i:s',$model->created_at),
                        ],
                        [                      // the owner name of the model
                            'attribute'=>'updated_at',
                            'label' => ''.\Yii::t('app', 'Ngày cập nhật'),
                            'value' => date('d/m/Y H:i:s',$model->updated_at),
                        ],

//                        'type',
//                        'site_id',
//                        'content_provider_id',
//                        'parent_id',
                    ],
                ]) ?>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <?= Html::a(''.\Yii::t('app', 'Cập nhật'), ['update-user', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>