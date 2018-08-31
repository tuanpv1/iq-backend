<?php

use common\models\PriceCard;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\PriceCard */
/* @var $site common\models\Site */

$this->title = Yii::t("app","IP");
$this->params['breadcrumbs'][] = ['label' => Yii::t("app","Danh sách IP nhà máy"), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?=Yii::t("app","Thông tin chi tiết")?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute'=>'ip_start',
                            'label'=>Yii::t('app','Địa chi IP')
                        ],
                        'stateprov',
                        'city',
                        'country',
                    ],
                ]) ?>
            </div>
        </div>
    </div>
