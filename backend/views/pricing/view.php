<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Pricing */
/* @var $site common\models\Site */

$this->title = 'Mức giá';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'description:ntext',
                        [
                            'attribute' => 'type',
                            'value' => \common\models\Pricing::getListTypeNameByType($model->type)
                        ],
                        [
                            'attribute' => 'price_coin',
                            'value' => $model->price_coin.' '.$model->site->currency
                        ],
                        [
                            'attribute' => 'price_sms',
                            'value' => $model->price_sms.' '.$model->site->currency
                        ],
                        [
                            'attribute' => 'watching_period',
                            'value' => $model->watching_period.' HOURS'
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => date('d/m/Y', $model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => date('d/m/Y', $model->updated_at)
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
