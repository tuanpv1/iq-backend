<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ContentProfile */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Content Profiles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-profile-view">

    <h1><?= Html::encode($this->title) ?></h1>


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [

            'name',
            'url',
            'description:html:Mô tả',
            [
                'label' => \Yii::t('app', 'Loại'),
                'value' => $model->getTypeName()
            ],
            [
                'label' => \Yii::t('app', 'Trạng thái'),
                'value' => $model->getStatusName()
            ],
            'created_at:datetime',
            'updated_at:datetime',
            'bitrate',
            'width',
            'height',
            [
                'label' => \Yii::t('app', 'Quality'),
                'value' => $model->getQualityName()
            ],
            'progress',
        ],
    ]) ?>

</div>
