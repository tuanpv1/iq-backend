<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\auth\filters\Yii2Auth;
use common\models\User;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ''.\Yii::t('app', 'Quản lý người dùng');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= $this->title?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p><?= Html::a(''.\Yii::t('app', 'Tạo người dùng'), ['create'], ['class' => 'btn btn-success']) ?> </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\SerialColumn',
            ],
            [
                'attribute' => 'username',
                'format' => 'raw',
                'width'=>'20%',
//                'vAlign' => 'middle',
                'value' => function ($model, $key, $index, $widget) {
                    /**
                     * @var $model \common\models\User
                     */
                    $action = "user/view";
                    $res = Html::a('<kbd>'.$model->username.'</kbd>', [$action, 'id' => $model->id ]);
                    return $res;

                },
            ],
            [
                'attribute' => 'email',
                'width'=>'20%',
            ],
//            'auth_key',
//            'password_hash',
//            'password_reset_token',
//             'email:email',
//             'role',
            [
                'class' => '\kartik\grid\DataColumn',
                'attribute'=>'status',
                'label'=>''.\Yii::t('app', 'Trạng thái'),
//                'width'=>'180px',
                'width'=>'20%',
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
                'filterInputOptions' => ['placeholder' => "".\Yii::t('app', 'Tất cả')],
            ],
            // 'created_at',
            // 'updated_at',
            // 'type',
            // 'site_id',
            // 'content_provider_id',
            // 'parent_id',
            [
                'format' => 'html',
                'label' => ''.\Yii::t('app', 'Nhóm quyền'),
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

            ['class' => 'kartik\grid\ActionColumn',
                'template'=>'{view}{update}{delete}',
                'buttons'=>[
                    'view' => function ($url,$model) {
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::toRoute(['user/view','id'=>$model->id]), [
                            'title' => ''.\Yii::t('app', 'Thông tin user'),
                        ]);

                    },
                    'update' => function ($url,$model) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Url::toRoute(['user/update','id'=>$model->id]), [
                            'title' => ''.\Yii::t('app', 'Cập nhật thông tin user'),
                        ]);
                    },
                    'delete' => function ($url,$model) {
//                        Nếu là chính nó thì không cho thay đổi trạng thái
                        if($model->id != Yii::$app->user->getId()){
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['user/delete','id'=>$model->id]), [
                                'title' => ''.\Yii::t('app', 'Xóa user'),
                                'data-confirm' => Yii::t('app', 'Xóa người dùng này?')
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