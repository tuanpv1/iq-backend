<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ContentViewLog */

$this->title = \Yii::t('app', 'Update Content View Log: ') . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Content View Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = \Yii::t('app', 'Update');
?>
<div class="content-view-log-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
