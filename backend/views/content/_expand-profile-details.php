<?php

use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\VideoStream */
$dataProvider = $model->getStreamProvider();
$dataProviderEx = $model->getStreamProvider();
?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'responsive' => true,
    'pjax' => false,
    'hover' => true,
    'columns' => [
        'url',
        'protocol'
    ],
]); ?>