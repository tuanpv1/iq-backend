<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\KodiCategory */

$this->title = 'Tạo group';
$this->params['breadcrumbs'][] = ['label' => 'Danh mục ', 'url' => Yii::$app->urlManager->createUrl(['/kodi-category/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i>Tạo group
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
