<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\ContentProfileSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = \Yii::t('app', 'Content Profiles');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-profile-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(\Yii::t('app', 'Create Content Profile'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'content_id',
            'name',
            'url:url',
            'description',
            // 'type',
            // 'status',
            // 'created_at',
            // 'updated_at',
            // 'bitrate',
            // 'width',
            // 'height',
            // 'quality',
            // 'progress',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
