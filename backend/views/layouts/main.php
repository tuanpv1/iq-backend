<?php
use backend\assets\AppAsset;
use common\models\ParamAttribute;
use common\widgets\Alert;
use common\widgets\Nav;
use common\widgets\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
$this->registerJs("Metronic.init();");
$this->registerJs("Layout.init();");
$arrlang =  array();
$multi = \common\models\Multilanguage::findAll(['status'=>\common\models\Multilanguage::STATUS_ACTIVE]);
foreach($multi as $item){
    $name = ['label' => $item->name,'url'=>['site/index','lang'=>$item->code]];
    array_push($arrlang,$name);
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="page-header-menu-fixed">
<?php $this->beginBody() ?>
<div class="page-header">
<?php
NavBar::begin([
    'brandLabel' => '<img src="' . Url::to("@web/img/logo_tvod.png") . '" alt="logo" class="logo-default"  width="150" style="margin-top:10px;"/>',
    'brandUrl' => Yii::$app->homeUrl,
    'brandOptions' => [
        'class' => 'page-logo'
    ],
    'renderInnerContainer' => true,
    'innerContainerOptions' => [
        'class' => 'container-fluid'
    ],
    'options' => [
        'class' => 'page-header-top',
    ],
    'containerOptions' => [
        'class' => 'top-menu'
    ],
]);
if (Yii::$app->user->isGuest) {
    $rightItems[] = [
        'encode' => false,
        'label' => '<i class="icon-user"></i><span class="username username-hide-on-mobile">'.Yii::t('app','Đăng nhập').'</span>',
        'url' => Yii::$app->urlManager->createUrl("site/login"),
        'options' => [
            'class' => 'dropdown dropdown-user'
        ],
        'linkOptions' => [
            'class' => "dropdown-toggle",
        ],
    ];
} else {
    $rightItems[] = [
        'encode' => false,
        'label' => '<img alt="" class="img-circle" src="' . Url::to("@web/img/haha.png") . '"/>
					<span class="username username-hide-on-mobile">
						 ' . Yii::$app->user->identity->username . '
					</span>',
        'options' => ['class' => 'dropdown dropdown-user dropdown-dark'],
        'linkOptions' => [
            'data-hover' => "dropdown",
            'data-close-others' => "true"
        ],
        'url' => 'javascript:;',
        'items' => [
            [
                'encode' => false,
                'label' => '<i class="icon-user"></i>'.Yii::t('app','Thông tin tài khoàn').'</a>',
                'url' => ['user/info']
            ],
            [
                'encode' => false,
                'label' => '<i class="icon-key"></i>'.Yii::t('app','Đăng xuất'),
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post'],
            ],
        ]
    ];
}
echo Nav::widget([
    'options' => ['class' => 'navbar-nav pull-right'],
    'items' => $rightItems,
    'activateParents' => true
]);
NavBar::end();
?>

<?php
NavBar::begin([
    'renderInnerContainer' => true,
    'innerContainerOptions' => [
        'class' => 'container-fluid'
    ],
    'options' => [
        'class' => 'page-header-menu',
        'style' => 'display: block;'
    ],
    'containerOptions' => [
        'class' => 'hor-menu'
    ],
    'toggleBtn' => false
]);
$menuItems = [

    [
        'label' => Yii::t('app','Nhà cung cấp dịch vụ'),
        'url' => 'javascript:;',
        'options' => ['class' => 'menu-dropdown mega-menu-dropdown'],
        'linkOptions' => ['data-hover' => 'megamenu-dropdown', 'data-close-others' => 'true'],
        'items' => [
            [
                'encode' => false,
                'label' => '<i class=" icon-layers"></i>'.Yii::t('app','Quản lý nhà cung cấp dịch vụ'),
                'url' => ['service-provider/index'],
                'require_auth' => true,
            ],
            [
                'encode' => false,
                'label' => '<i class="icon-badge"></i>'.Yii::t('app','Tạo mới nhà cung cấp dịch vụ'),
                'url' => ['service-provider/create'],
                'require_auth' => true,
            ],
            [
                'encode' => false,
                'label' => '<i class="icon-badge"></i>'.Yii::t('app','Độ trễ nội dung theo nhà cung cấp dịch vụ'),
                'url' => ['delay/index'],
                'require_auth' => true,
            ],
        ]
    ],
    [
        'label' => Yii::t('app','Nhà cung cấp nội dung'),
        'url' => 'javascript:;',
        'options' => ['class' => 'menu-dropdown mega-menu-dropdown'],
        'linkOptions' => ['data-hover' => 'megamenu-dropdown', 'data-close-others' => 'true'],
        'items' => [
            [
                'encode' => false,
                'label' => '<i class=" icon-layers"></i>'.Yii::t('app','Quản lý nhà cung cấp nội dung'),
                'url' => ['content-provider/index'],
                'require_auth' => true,
            ],
        ]
    ],
    [
        'label' => Yii::t('app','Nội dung'),
        'url' => 'javascript:;',
        'options' => ['class' => 'menu-dropdown mega-menu-dropdown'],
        'linkOptions' => ['data-hover' => 'megamenu-dropdown', 'data-close-others' => 'true'],
        'items' => [
            [
                'label' => 'Phim',
                'items' => [
                    [
                        'encode' => false,
                        'label' => '<i class="icon-film"></i>'.Yii::t('app','Danh mục Phim'),
                        'url' => ['category/index', 'type' => \common\models\Category::TYPE_FILM],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-film"></i>'.Yii::t('app','Phim'),
                        'url' => ['content/index', 'type' => \common\models\Category::TYPE_FILM],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.Yii::t('app','Quản lý Diễn viên/Đạo diễn'),
                        'url' => ['actor-director/index','content_type'=>\common\models\ActorDirector::TYPE_VIDEO],
                        'require_auth' => true,
                    ],

                ]
            ],
            [
                'label' => 'Clip',

                'items' => [
                    [
                        'encode' => false,
                        'label' => '<i class="icon-social-youtube"></i>'.\Yii::t('app', 'Danh mục Clip'),
                        'url' => ['category/index', 'type' => \common\models\Category::TYPE_CLIP],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-social-youtube"></i>'.\Yii::t('app', 'Clip'),
                        'url' => ['content/index', 'type' => \common\models\Category::TYPE_CLIP],
                        'require_auth' => true,
                    ],

                ]
            ],
            [
                'label' => ''.\Yii::t('app', 'Live'),

                'items' => [

                    [
                        'encode' => false,
                        'label' => '<i class="icon-book-open"></i>'.\Yii::t('app', 'Danh mục Live'),
                        'url' => ['category/index', 'type' => \common\models\Category::TYPE_LIVE],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-book-open"></i>'.\Yii::t('app', 'Lives'),
                        'url' => ['content/index', 'type' => \common\models\Category::TYPE_LIVE],
                        'require_auth' => true,
                    ],

                ]
            ],
            [
                'label' => ''.\Yii::t('app', 'Âm nhạc'),

                'items' => [

                    [
                        'encode' => false,
                        'label' => '<i class="icon-music-tone-alt"></i>'.\Yii::t('app', 'Danh mục Âm nhạc'),
                        'url' => ['category/index', 'type' => \common\models\Category::TYPE_MUSIC],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-music-tone-alt"></i>'.\Yii::t('app', 'Âm nhạc'),
                        'url' => ['content/index', 'type' => \common\models\Category::TYPE_MUSIC],
                        'require_auth' => true,
                    ],

                ]
            ],

            [
                'label' => ''.\Yii::t('app', 'Tin tức'),

                'items' => [

                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Danh mục Tin tức'),
                        'url' => ['category/index', 'type' => \common\models\Category::TYPE_NEWS],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Tin tức'),
                        'url' => ['content/index', 'type' => \common\models\Category::TYPE_NEWS],
                        'require_auth' => true,
                    ],

                ]
            ],
            [
                'label' => ''.\Yii::t('app', 'Karaoke'),

                'items' => [

                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Danh mục Karaoke'),
                        'url' => ['category/index', 'type' => \common\models\Category::TYPE_KARAOKE],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Karaoke'),
                        'url' => ['content/index', 'type' => \common\models\Category::TYPE_KARAOKE],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Quản lý Ca sĩ/Nhạc sĩ'),
                        'url' => ['actor-director/index','content_type'=>\common\models\ActorDirector::TYPE_KARAOKE],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Release Static Data'),
                        'url' => ['content/release-static-data'],
                        'require_auth' => true,
                    ],

                ]
            ],
            [
                'label' => ''.\Yii::t('app', 'Radio'),

                'items' => [

                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Danh mục Radio'),
                        'url' => ['category/index', 'type' => \common\models\Category::TYPE_RADIO],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Radio'),
                        'url' => ['content/index', 'type' => \common\models\Category::TYPE_RADIO],
                        'require_auth' => true,
                    ],

                ]
            ],
            [
                'label' => ''.\Yii::t('app', 'Live Content'),
                'url' => ['content/index', 'type' => \common\models\Category::TYPE_LIVE_CONTENT],
                'require_auth' => true,
            ],
            [
                'label' => ''.\Yii::t('app', 'Lịch sử phân phối nội dung'),
                'url' => ['log-sync-content/index'],
                'require_auth' => true,
            ],
            [
                'label' => 'Content activity',

                'items' => [

                    [
                        'encode' => false,
                        'label' => '<i class="icon-docs"></i>'.\Yii::t('app', 'Content Log'),
                        'url' => ['content-log/index'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-list"></i>'.\Yii::t('app', 'Quản lý Content Feedback'),
                        'url' => ['content-feedback/index'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-list"></i>'.\Yii::t('app', 'Quản lý Thuộc tính nội dung'),
                        'url' => ['content-attribute/index'],
                        'require_auth' => true,
                    ],
                ]
            ],
        ]
    ],
    [
        'label' => ''.\Yii::t('app', 'Gói cước'),
        'url' => 'javascript:;',
        'options' => ['class' => 'menu-dropdown mega-menu-dropdown'],
        'linkOptions' => ['data-hover' => 'megamenu-dropdown', 'data-close-others' => 'true'],
        'items' => [
            [
                'encode' => false,
                'label' => '<i class="icon-list"></i>'.\Yii::t('app', 'Danh mục Nhóm Gói cước'),
                'url' => ['service-group/index'],
                'require_auth' => true,
            ],
            [
                'encode' => false,
                'label' => '<i class="icon-basket"></i>'.\Yii::t('app', 'Danh mục Gói cước'),
                'url' => ['service/index', 'type' => \common\models\Category::TYPE_FILM],
                'require_auth' => true,
            ],
            [
                'encode' => false,
                'label' => '<i class="icon-basket"></i>'.\Yii::t('app', 'Danh mục mức nạp'),
                'url' => ['price-card/index'],
                'require_auth' => true,
            ],
        ]
    ],
    [
        'label' => ''.\Yii::t('app', 'Báo cáo'),
        'url' => 'javascript:;',
        'options' => ['class' => 'menu-dropdown mega-menu-dropdown'],
        'linkOptions' => ['data-hover' => 'megamenu-dropdown', 'data-close-others' => 'true'],
        'items' => [
            [
                'label' => ''.\Yii::t('app', 'Thuê bao'),
                'items' => [
                    [
                        'encode' => false,
                        'label' => ''.\Yii::t('app', 'Số lượng thuê bao'),
                        'url' => ['report/subscriber-number'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => ''.\Yii::t('app', 'Số lượt truy cập'),
                        'url' => ['report/subscriber-activity'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => Yii::t('app','Thông kê gói cước'),
                        'url' => ['report/report-service-subscriber'],
                        'require_auth' => true,
                    ],
                ]
            ],

            [
                'label' => ''.\Yii::t('app', 'Tài chính'),
                'url' => ['report/revenues'],
                'require_auth' => true,
            	'items' => [
                    [
                        'encode' => false,
                        'label' => Yii::t('app','Doanh thu'),
                        'url' => ['report/revenues'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => Yii::t('app','Nạp tiền'),
                        'url' => ['report/topup'],
                        'require_auth' => true,
                    ],
                ]
            ],
            [
                'label' => ''.\Yii::t('app', 'Nội dung'),
                'items' => [
                    [
                        'label' => ''.\Yii::t('app', 'Thống kê nội dung'),
                        'url' => ['report/content'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => ''.\Yii::t('app', 'Nội dung hot'),
                        'url' => ['report/content-hot'],
                        'require_auth' => true,
                    ],
//                    [
//                        'encode' => false,
//                        'label' =>\Yii::t('app',  'Thống kê phiên bản'),
//                        'url' => ['report/content-profile'],
//                        'require_auth' => true,
//                    ],
                ]
            ],
            [
                'label' => ''.\Yii::t('app', 'Báo cáo MT'),
                'url' => ['report/mt'],
                'require_auth' => true,

            ],
//            [
//                'encode' => false,
//                'label' => Yii::t('app','Báo cáo nạp thẻ (voucher)'),
//                'url' => ['report/voucher-report'],
//                'require_auth' => true,
//            ],
        ]
    ],
    [
        'label' => ''.\Yii::t('app', 'Hệ thống'),
        'url' => 'javascript:;',
        'options' => ['class' => 'menu-dropdown mega-menu-dropdown'],
        'linkOptions' => ['data-hover' => 'megamenu-dropdown', 'data-close-others' => 'true'],
        'items' => [
            [
                'encode' => false,
                'label' => '<i class="fa fa-server"></i> '.\Yii::t('app', 'Cấu hình api '),
                'url' => ['param-attribute/index'],
                'require_auth' => true,
            ],
//            [
//                'encode' => false,
//                'label' => '<i class="fa fa-server"></i> '.\Yii::t('app', 'Quản lý IP '),
//                'url' => ['ip-address/index'],
//                'require_auth' => true,
//            ],
            [
                'encode' => false,
                'label' => '<i class="fa fa-server"></i> '.\Yii::t('app', 'Quản lý ngôn ngữ hệ thống'),
                'url' => ['multilanguage/index'],
                'require_auth' => true,
            ],
            [
                'encode' => false,
                'label' => '<i class="fa fa-server"></i> '.\Yii::t('app', 'Quản lý địa chỉ phân phối nội dung'),
                'url' => ['streaming-server/index'],
                'require_auth' => true,
            ],
            [
                'encode' => false,
                'label' => '<i class="icon-users"></i> '.\Yii::t('app', 'QL người dùng'),
                'url' => ['user/index'],
                'require_auth' => true,
            ],
            [
                'encode' => false,
                'label' => '<i class=" icon-eyeglasses"></i> '.\Yii::t('app', 'Lịch sử tương tác'),
                'url' => ['user-activity/index'],
                'require_auth' => true,
            ],
            [
                'label' => ''.\Yii::t('app', 'QL quyền'),
                'items' => [
                    [
                        'encode' => false,
                        'label' => '<i class="icon-key"></i> '.\Yii::t('app', 'QL quyền trang backend'),
                        'url' => ['rbac-backend/permission'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-key"></i> '.\Yii::t('app', 'QL quyền trang nhà cung cấp dịch vụ'),
                        'url' => ['rbac-sp/permission'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-key"></i> '.\Yii::t('app', 'QL quyền trang đại lý'),
                        'url' => ['rbac-cp/permission'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-key"></i> '.\Yii::t('app', 'QL quyền trang CP'),
                        'url' => ['rbac-cprovider/permission'],
                        'require_auth' => true,
                    ],
                ]
            ],
            [
                'label' => ''.\Yii::t('app', 'QL nhóm quyền'),
                'items' => [
                    [
                        'encode' => false,
                        'label' => '<i class="icon-lock-open"></i> '.\Yii::t('app', 'QL nhóm quyền trang backend'),
                        'url' => ['rbac-backend/role'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-lock-open"></i> '.\Yii::t('app', 'QL nhóm quyền trang nhà cung cấp dịch vụ'),
                        'url' => ['rbac-sp/role'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-lock-open"></i> '.\Yii::t('app', 'QL nhóm quyền trang đại lý'),
                        'url' => ['rbac-cp/role'],
                        'require_auth' => true,
                    ],
                    [
                        'encode' => false,
                        'label' => '<i class="icon-lock-open"></i> '.\Yii::t('app', 'QL nhóm quyền CP'),
                        'url' => ['rbac-cprovider/role'],
                        'require_auth' => true,
                    ],
                ]
            ],
        ]
    ],
     [
         'label' => Yii::t('app','Ngôn ngữ'),
         'url' => 'javascript:;',
         'options' => ['class' => 'menu-dropdown mega-menu-dropdown'],
         'linkOptions' => ['data-hover' => 'megamenu-dropdown', 'data-close-others' => 'true'],
         'items' => $arrlang
     ]
];
echo Nav::widget([
    'options' => ['class' => 'navbar-nav'],
    'items' => $menuItems,
    'activateParents' => true
]);
NavBar::end();
?>
</div>
<!-- BEGIN CONTAINER -->
<div class="page-container">
    <!--    <div class="page-head">-->
    <!--        <div class="container-fluid">-->
    <!--            <div class="page-title">-->
    <!--                <h1>--><?php //echo $this->title ?><!--</h1>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <div class="page-content">
        <div class="container-fluid">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                'options' => [
                    'class' => 'page-breadcrumb breadcrumb'
                ],
                'itemTemplate' => "<li>{link}<i class=\"fa fa-circle\"></i></li>\n",
                'activeItemTemplate' => "<li class=\"active\">{link}</li>\n"
            ]) ?>
            <?= Alert::widget() ?>
            <?= $content ?>
        </div>
    </div>
</div>
<!-- END CONTAINER -->

<!-- BEGIN FOOTER -->
<div class="page-footer footer">
    <div class="container-fluid">
        <p><b>&copy;Copyright  <?php echo date('Y'); ?> </b>. All Rights Reserved. <b>TVOD Backend</b>.
            Design By VIVAS Co.,Ltd.</p>
    </div>
</div>
<div class="scroll-to-top">
    <i class="icon-arrow-up"></i>
</div>
<!-- END FOOTER -->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
