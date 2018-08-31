<?php


$config = [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language' => 'vi', /**  set target language to be vi */
    'aliases' => [
        '@file_downloads' => 'static/file_dowloads',
        '@cat_image' => 'static/content_images',
        '@messages' => 'messages',
        '@content_images' => 'static/content_images',
        '@site' => '@sp',
        '@dealer' => '@cp',
        '@service_group_icon' => 'static/service_group_icon',
        '@storage_location' => '/storage/tvod2-backend',
        '@video_storage' => 'video',
        '@excel_folder' => "uploaded_excels",
        '@subtitle' => 'static/content_images/subtitle',
        '@file_customer' => 'static/file_customer',
        '@default_site_id' => 1,
        '@domain' => 'http://localhost',
        '@staticdata' => 'backend/web/staticdata',
        '@originVOD' => dirname(dirname(__DIR__)).'/originVOD',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\ApcCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'error', 'warning'],
                ],
            ],
        ],
        'i18n' => [
            'translations' => [
                'app*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                ],
                'frontend*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                ],
                'backend*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                ],
                'api*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                ],
                'log*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                ],
//                'zii*' => [
//                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath' => '@common/messages',
////                    'forceTranslation' => true
//                ],
//                'kvgrid*' => [
                //                    'class' => 'yii\i18n\PhpMessageSource',
                //                    'basePath' => '@common/messages',
                ////                    'forceTranslation' => true
                //                ],
            ],
        ],
        'response' => [
            'on beforeSend' => function($event) {
                $event->sender->headers->add('X-Frame-Options', 'DENY');
            },
        ],
    ],
    'timeZone' => 'Asia/Ho_Chi_Minh',
];


//if (YII_ENV == 'prod' && extension_loaded('apcu')) {
//    $config['components']['cache'] = [
//        'class' => 'yii\caching\ApcCache',
//        'useApcu' => true,
//    ];
//}


return $config;
