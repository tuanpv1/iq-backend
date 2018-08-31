<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\KodiCategory */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Group', 'url' => Yii::$app->urlManager->createUrl(['kodi-category/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= \kartik\detail\DetailView::widget([
        'model' => $model,
        'attributes' => [

            'display_name',
            [
                'label' => $model->getAttributeLabel('status'),
                'attribute' => 'status',
                'value' => $model->getStatusName()
            ],
            'description:ntext',


        ],
    ]) ?>

</div>
