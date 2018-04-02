<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 10/18/17
 * Time: 4:19 PM
 */
namespace testa\controller;

use Strong\core\BaseHttpController;
use Strong\Core\Router;
use testa\utils\Helper;

class IndexController extends BaseHttpController {
    
    public function init() {
        // TODO: Implement init() method.
    }
    
    public static function test() {
        Helper::testHelper();
    }
    
    
    public function actionIndex() {
        echo(json_encode(['moudle' => 'testa', 'controller' => 'index', 'action' => 'index', 'get' => $_GET, 'post' => $_POST, 'env' => Router::$env, 'branch' => Router::$branch]));
    }
    
}



