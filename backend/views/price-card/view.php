<?php

use common\models\PriceCard;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\PriceCard */
/* @var $site common\models\Site */

$this->title = Yii::t("app","Mức nạp");
$this->params['breadcrumbs'][] = ['label' => Yii::t("app","Danh sách mức nạp"), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?=Yii::t("app","Thông tin chi tiết")?></span>(<?= '<span class="label label-' . $model->getStatusClassCss() . '">' . PriceCard::getListStatusNameByStatus($model->status) . '</span>' ?>)
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <div class="caption">
                    <i class="fa fa-comment-o font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?=Yii::t("app","Ghi chú")?></span>
                </div>
                <div class="well">
                    <?php
                    if($model->updated_status_at){
                        echo PriceCard::getListStatusNameByStatus($model->status). Yii::t('app',' vào lúc ').date('H:i:s d/m/Y', $model->updated_status_at);
                    }else{
                        echo PriceCard::getListStatusNameByStatus($model->status). Yii::t('app',' vào lúc ').date('H:i:s d/m/Y', $model->created_at);
                    }
                    ?>
                </div>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        [
                            'attribute' => 'price',
                            'label'=>Yii::t('app','Mức nạp'),
                            'value' => number_format($model->price,0,",",".").' '.PriceCard::getCurrency($model->site_id)
                        ],
                        'description',
                        [
                            'attribute' => 'status',
                            'format' => 'raw',
                            'value' => '<span class="label label-' . $model->getStatusClassCss() . '">' .PriceCard::getListStatusNameByStatus($model->status). '</span>'
                        ],
                        [
                            'attribute' => 'created_at',
                            'value' => date('d/m/Y H:i:s', $model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'value' => date('d/m/Y H:i:s', $model->updated_at)
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
