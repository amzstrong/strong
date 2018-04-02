<?php


namespace Strong\Core;


class Router {
    
    public static $env = null;
    public static $branch = null;
    
    public static function init() {
        $http_host = $_SERVER['HTTP_HOST'];
        $p         = '/^([^-]*)-([^-]*)-([^:]*)(:?.*)/';
        $res       = preg_match($p, $http_host, $match);
        $branch    = 'master';
        $env       = 'pro';
        if ($res) {
            $branch = $match[1];
            $env    = $match[2];
            $env    = in_array($env, AppConst::HTTP_ENV) ? $env : 'test';
        }
        self::$branch = $branch;
        self::$env    = $env;
        return;
    }
    
    public static function r($uri, $allEnvConfig) {
        gc_enable();
        self::init();
        $oneEnvConfig = $allEnvConfig[self::$env];
        $uri          = trim($uri, '/');
        $rout         = explode('/', $uri);
        if (empty($rout)) return Error::error('route error');
        $module              = MODULE;
        $controller          = isset($rout[0]) ? $rout[0] : 'index';
        $action              = isset($rout[1]) ? $rout[1] : 'index';
        $controllerClassName = ucfirst($controller) . "Controller";
        $actionName          = "action" . ucfirst($action);
        $class               = "\\$module\\controller\\$controllerClassName";
        if (class_exists($class)) {
            $classOb = new $class($oneEnvConfig);
        } else {
            return Error::error($class . ' class not found' . PHP_EOL);
        }
        if (!method_exists($classOb, $actionName)) {
            return Error::error("method $actionName not found in  $class " . PHP_EOL);
        }
        $classOb->$actionName();
        unset($classOb);
        return;
    }
    
    
}