<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ItemKodi */

$this->title = 'Cập nhật Item';
$this->params['breadcrumbs'][] = ['label' => 'Item ', 'url' => Yii::$app->urlManager->createUrl(['/item-kodi/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Cập nhật Item
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
                    'selectedCats' => $selectedCats,
                    'site_id' => $site_id,
                ]) ?>
            </div>
        </div>
    </div>
</div>