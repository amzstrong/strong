<?php

namespace Strong\core;

use Workerman\Worker;
use Workerman\Lib\Timer;
use Exception;

class StrongTask {
    
    public static $pidFile = '';
    public static $stdoutFile = '';
    public static $workerLogFile = '';
    private $worker = null;
    public $workerNum = 1;
    public $crontab = [];
    public $database = [];
    public $allConfig = [];
    
    public function __construct($crontabName = AppConst::DEFAULT_TASK) {
        $this->loadConfig();
        $this->init($crontabName);
    }
    
    public function run() {
        $this->worker        = new Worker();
        $this->worker->count = $this->workerNum;
        if (self::$stdoutFile) $this->worker->stdoutFile = self::$stdoutFile;
        if (self::$workerLogFile) $this->worker->logFile = self::$workerLogFile;
        if (self::$pidFile) $this->worker->pifFile = self::$pidFile;
        $this->worker->onWorkerStart = function () {
            $this->exec($this->crontab, $this->worker);
        };
        $this->worker->runAll();
    }
    
    
    public function setPidFile($path, $name) {
        if (!is_dir($path)) {
            if (mkdir($path, 0777, true)) {
                self::$pidFile = $path . $name;
            } else {
                throw new \Exception('pid path is invalid or no permission ');
            }
        }
    }
    
    public function setWorkerLogFile($path, $name) {
        if (!is_dir($path)) {
            if (mkdir($path, 0777, true)) {
                self::$workerLogFile = $path . $name;
            } else {
                throw new \Exception('pid path is invalid or no permission ');
            }
        }
    }
    
    public function setLogFile($path, $name) {
        if (!is_dir($path)) {
            if (mkdir($path, 0777, true)) {
                self::$stdoutFile = $path . $name;
            } else {
                throw new \Exception('pid path is invalid or no permission ');
            }
        }
    }
    
    private function init($crontabName) {
        $database = $this->allConfig->get('database');
        $crontab  = $this->allConfig->get($crontabName);
        if (empty($crontab)) throw new Exception("$crontabName is empty");
        $sum = 0;
        foreach ($crontab ?: [] as $k => $v) {
            $sum_pre = $sum;
            $sum += $v['workerNum'];
            $ids                = range($sum_pre, ($sum - 1));
            $crontab[$k]['ids'] = $ids;
        }
        $this->workerNum = $sum;
        $this->crontab   = $crontab;
        $this->database  = $database;
    }
    
    private function exec($crontab, $worker) {
        foreach ($crontab ?: [] as $k => $v) {
            if (in_array($worker->id, $v['ids'])) {
                Timer::add($v['time'], function () use ($v, $worker) {
                    echo $worker->id . PHP_EOL;
                    gc_enable();
                    $module     = MODULE;
                    $controller = ucfirst($v['controller']) . 'Controller';
                    $action     = 'action' . ucfirst($v['action']);
                    $class      = "\\$module\\controller\\{$controller}";
                    try {
                        if (!class_exists($class)) \Strong\core\Error::error($class . ' not found');
                        $classObj           = new $class($this->allConfig);
                        $taskData           = $classObj->getMsg($v['queue']);
                        $classObj->taskData = $taskData;
                        if ($classObj->$action()) $classObj->deleteMsg($v['queue']);
                    } catch (Exception $e) {
                        \Strong\core\Error::error($e);
                    }
                    unset($classObj);
                });
            }
        }
    }
    
    private function loadConfig() {
        try {
            if (!is_dir(MODULE_PATH . "/config/" . CRON_ENV)) \Strong\core\Error::error(MODULE_PATH . "/config/" . CRON_ENV . " not found");
            $this->allConfig = new \Sinergi\Config\Config(MODULE_PATH . "/config/" . CRON_ENV);
        } catch (Exception $e) {
            echo $e;
            return;
        }
    }
    
}
