<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ContentLog */

$this->title = \Yii::t('app', 'Update Content Log: ') . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Content Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = \Yii::t('app', 'Update');
?>
<div class="content-log-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
