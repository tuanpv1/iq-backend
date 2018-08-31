<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\ContentAttribute */

$this->title = \Yii::t('app', 'Thêm mới Thuộc tính nội dung');
$this->params['breadcrumbs'][] = ['label' => \Yii::t('app', 'Thuộc tính nội dung'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-attribute-create portlet light">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
