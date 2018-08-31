<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\editable\Editable;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ''.\Yii::t('app', 'Quản lý nhóm quyền trang nhà cung cấp nội dung');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <p>
        <?= Html::a(''.\Yii::t('app', 'Tạo nhóm quyền'), ['create-role'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'id' => 'rbac-role',
        'dataProvider' => $dataProvider,
        'responsive' => true,
        'pjax' => false,
        'hover' => true,
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],
            [
                'attribute' => 'name',
//                'vAlign' => 'middle',
                'format' => 'html',
                'noWrap' => true,
                'value' => function ($model, $key, $index, $widget) {
                    /**
                     * @var $model \common\models\AuthItem
                     */
                    $res = Html::a($model->name, ['rbac-cprovider/update-role', 'name' => $model->name]);
                    $res .= " [". sizeof($model->children) . "]";
                    return $res;
                },
            ],
            'description',
//            'data',
//            'type',
            ['class' => 'yii\grid\ActionColumn',
            'template' => '{set-password} {view} {update} {delete}',
               'buttons'=> [
                    'view' => function ($url, $model, $key) {
                        return   Html::a('<span class="glyphicon glyphicon-eye-open"></span>',
                            Yii::$app->urlManager->createUrl(['rbac-cprovider/view-role','name'=>$model->name]),[
                            'title' => Yii::t('yii', 'Xem'),
                            'data-pjax' => '0',
                        ]) ;
                    },
                    'update' => function ($url, $model, $key) {
                        return   Html::a('<span class="glyphicon glyphicon-pencil"></span>',
                            Yii::$app->urlManager->createUrl(['rbac-cprovider/update-role','name'=>$model->name]),[
                                'title' => Yii::t('yii', 'Cập nhật'),
                                'data-pjax' => '0',
                            ]) ;
                    },
                   'delete' => function ($url, $model) {
                       return Html::a('<span class="glyphicon glyphicon-trash"></span>', Yii::$app->urlManager->createUrl(['rbac-cprovider/delete-role','name'=>$model->name]), [
                           'title' => Yii::t('yii', 'Xóa'),
                           'data-confirm' => Yii::t('yii', 'Bạn có chắc chắn xóa nhóm quyền này?'),
                           'data-method' => 'post',
                           'data-pjax' => '0',
                       ]);
                   }
                 ],
            ],
        ],
    ]); ?>

</div>


