<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\LogSyncContent */

$this->title = 'Create Log Sync Content';
$this->params['breadcrumbs'][] = ['label' => 'Log Sync Contents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-sync-content-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
