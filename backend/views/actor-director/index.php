<?php

use common\models\ActorDirector;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ActorDirectorSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
if($content_type == ActorDirector::TYPE_VIDEO){
    $this->title = Yii::t('app', 'Quản lý Diễn viên/Đạo diễn');
}else{
    $this->title = Yii::t('app', 'Quản lý Ca sĩ/Nhạc sĩ');
}

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
                    <a href="javascript:;" class="collapse"></a>
                </div>
            </div>
            <div class="portlet-body">
                <p><?php if(!Yii::$app->params['tvod1Only']) echo Html::a(\Yii::t('app', 'Thêm mới'), ['create','content_type'=>$content_type], ['class' => 'btn blue']) ?> </p>
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsive' => true,
                    // 'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'name',
                            'format'=>'raw',
                            'value'=>function ($model, $key, $index, $widget) {
                                $action = "actor-director/view";
                                $res = Html::a('<kbd>'.$model->name.'</kbd>', [$action, 'id' => $model->id,'content_type' => $model->content_type, ]);
                                return $res;
                            },
                        ],
                        [
                            'class' => '\kartik\grid\DataColumn',
                            'attribute' => 'image',
                            'filter' => false,
                            'format' => 'raw',
//                            'label' => 'Ảnh',
                            'value' => function ($model, $key, $index, $widget) {
                                /** @var $model \common\models\ActorDirector */

                                $link = $model->getImage();
                                return $link ? Html::img($link, ['alt' => 'Thumbnail', 'width' => '50', 'height' => '50']) : '';

                            },
                        ],
                        [
                            'attribute' => 'type',
                            'class' => '\kartik\grid\DataColumn',
                            'width'=>'200px',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model \common\models\ActorDirector
                                 */
                                return $model->getTypeName();
                            },
                            'filterType' => GridView::FILTER_SELECT2,
                            'filter' => ActorDirector::listType($content_type),
                            'filterWidgetOptions' => [
                                'pluginOptions' => ['allowClear' => true],
                            ],
                            'filterInputOptions' => ['placeholder' => "Tất cả"],
                        ],
                        [
                            'class' => '\kartik\grid\ActionColumn',
                            'template' => '{view}{update}{delete}',
                            'buttons'=>[
                                'view' => function ($url,$model) {
                                    return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::toRoute(['actor-director/view','id'=>$model->id,'content_type'=>$model->content_type]), [
                                        'title' => 'Xem',
                                    ]);

                                },
                                'update' => function ($url,$model) {
                                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>', Url::toRoute(['actor-director/update','id'=>$model->id,'content_type'=>$model->content_type]), [
                                        'title' => 'Cập nhật',
                                    ]);

                                },
                                'delete' => function ($url,$model) {
                                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['actor-director/delete','id'=>$model->id,'content_type'=>$model->content_type]), [
                                        'title' => 'Xóa',
                                        'data-confirm' => Yii::t('app', 'Bạn có muốn xóa không?')
                                    ]);
                                },
                            ],
//                            'dropdown' => true,
                        ],
                    ],
                ]); ?>

            </div>
        </div>
    </div>
</div>
