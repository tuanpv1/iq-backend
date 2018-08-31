<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Category */

$this->title = \Yii::t('app', 'Cập nhật danh mục');
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Danh mục '). \common\models\Category::getTypeName($model->type), 'url' => Yii::$app->urlManager->createUrl(['/category/index','type'=>$model->type])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Cập nhật danh mục'); ?>
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                    'type'=>$model->type
                ]) ?>
            </div>
        </div>
    </div>
</div>
