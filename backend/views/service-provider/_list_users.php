<?php

use yii\bootstrap\Modal;
use yii\helpers\Html;
use kartik\grid\GridView;
use common\auth\filters\Yii2Auth;
use common\models\User;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $newModel User */

$this->title = ''.\Yii::t('app', 'Users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Quản lý Users') ?> </span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?php
                    Modal::begin([
                        'header' => '<h4>Tạo người dùng</h4>',
                        'toggleButton' => ['label' => ''.\Yii::t('app', 'Tạo người dùng'), 'class' => 'btn btn-success'],
                        'closeButton' => ['label' => 'Cancel']
                    ]);
                    echo $this->render('_form_user', ['model' => $newModel]);
                    Modal::end();
                    ?>
                </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'label' => 'Id',
                'width'=>'50px',
            ],
            [
                'attribute' => 'username',
                'format' => 'raw',
//                'vAlign' => 'middle',
                'value' => function ($model, $key, $index, $widget) {
                    /**
                     * @var $model \common\models\User
                     */
                    $action = "view-user";
                    $res = Html::a('<kbd>'.$model->username.'</kbd>', [$action, 'id' => $model->id ]);
                    return $res;

                },
            ],
//            'auth_key',
//            'password_hash',
//            'password_reset_token',
             'email:email',
//             'role',
            [
                'class' => '\kartik\grid\DataColumn',
                'attribute'=>'status',
                'label'=>''.\Yii::t('app', 'Trạng thái'),
                'width'=>'120px',
                'format'=>'raw',
                'value' => function ($model, $key, $index, $widget) {
                    /**
                     * @var $model \common\models\User
                     */
                    if($model->status == User::STATUS_ACTIVE){
                        return '<span class="label label-success">'.$model->getStatusName().'</span>';
                    }else{
                        return '<span class="label label-danger">'.$model->getStatusName().'</span>';
                    }

                },
                'filter' => User::listStatus(),
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => "Tất cả"],
            ],
            // 'created_at',
            // 'updated_at',
            // 'type',
            // 'site_id',
            // 'content_provider_id',
            // 'parent_id',
            [
                'format' => 'html',
                'label' => ''.\Yii::t('app', 'Quyền người dùng'),
//                'vAlign' => 'middle',
                'value' => function ($model, $key, $index, $widget) {
                    /**
                     * @var $model \common\models\User
                     */
                    $e = new Yii2Auth();
                    if($e->superAdmin != $model->username){
                        return $model->getRolesName();
                    }else{
                        return "Supper Admin";
                    }
                },
            ],

            ['class' => 'yii\grid\ActionColumn',
                'template'=>'{view} {update} {delete}',
                'buttons'=>[
                    'view' => function ($url,$model) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::toRoute(['service-provider/view-user','id'=>$model->id]), [
                            'title' => ''.\Yii::t('app', 'Thông người dùng'),
                        ]);

                    },
                    'update' => function ($url,$model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Url::toRoute(['service-provider/update-user','id'=>$model->id]), [
                            'title' => ''.\Yii::t('app', 'Cập nhật thông tin người dùng'),
                        ]);
                    },
                    'delete' => function ($url,$model) {
//                        Nếu là chính nó thì không cho thay đổi trạng thái
                        if($model->id != Yii::$app->user->getId()){
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['service-provider/delete-user','id'=>$model->id]), [
                                'title' => ''.\Yii::t('app', 'Xóa người dùng'),
                            ]);
                        }
                    }
                ]
            ],
        ],
    ]); ?>

            </div>
        </div>
    </div>
</div>