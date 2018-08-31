<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\LogSyncContent */

$this->title = 'Update Log Sync Content: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Log Sync Contents', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="log-sync-content-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
