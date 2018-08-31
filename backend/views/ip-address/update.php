<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PriceCard */
/* @var $site common\models\Site */

$this->title = Yii::t("app","Cập nhật");
$this->params['breadcrumbs'][] = ['label' => Yii::t("app","Danh sách IP nhà máy"), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t("app","Cập nhật");
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?=Yii::t("app","Cập nhật")?>
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
