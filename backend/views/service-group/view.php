<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ServiceGroup */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Nhóm gói cước'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Nhóm gói cước:') ?>  "<?= $model->name ?>
                        "</span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse">
                    </a>
                </div>
            </div>
            <div class="portlet-body">

                <div class="tabbable-custom nav-justified">
                    <ul class="nav nav-tabs nav-justified">
                        <li class="<?php echo $active==1? 'active':'';?>">
                            <a href="#tab_info" data-toggle="tab">
                                <?= \Yii::t('app', 'Thông tin') ?></a>
                        </li>

                        <li class="<?php echo $active==2? 'active':'';?>">
                            <a href="#tab_service" data-toggle="tab">
                                <?= \Yii::t('app', 'Danh sách gói cước') ?> </a>
                        </li>

                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane <?php echo $active==1? 'active':'';?>" id="tab_info">
                            <?= $this->render('_view', [
                                'model' => $model

                            ]) ?>
                        </div>

                        <div class="tab-pane <?php echo $active==2? 'active':'';?>" id="tab_service">
                            <?= $this->render('_list_service', [
                                'serviceProvider' => $serviceProvider,

                            ]) ?>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
