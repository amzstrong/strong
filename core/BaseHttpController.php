<?php

namespace Strong\core;

abstract class BaseHttpController {
    
    private $allConfig = null;
    protected $database = null;
    
    abstract protected function init();
    
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
    
    
    public function out($data, $code = 200, $msg = 'ok', $timeUsed = '') {
        $res['code'] = $code;
        $res['msg']  = $msg;
        $res['data'] = $data;
        if ($timeUsed) {
            $res['timeUsed'] = $timeUsed;
        }
        echo json_encode($res);
        return;
    }
    
    public function getRequest($arg, $default = '', $type = 'get') {
        if ($type == 'get') {
            $arg = isset($_GET[$arg]) ? $_GET[$arg] : $default;
        } else {
            $arg = isset($_POST[$arg]) ? $_POST[$arg] : $default;
        }
        return $arg;
    }
}