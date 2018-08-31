<?php

use common\models\SmsMtTemplate;
use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SmsMtTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = ''.\Yii::t('app', 'Sms Mt Templates');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Quản lý MT Template') ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= Html::a("".\Yii::t('app', 'Tạo MT Template') , Yii::$app->urlManager->createUrl(['/sms-mt-template/create']), ['class' => 'btn btn-success']) ?>
                </p>

                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'id' => 'grid-category-id',
//                    'filterModel' => $searchModel,
                    'responsive' => true,
                    'pjax' => true,
                    'hover' => true,
                    'columns' => [
                        'code_name',
                        'content',
                        'description',
                        'params',
                        [
                            'attribute' => 'type',
                            'class' => '\kartik\grid\DataColumn',
                            'width'=>'200px',
                            'value' => function ($model, $key, $index, $widget) {
                                /**
                                 * @var $model SmsMtTemplate
                                 */
                                return $model->getTypeName();
                            },
                        ],
                        [
                            'class' => 'kartik\grid\ActionColumn',

                            'template' => '{update}',
//                            'dropdown' => true,
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
