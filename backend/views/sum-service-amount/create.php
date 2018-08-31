<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\SumServiceAmount */

$this->title = ''.\Yii::t('app', 'Create Sum Service Amount');
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Sum Service Amounts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sum-service-amount-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
