<?php


/* @var $this yii\web\View */
/* @var $model common\models\ContentProvider */
/* @var $userAdminCp \common\models\User */

$this->title = $model->cp_name;
$this->params['breadcrumbs'][] = ['label' => '' . \Yii::t('app', 'Nhà cung cấp nội dung'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span
                        class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Nhà cung cấp nội dung') ?>
                        : <?php echo $model->cp_name; ?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>

                </div>
            </div>
            <div class="portlet-body">
                <div class="tabbable-custom ">
                    <ul class="nav nav-tabs ">
                        <li class="active">
                            <a href="#tab1" data-toggle="tab">
                                <?= \Yii::t('app', 'Thông tin chung') ?></a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <?= $this->render('_detail', ['model' => $model, 'userAdminCp' => $userAdminCp]) ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>