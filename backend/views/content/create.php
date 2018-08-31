<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Content */

$this->title = \Yii::t('app', 'Tạo ').\common\models\Category::getTypeName($model->type);
$this->params['breadcrumbs'][] = ['label' => \common\models\Category::getTypeName($model->type), 'url' => Yii::$app->urlManager->createUrl(['content/index','type'=>$model->type])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Tạo <?php echo \common\models\Category::getTypeName($type);?>
                </div>
            </div>
            <div class="portlet-body form">
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
                    'parent' => $parent
                ]) ?>
            </div>
        </div>
    </div>
</div>
