<?php

use common\models\ActorDirector;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ActorDirector */
if($content_type == ActorDirector::TYPE_VIDEO){
    $title = Yii::t('app', 'Quản lý Diễn viên/Đạo diễn');
}else{
    $title = Yii::t('app', 'Quản lý Ca sĩ/Nhạc sĩ');
}
$this->title = Yii::t('app', 'Cập nhật');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', $title), 'url' => ['index','content_type' => $content_type,]];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id,'content_type' => $content_type,]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Cập nhật');
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?=$this->title?>
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                    'content_type' => $content_type,
                ]) ?>
            </div>
        </div>
    </div>
</div>
