<?php

use common\models\ActorDirector;
use kartik\detail\DetailView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ActorDirector */

if($content_type == ActorDirector::TYPE_VIDEO){
    $title = Yii::t('app', 'Quản lý Diễn viên/Đạo diễn');
}else{
    $title = Yii::t('app', 'Quản lý Ca sĩ/Nhạc sĩ');
}
$this->title = \Yii::t('app', "Thông tin chi tiết");
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', $title), 'url' => ['index','content_type' => $content_type,]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption"><i class="fa fa-gift"></i><?= $this->title ?></div>
            </div>
            <div class="portlet-body form">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        ['attribute'=>'name', 'format'=>'raw', 'value'=>'<kbd>'.$model->name.'</kbd>', 'displayOnly'=>true],
                        [
                            'attribute' => 'type',
                            'format' => 'html',
                            'value' =>  $model->getTypeName(),
                        ],
                        [
                            'attribute' => 'image',
                            'format' => 'raw',
                            'value' => Html::img($model->getImage()?$model->getImage():null,['alt' => 'Thumbnail', 'width' => '50', 'height' => '50']),
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => date('d-m-Y H:i:s', $model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => date('d-m-Y H:i:s', $model->updated_at)
                        ],

                    ],
                ]) ?>

                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <?= Html::a(\Yii::t('app', 'Cập nhật'), ['update', 'id' => $model->id,'content_type' => $content_type], ['class' => 'btn btn-primary']) ?>
                            <?= Html::a(\Yii::t('app', 'Hủy thao tác'), ['index','content_type' => $content_type], ['class' => 'btn btn-default']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
