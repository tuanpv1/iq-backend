<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

$config = [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'components' => [
//        'cache' => [
//            'class' => 'yii\caching\DbCache',
//        ],
        'db' => [
            'enableSlaves' => false,
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                ],
            ],
        ],
    ],
    'params' => $params,
];


//if (YII_ENV == 'prod' && extension_loaded('apcu')) {
//    $config['components']['cache'] = [
//        'class' => 'yii\caching\ApcCache',
//        'useApcu' => true,
//    ];
//}

return $config;