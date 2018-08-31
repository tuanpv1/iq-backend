<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\KodiAddon */

$this->title = 'Tạo Add-on';
$this->params['breadcrumbs'][] = ['label' => 'Add-on ', 'url' => Yii::$app->urlManager->createUrl(['/kodi-addon/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Tạo Add-on
                </div>
            </div>
            <div class="portlet-body form">
                <?= $this->render('_form', [
                    'model' => $model,
//                    'selectedCats' => $selectedCats,
                    'site_id' => $site_id,
                ]) ?>
            </div>
        </div>
    </div>
</div>
