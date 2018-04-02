<?php

/*
 * 框架入口文件 必须
 */
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Asia/Shanghai');

defined('APP_PATH') or define('APP_PATH', __DIR__);
defined('Strong_debug') or define('Strong_debug', true);

require_once(APP_PATH . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/core/AutoLoader.php');
\Strong\core\Autoloader::loadCommon();



