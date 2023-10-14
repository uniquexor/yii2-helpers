<?php
    define('YII_ENV', 'test');
    defined('YII_DEBUG') or define('YII_DEBUG', true);

    require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
    require __DIR__ .'/../vendor/autoload.php';

    $config = [
        'id' => 'yii2-helpers',
        'basePath' => __DIR__ . '/../',
    ];

    $application = new yii\console\Application($config);