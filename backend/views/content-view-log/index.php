<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ContentViewLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = \Yii::t('app', 'Content View Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-view-log-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(\Yii::t('app', 'Create Content View Log'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'subscriber_id',
            'content_id',
            'msisdn',
            'created_at',
            // 'ip_address',
            // 'status',
            // 'type',
            // 'description:ntext',
            // 'user_agent',
            // 'channel',
            // 'site_id',
            // 'started_at',
            // 'stopped_at',
            // 'view_date',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
