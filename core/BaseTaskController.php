<?php

namespace Strong\core;

abstract class BaseTaskController {
    
    public $taskData = null;

    private $allConfig = null;

    protected $database = null;

    abstract protected function init();
    
    abstract public function getMsg($queue);
    
    abstract public function deleteMsg();
    
    protected function getConfig($fileName, $key = null) {
        if (is_null($key)) {
            return $this->allConfig->get($fileName);
        } else {
            return $this->allConfig->get($fileName)[$key] ?: [];
        }
    }

    public function __construct($config) {
        $this->allConfig = $config;
        $this->database  = $this->getConfig('database');
        $this->init();
    }
}