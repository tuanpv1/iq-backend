<?php

use common\models\UserActivity;
use kartik\detail\DetailView;
use yii\helpers\Html;
use yii\helpers\VarDumper;

/* @var $this yii\web\View */
/* @var $model common\models\UserActivity */

?>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'username',
            'ip_address',
            'user_agent',
            'action',
            'target_id',
            [
                'attribute' => 'target_type',
                'value' => UserActivity::actionTargets()[$model->target_type]
            ],
            'description:ntext',
            'status',
            'request_detail',
            [
                'attribute' => 'request_params',
                'format' => 'html',
                'value' => $model->contentParamsDetail
            ],
            [
                'attribute' => 'created_at',
                'value' => date('d/m/Y H:i:s', $model->created_at)
            ],
        ],
    ]) ?>
