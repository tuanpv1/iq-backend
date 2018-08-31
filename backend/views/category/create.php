<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Category */

$this->title = \Yii::t('app', 'Tạo danh mục');
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Danh mục '). \common\models\Category::getTypeName($type), 'url' => Yii::$app->urlManager->createUrl(['/category/index','type'=>$type])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Tạo danh mục'); ?>
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                    'type'=>$type
                ]) ?>
            </div>
        </div>
    </div>
</div>
