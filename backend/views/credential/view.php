<?php

use common\models\ServiceProviderApiCredential;
use common\models\ApiCredential;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ApiCredential */

$this->title = $model->client_name;
$this->params['breadcrumbs'][] = ['label' => Yii::t("app","Danh sách API KEY"), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?=Yii::t("app","Thông tin API KEY ")?><?= $model->client_name ?>"</span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <p>
                    <?= Html::a(Yii::t("app","Cập nhật"), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a(Yii::t("app","Xóa"), ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'data' => [
                            'confirm' => Yii::t("app","Bạn chắc chắn muốn xóa API KEY này không?"),
                            'method' => 'post',
                        ],
                    ]) ?>
                </p>
                <?php switch($model->type){
                    case ApiCredential::TYPE_ANDROID_APPLICATION:
                        echo DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'client_name',
                                'description:ntext',
                                'client_api_key',             // title attribute (in plain text)
                                'certificate_fingerprint',
                                [
                                    'attribute' => 'type',
                                    'value' => ApiCredential::$api_key_types[$model->type]
                                ],
                                [
                                    'attribute' => 'status',
                                    'value' => ApiCredential::getListStatusNameByStatus($model->status)
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'value' => date('d/m/Y',$model->created_at)
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'value' => date('d/m/Y',$model->updated_at)
                                ],
                            ],
                        ]);
                        break;
                    case ApiCredential::TYPE_IOS_APPLICATION:
                        echo DetailView::widget([
                            'model' => $model,
                            'attributes' => [
                                'client_name',             // title attribute (in plain text)
                                'description:ntext',
                                'client_api_key',             // title attribute (in plain text)
                                'client_secret',  // description attribute in HTML
                                [
                                    'attribute' => 'type',
                                    'value' => ApiCredential::$api_key_types[$model->type]
                                ],
                                [
                                    'attribute' => 'status',
                                    'value' => ApiCredential::getListStatusNameByStatus($model->status)
                                ],
                                [
                                    'attribute' => 'created_at',
                                    'value' => date('d/m/Y',$model->created_at)
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'value' => date('d/m/Y',$model->updated_at)
                                ],
                            ],
                        ]);
                        break;
                } ?>
            </div>

        </div>
    </div>
</div>