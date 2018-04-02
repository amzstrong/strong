<?php
use Workerman\Worker;
use Strong\Core\StrongHttp;

require_once(dirname(dirname(__FILE__)) . '/index.php');


$webserver        = new StrongHttp('http://0.0.0.0:10008');
$webserver->user  = '';
$webserver->count = 1;

$path = "/alidata/logs";

$webserver->setPidFile($path . '/strongPid/', 'strong.http_server.php.pid');
$webserver->setLogFile($path . '/workerLog/', 'abc.com.log');
$webserver->setStdoutFile($path . '/strongStdout/', 'stdout.log');
$webserver->addRoot('abc.com', APP_PATH . '/testa/');

$webserver::runAll();