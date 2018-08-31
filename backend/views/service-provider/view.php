<?php

use common\models\SmsMoSyntaxSearch;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Site */
/* @var $active int */
/* @var $user_admin common\models\User */
/* @var $serviceSearchModel common\models\ServiceSearch */
/* @var $serviceDataProvider common\models\ServiceSearch */
/* @var $moSearchModel SmsMoSyntaxSearch */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => ''.\Yii::t('app', 'Nhà cung cấp dịch vụ'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>



<div class="row">
    <div class="col-md-12">
        <div class="portlet light">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-cogs font-green-sharp"></i>
                    <span class="caption-subject font-green-sharp bold uppercase"><?= \Yii::t('app', 'Nhà cung cấp dịch vụ') ?> : <?php echo $model->name;?></span>
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                    </a>

                </div>
            </div>
            <div class="portlet-body">
                <div class="tabbable-custom ">
                    <ul class="nav nav-tabs ">
                        <li class="<?= ($active == 1) ? 'active' : '' ?>">
                            <a href="#tab1" data-toggle="tab" >
                                <?= \Yii::t('app', 'Thông tin chung') ?></a>
                        </li>
                        <li class=" <?= ($active == 2) ? 'active' : '' ?>">
                            <a href="#tab2" data-toggle="tab" >
                                <?= \Yii::t('app', 'Thông tin người quản trị') ?> </a>
                        </li>
                        <li class=" <?= ($active == 3) ? 'active' : '' ?>">
                            <a href="#tab3" data-toggle="tab" >
                                <?= \Yii::t('app', 'Địa chỉ phân phối nội dung') ?> </a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane <?= ($active == 1) ? 'active' : '' ?>" id="tab1">
                            <?=$this->render('_detail',['model'=>$model])?>
                        </div>
                        <div class="tab-pane <?= ($active == 2) ? 'active' : '' ?>" id="tab2">
                            <?php if ($user_admin){ ?>
                                <?=$this->render('view_user',['model'=>$user_admin])?>
                            <?php }else{ ?>
                                <p><?= \Yii::t('app', 'Không có tải khoản admin') ?></p>
                            <?php } ?>
                        </div>
                        <div class="tab-pane <?= ($active == 3) ? 'active' : '' ?>" id="tab3">
                            <?=$this->render('_list_streaming_servers',['site'=>$model,
                                'dataProvider' => $streamingServerDataProvider,
                                'primary_streaming_server_id' => $model->primary_streaming_server_id,
                            ])?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>