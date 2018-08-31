<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ParamAttribute */

$this->title = \Yii::t('app', 'Tạo ');
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Danh sách '), 'url' => Yii::$app->urlManager->createUrl(['/param-attribute/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?= \Yii::t('app', 'Tạo '); ?>
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
            </div>
        </div>
    </div>
</div>
