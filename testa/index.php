<?php

/*
 * 模块入口文件 必须
 */

require_once(dirname(dirname(__FILE__)) . '/Bootstrap.php');
defined('MODULE') or define('MODULE', basename(__DIR__));
defined('MODULE_PATH') or define('MODULE_PATH', __DIR__);
defined('CRON_ENV') or define('CRON_ENV', 'test');
defined('CONFIG_PATH') OR define('CONFIG_PATH', MODULE_PATH . '/config/' . CRON_ENV);
