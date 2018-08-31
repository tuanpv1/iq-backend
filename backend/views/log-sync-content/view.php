<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\LogSyncContent */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Log Sync Contents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-sync-content-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'content_id',
            'site_id',
            'content_status',
            'sync_status',
            'retry',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
