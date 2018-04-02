<?php

namespace Strong\core;

class Error {

    public function __construct($msg) {
        $res = $msg ?: 'a error';
        echo $res . PHP_EOL;
        return;
    }

    public static function error($msg = '') {
        $res = $msg ?: 'a error';
        if (Strong_debug) echo $res . PHP_EOL;
        return 500;
    }

    
}