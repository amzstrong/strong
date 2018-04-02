<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 10/18/17
 * Time: 4:19 PM
 */
namespace testa\controller;

use Medoo\Medoo;
use Strong\Core\LoadConfig;
use testa\utils\Helper;
use Strong\core\BaseTaskController;

class TestController extends BaseTaskController {
    
    protected $db;
    protected $ots;
    
    protected function init() {
        echo 'init' . PHP_EOL;
        $this->ots = $this->getConfig('ots')['a'];
        $this->db  = new Medoo($this->getConfig('database')['sanbai']);
    }
    
    public function deleteMsg() {
        return 1;
    }
    
    public function getMsg($queue) {
        return ['name' => 'test'];
    }
    
    public function actionTest1() {
        echo 'test-->test1----' . $this->taskData['name'] . PHP_EOL;
        print_r($this->ots);
        return 1;
    }
    
    public function actionTest2() {
        echo 'test-->test2----' . $this->taskData['name'] . PHP_EOL;
        print_r($this->ots);
        return 1;
    }
}




