<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ApiCredential */

$this->title = Yii::t("app","Tạo API KEY");
$this->params['breadcrumbs'][] = ['label' => Yii::t("app","Danh sách API KEY"), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">

        <div class="portlet box green">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-gift"></i><?=Yii::t("app","Tạo API KEY")?>
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
