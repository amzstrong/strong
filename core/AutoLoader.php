<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Strong\core;

/**
 * Autoload.
 */
class Autoloader {
    /**
     * Autoload root path.
     *
     * @var string
     */
    protected static $_autoloadRootPath = '';

    /**
     * Set autoload root path.
     *
     * @param string $root_path
     * @return void
     */
    public static function setRootPath($root_path) {
        self::$_autoloadRootPath = $root_path;
    }

    /**
     * Load files by namespace.
     *
     * @param string $name
     * @return boolean
     */
    public static function loadByNamespace($name) {

        $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
        
        if (strpos($name, 'Strong\\') === 0) {
            
            $class_file = APP_PATH . substr($class_path, strlen('Strong')) . '.php';
            
        } else {

            if (self::$_autoloadRootPath) {
                $class_file = self::$_autoloadRootPath . DIRECTORY_SEPARATOR . $class_path . '.php';
            }
            if (empty($class_file) || !is_file($class_file)) {
                $class_file = APP_PATH . DIRECTORY_SEPARATOR . "$class_path.php";
            }
        }
        if (is_file($class_file)) {
            require_once($class_file);
            if (class_exists($name, false)) {
                return true;
            }
        }
        return false;
    }

    public static function loadCommon($dir = APP_PATH . '/common') {
        $handle = opendir($dir);
        if ($handle) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' && $file != '..') {
                    $cur_path = $dir . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($cur_path)) {
                        self::loadCommon($cur_path);
                    } else {
                        include $cur_path;
                    }
                }
            }
            closedir($handle);
        }
        return true;

    }
}

spl_autoload_register('\Strong\core\Autoloader::loadByNamespace');