<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\KodiCategory */

$this->title = $model->display_name;
$this->params['breadcrumbs'][] = ['label' => 'Add-on', 'url' => Yii::$app->urlManager->createUrl(['kodi-addon/index'])];
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
            'description',
            [
                'label' => $model->getAttributeLabel('status'),
                'attribute' => 'status',
                'value' => $model->getStatusName()
            ],
            [
                'label' => 'Danh mục',
                'attribute' => 'list_cat_id',
                'value' => $model->getAllCategory()
            ],
        ],
    ]) ?>

</div>