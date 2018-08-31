<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ItemKodi */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Danh mục', 'url' => Yii::$app->urlManager->createUrl(['item-kodi/index'])];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-kodi-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'display_name',
            'description:ntext',
            [
                'label' => $model->getAttributeLabel('type'),
                'attribute' => 'type',
                'value' => $model->getListType()
            ],
            [
                'label' => $model->getAttributeLabel('status'),
                'attribute' => 'status',
                'value' => $model->getStatusName()
            ],
            [
                'label' => $model->getAttributeLabel('honor'),
                'attribute' => 'honor',
                'value' => $model->getListHonor()
            ],
            [
                'label' => 'Danh mục',
                'attribute' => 'list_cat_id',
                'value' => $model->getAllCategory()
            ],
            'path',
            [
                'label' => $model->getAttributeLabel('image'),
                'attribute' => 'image',
                'value' => \kartik\helpers\Html::img($model->getImageLink(),array('style'=>' \'width = 200px; height: 100px\''))
            ]
        ],
    ]) ?>

</div>
