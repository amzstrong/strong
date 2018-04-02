<?php

namespace Strong\Core;


use testa\controller\IndexController;
use Workerman\Protocols\Http;
use Workerman\Worker;


class StrongHttp extends Worker {
    /**
     * Virtual host to path mapping.
     *
     * @var array ['workerman.net'=>'/home', 'www.workerman.net'=>'home/www']
     */
    protected $serverRoot = array();
    
    /**
     * Mime mapping.
     *
     * @var array
     */
    protected static $mimeTypeMap = array();
    
    
    /**
     * Used to save user OnWorkerStart callback settings.
     *
     * @var callback
     */
    protected $_onWorkerStart = null;
    
    
    protected $documentRoot = null;
    
    
    protected static $allConfig = null;
    
    /**
     * Add virtual host.
     *
     * @param string $domain
     * @param string $root_path
     * @return void
     */
    public function addRoot($domain, $root_path) {
        $this->serverRoot[$domain] = $root_path;
    }
    
    public function setPidFile($path, $name) {
        if (!is_dir($path)) {
            if (mkdir($path, 0777, true)) {
                parent::$pidFile = $path . $name;
            } else {
                throw new \Exception('pid path is invalid or no permission ');
            }
        }
    }
    
    public function setLogFile($path, $name) {
        if (!is_dir($path)) {
            if (mkdir($path, 0777, true)) {
                parent::$logFile = $path . $name;
            } else {
                throw new \Exception('pid path is invalid or no permission ');
            }
        }
    }
    
    public function setStdoutFile($path, $name) {
        if (!is_dir($path)) {
            if (mkdir($path, 0777, true)) {
                parent::$stdoutFile = $path . $name;
            } else {
                throw new \Exception('pid path is invalid or no permission ');
            }
        }
    }
    
    /**
     * Construct.
     *
     * @param string $socket_name
     * @param array $context_option
     */
    public function __construct($socket_name, $context_option = array()) {
        list(, $address) = explode(':', $socket_name, 2);
        parent::__construct('http:' . $address, $context_option);
        $this->name = 'WebServer';
    }
    
    /**
     * Run webserver instance.
     *
     * @see Workerman.Worker::run()
     */
    public function run() {
        $this->_onWorkerStart = $this->onWorkerStart;
        $this->onWorkerStart  = array($this, 'onWorkerStart');
        $this->onMessage      = array($this, 'onMessage');
        parent::run();
    }
    
    /**
     * Emit when process start.
     *
     * @throws \Exception
     */
    public function onWorkerStart() {
        self::loadConfig();
        if (empty($this->serverRoot)) {
            echo new \Exception('server root not set, please use WebServer::addRoot($domain, $root_path) to set server root path');
            exit(250);
        }
        
        // Init mimeMap.
        $this->initMimeTypeMap();
        
        // Try to emit onWorkerStart callback.
        if ($this->_onWorkerStart) {
            try {
                call_user_func($this->_onWorkerStart, $this);
            } catch (\Exception $e) {
                self::log($e);
                exit(250);
            } catch (\Error $e) {
                self::log($e);
                exit(250);
            }
        }
    }
    
    
    /**
     * Init mime map.
     *
     * @return void
     */
    public function initMimeTypeMap() {
        $mime_file = Http::getMimeTypesFile();
        if (!is_file($mime_file)) {
            $this->log("$mime_file mime.type file not fond");
            return;
        }
        $items = file($mime_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!is_array($items)) {
            $this->log("get $mime_file mime.type content fail");
            return;
        }
        foreach ($items as $content) {
            if (preg_match("/\s*(\S+)\s+(\S.+)/", $content, $match)) {
                $mime_type                      = $match[1];
                $workerman_file_extension_var   = $match[2];
                $workerman_file_extension_array = explode(' ', substr($workerman_file_extension_var, 0, -1));
                foreach ($workerman_file_extension_array as $workerman_file_extension) {
                    self::$mimeTypeMap[$workerman_file_extension] = $mime_type;
                }
            }
        }
    }
    
    /**
     * Emit when http message coming.
     *
     * @param Connection\TcpConnection $connection
     * @return void
     */
    
    public function onMessage($connection) {

        $workerman_root_dir = isset($this->serverRoot[$_SERVER['SERVER_NAME']]) ? $this->serverRoot[$_SERVER['SERVER_NAME']] : current($this->serverRoot);
        $workerman_root_dir = rtrim($workerman_root_dir, '/');
        if (!is_dir($workerman_root_dir)) {
            $connection->close("document set error!");
            return;
        }
        $REQUEST_URI = $_SERVER['REQUEST_URI'];
        $URI         = preg_replace('/\?.*/', '', $REQUEST_URI);
        
        if ($URI == '/' || empty($URI)) {
            $URI = '/index/index';
        }
        $uriFile     = $workerman_root_dir . $URI;
        $uriFileInfo = pathinfo($uriFile);
        if (isset($uriFileInfo['extension']) && $uriFileInfo['extension'] != 'php') {
            if (is_file($uriFile)) {
                return self::sendFile($connection, $uriFile);
            } else {
                Http::header('HTTP/1.1 400 Bad Request');
                $connection->close('<h1>400 Bad Request</h1>');
                return;
            }
        }
        $workerman_cwd = getcwd();
        chdir($workerman_root_dir);
        ini_set('display_errors', 'off');
        ob_start();
        try {
            $_SERVER['REMOTE_ADDR'] = $connection->getRemoteIp();
            $_SERVER['REMOTE_PORT'] = $connection->getRemotePort();
            $code                   = Router::r($URI, self::$allConfig);
            if ($code && !Strong_debug) {
                Http::header('HTTP/1.1 ' . $code . ' Bad Request');
                $connection->close('<h1>' . $code . ' Bad Request</h1>');
            }
        } catch (\Exception $e) {
            if ($e->getMessage() != 'jump_exit') {
                echo $e;
            }
        }
        $content = ob_get_clean();
        ini_set('display_errors', 'on');
        if (strtolower($_SERVER['HTTP_CONNECTION']) === "keep-alive") {
            $connection->send($content);
        } else {
            $connection->close($content);
        }
        chdir($workerman_cwd);
    }
    
    public static function sendFile($connection, $file_path) {
        // Check 304.
        $info          = stat($file_path);
        $modified_time = $info ? date('D, d M Y H:i:s', $info['mtime']) . ' ' . date_default_timezone_get() : '';
        if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $info) {
            // Http 304.
            if ($modified_time === $_SERVER['HTTP_IF_MODIFIED_SINCE']) {
                // 304
                Http::header('HTTP/1.1 304 Not Modified');
                // Send nothing but http headers..
                $connection->close('');
                return;
            }
        }
        
        // Http header.
        if ($modified_time) {
            $modified_time = "Last-Modified: $modified_time\r\n";
        }
        $file_size = filesize($file_path);
        $file_info = pathinfo($file_path);
        $extension = isset($file_info['extension']) ? $file_info['extension'] : '';
        $file_name = isset($file_info['filename']) ? $file_info['filename'] : '';
        $header    = "HTTP/1.1 200 OK\r\n";
        if (isset(self::$mimeTypeMap[$extension])) {
            $header .= "Content-Type: " . self::$mimeTypeMap[$extension] . "\r\n";
        } else {
            $header .= "Content-Type: application/octet-stream\r\n";
            $header .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
        }
        $header .= "Connection: keep-alive\r\n";
        $header .= $modified_time;
        $header .= "Content-Length: $file_size\r\n\r\n";
        $trunk_limit_size = 1024 * 1024;
        if ($file_size < $trunk_limit_size) {
            return $connection->send($header . file_get_contents($file_path), true);
        }
        $connection->send($header, true);
        
        // Read file content from disk piece by piece and send to client.
        $connection->fileHandler = fopen($file_path, 'r');
        $do_write                = function () use ($connection) {
            // Send buffer not full.
            while (empty($connection->bufferFull)) {
                // Read from disk.
                $buffer = fread($connection->fileHandler, 8192);
                // Read eof.
                if ($buffer === '' || $buffer === false) {
                    return;
                }
                $connection->send($buffer, true);
            }
        };
        // Send buffer full.
        $connection->onBufferFull = function ($connection) {
            $connection->bufferFull = true;
        };
        // Send buffer drain.
        $connection->onBufferDrain = function ($connection) use ($do_write) {
            $connection->bufferFull = false;
            $do_write();
        };
        $do_write();
    }
    
    public static function loadConfig() {
        foreach (AppConst::HTTP_ENV as $k => $v) {
            if (is_dir(MODULE_PATH . "/config/$v")) {
                $config              = new \Sinergi\Config\Config(MODULE_PATH . "/config/$v");
                self::$allConfig[$v] = $config;
            }
        }
    }
    
}
