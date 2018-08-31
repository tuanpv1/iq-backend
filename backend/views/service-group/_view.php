<?php

use common\models\SmsMoSyntax;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ServiceGroup */

 
?>

<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Thông tin nhóm gói cước') ?> "<?= $model->name ?>
                        "</span>
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
                            'attribute' => 'name',
                            'label' => 'Mã'
                        ],
                        [
                            'attribute' => 'display_name',
                            'label' => 'Tên hiển thị'
                        ],
                        [
                            'attribute' => 'description',
                            'label' => 'Mô tả'
                        ],
                        [
                            'attribute' => 'created_at',
                            'label' => 'Ngày tạo',
                            'value' => date('d/m/Y', $model->created_at)
                        ],
                        [
                            'attribute' => 'updated_at',
                            'label' => 'Ngày cập nhật',
                            'value' => date('d/m/Y', $model->updated_at)
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>