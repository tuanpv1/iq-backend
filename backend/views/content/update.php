<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Content */

$this->title = \Yii::t('app', 'Cập nhật nội dung: ') . ' ' . $model->display_name;
$this->params['breadcrumbs'][] = ['label' => \common\models\Category::getTypeName($model->type), 'url' => Yii::$app->urlManager->createUrl(['content/index','type'=>$model->type])];
if($model->parent){
    $this->params['breadcrumbs'][] = ['label' => $model->parent->display_name, 'url' => ['view', 'id' => $model->parent->id]];

}
$this->params['breadcrumbs'][] = ['label' => $model->display_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>



<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Thông tin nội dung');?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <?= $this->render('_form', [
                    'model' => $model,
                    'logoInit' => $logoInit,
                    'logoPreview' => $logoPreview,
                    'thumbnail_epgPreview'=>$thumbnail_epgPreview,
                    'thumbnail_epgInit'=>$thumbnail_epgInit,
                    'thumbnailInit' => $thumbnailInit,
                    'thumbnailPreview' => $thumbnailPreview,
                    'screenshootInit' => $screenshootInit,
                    'screenshootPreview' => $screenshootPreview,
                    'type' => $model->type,
                    'selectedCats' => $selectedCats,
                    'site_id' => $site_id,
                    'parent' => null,
                ]) ?>
            </div>

        </div>
    </div>
</div>
