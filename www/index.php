<?php

require '../vendor/autoload.php';

$utils = dirname(__FILE__) . '/../protected/utils.php';
require_once($utils);

$yii = dirname(__FILE__).'/../yii/framework/yii.php';
$config = dirname(__FILE__) . '/../protected/config/' . (is_developer() ? "dev.php" : "main.php");

if(is_developer()) {
	defined('YII_DEBUG') or define('YII_DEBUG', true);
	defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
}

require_once($yii);
Yii::createWebApplication($config)->run();
