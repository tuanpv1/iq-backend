<?php

use common\models\IpAddress;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\IpAddressSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t("app","Danh sách IP nhà máy");
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?=Yii::t("app","Quản lý IP")?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= Html::a(Yii::t("app","Tạo mới") ,
                        Yii::$app->urlManager->createUrl(['/ip-address/create']),
                        ['class' => 'btn btn-success']) ?>
                </p>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'responsive' => true,
                    'pjax' => false,
                    'hover' => true,
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => Yii::t('app','STT'),
                        ],
                        'ip_start',
                        'stateprov',
                        'city',
                        'country',
                        [
                            'class' => 'kartik\grid\ActionColumn',
                            'width' => '15%',
                            'template' => '{view} {update} {delete}',
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
