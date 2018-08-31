<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ContentViewLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Content View Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-view-log-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => \Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'subscriber_id',
            'content_id',
            'msisdn',
            'created_at',
            'ip_address',
            'status',
            'type',
            'description:ntext',
            'user_agent',
            'channel',
            'site_id',
            'started_at',
            'stopped_at',
            'view_date',
        ],
    ]) ?>

</div>
