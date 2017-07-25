<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2017/7/14
 * Time: 17:40
 * Desc:
 */


namespace Kswoole\Server;

use \Lkk\LkkService;


class SwooleServer extends LkkService{

    public $conf;
    private $server;
    private $events;
    private $requests;
    private $splqueue; //标准库队列,非持久化工作
    private $redqueue; //redis持久化队列


    /**
     * 构造函数
     * SwooleServer constructor.
     * @param array $vars
     */
    public function __construct(array $vars = []) {
        parent::__construct($vars);

    }



    /**
     * 获取SWOOLE服务
     * @return mixed
     */
    public static function getServer() {
        return (is_null(self::$instance) || !is_array(self::$instance)) ? null : self::$instance->server;
    }


    private function setSplQueue() {
        $this->splqueue = new \SplQueue();
        //设置迭代后数据删除
        $this->splqueue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_DELETE);
    }


    public static function getSplQueue() {
        return (is_null(self::$instance) || !is_array(self::$instance)) ? null : self::$instance->splqueue;
    }


    private function setRedQueue() {
        //TODO
    }


    public static function getRedQueue() {
        return (is_null(self::$instance) || !is_array(self::$instance)) ? null : self::$instance->redqueue;
    }


    private function setSwooleRequest($request) {
        $reqUuid = self::getRequestUuid();
        $this->requests[$reqUuid] = $request;
    }


    private function unsetSwooleRequest($reqUuid='') {
        if(empty($reqUuid)) $reqUuid = self::getRequestUuid();
        unset($this->requests[$reqUuid]);
    }


    public static function getSwooleRequest($reqUuid='') {
        $res = null;
        if(is_object(self::$instance) && !empty(self::$instance)) {
            if(empty($reqUuid)) $reqUuid = self::getRequestUuid();
            $res = isset(self::$instance->requests[$reqUuid]) ? self::$instance->requests[$reqUuid] : null;
        }

        return $res;
    }



    /**
     * 设置配置
     * @param array $conf
     */
    public function setConf(array $conf) {
        $this->conf = $conf;
        return $this;
    }



    /**
     * 初始化
     * @return $this
     */
    public function initServer() {
        //检查是否已安装swoole和phalcon
        if(!extension_loaded('swoole')) {
            throw new \Exception("no swoole extension!");
        }elseif (!extension_loaded('phalcon')) {
            throw new \Exception("no phalcon extension!");
        }

        $httpCnf = $this->conf['http_server'];
        $this->server = new \swoole_http_server($httpCnf['host'], $httpCnf['port']);

        $servCnf = $this->conf['server_conf'];
        $this->server->set($servCnf);
        return $this;
    }



    /**
     * 获取外部扩展事件
     * @param string $eventName 事件名称
     * @return bool|mixed
     */
    public function getExtEvent(string $eventName) {
        return empty($eventName) ? false : (isset($this->events[$eventName]) ? $this->events[$eventName] : false);
    }



    /**
     * 当启动时
     */
    public function onStart($serv) {
        //TODO
        $modName = php_sapi_name();
        echo "Start:{$modName}\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        $this->setSplQueue();
        $this->setRedQueue();

        return $this;
    }


    /**
     * 当关掉时
     */
    public function onShutdown($serv) {
        //TODO
        echo "Shutdown\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }


    public function onWorkerStart($serv, $workerId) {
        //TODO
        echo "WorkerStart:{$workerId}\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }


    public function onWorkerStop($serv, $workerId) {
        //TODO
        echo "WorkerStop:{$workerId}\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }


    public function onConnect($serv, $fd, $fromId) {
        //TODO
        echo "onConnect:{$fromId}\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }


    public function onRequest($request, $response) {
        //TODO
        $modName = php_sapi_name();
        echo "onRequest:{$modName}\r\n";

        //注册捕获错误函数
        register_shutdown_function('SwooleServer::handleRequestFatal', $request);

        if ($request->server['request_uri'] == '/favicon.ico' || $request->server['path_info'] == '/favicon.ico') {
            return $response->end();
        }elseif (preg_match('/(.css|.js|.gif|.png|.jpg|.jpeg|.ttf|.woff|.ico)$/i', $request->server['request_uri']) === 1) {
            return $response->end();
        }

        $this->setGlobal($request);

        $requestMd5 = self::getRequestUuid();
        $GLOBALS[$requestMd5] = microtime(true);
        var_dump($requestMd5, $request);


        //var_dump($request);
        $date = date('Y-m-d H:i:s');
        $uniqid = uniqid('', true);
        //var_dump(',------------------------------,', $date, $uniqid, $_POST);
        $_POST['date'] = $date;
        $_POST['uniqid'] = $uniqid;

        //var_dump('==========================:', $date, $uniqid, $_POST);
        if(!isset($_POST['uniqid']) || $uniqid != $_POST['uniqid']) {
            var_dump('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!', $date, $uniqid, $_POST);
        }

        $response->end('hello world');
        unset($GLOBALS[$requestMd5]);
    }


    //获取请求的UUID
    public static function getRequestUuid() {
        $arr = array_merge($_GET, $_COOKIE, $_SERVER);
        sort($arr);
        $res = md5(serialize($arr));
        return $res;
    }


    public function afterResponse($request, $response) {
        $this->unsetGlobal();
        unset($request);
        unset($response);
    }


    public function onClose($serv, $fd, $fromId) {
        //TODO
        echo "onClose\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }



    public function onTask($serv, $taskId, $fromId, $data) {
        //TODO
        echo "onTask\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }


    public function onFinish($serv, $taskId, $data) {
        //TODO
        echo "onFinish\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }


    public function onPipeMessage($serv, $fromWorkerId, $message) {
        //TODO
        echo "onPipeMessage\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }



    public function onWorkerError($serv, $workerId, $workerPid, $exitCode) {
        //TODO
        echo "onWorkerError\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }



    public function onManagerStart($serv) {
        //TODO
        echo "onManagerStart\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }



    public function onManagerStop($serv) {
        //TODO
        echo "onManagerStop\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }


    /**
     * 添加事件
     * @param string $eventName 事件名称
     * @param callable $eventFunc 事件闭包函数
     * @param array $funcParam 事件参数
     * @return $this
     */
    public function addEvent(string $eventName, callable $eventFunc, array $funcParam=[]) {
        if(method_exists($this, $eventName) && substr($eventName, 0, 2)==='on') {
            $this->events[$eventName] = [
                'func' => $eventFunc,
                'parm' => $funcParam
            ];
        }

        return $this;
    }


    /**
     * 绑定事件
     */
    public function bindEvents() {
        $this->server->on('Start', [$this, 'onStart']);
        $this->server->on('Shutdown', [$this, 'onShutdown']);
        $this->server->on('WorkerStart', [$this, 'onWorkerStart']);
        $this->server->on('WorkerStop', [$this, 'onWorkerStop']);
        $this->server->on('Connect', [$this, 'onConnect']);
        $this->server->on('Request', [$this, 'onRequest']);
        $this->server->on('Close', [$this, 'onClose']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
        $this->server->on('PipeMessage', [$this, 'onPipeMessage']);
        $this->server->on('WorkerError', [$this, 'onWorkerError']);
        $this->server->on('ManagerStart', [$this, 'onManagerStart']);
        $this->server->on('ManagerStop', [$this, 'onManagerStop']);

        return $this;
    }


    //启动服务
    public function startServer() {
        $this->bindEvents();
        $this->server->start();

        return $this;
    }


    //关闭服务
    public function shutdownServer() {
        //重启所有worker进程
        $this->server->shutdown();
        return $this;
    }


    public function stopWorker() {
        //使当前worker进程停止运行
        $this->server->stop();
        return $this;
    }


    public function reloadWorkers() {
        $this->server->reload();
        return $this;
    }






    /**
     * 将原始请求信息转换到PHP超全局变量中
     */
    public function setGlobal($request) {
        $_REQUEST = $_SESSION = $_COOKIE = $_FILES = $_POST = $_SERVER = $_GET = [];

        if (isset($request->get)) $_GET = $request->get;
        if (isset($request->post)) $_POST = $request->post;
        if (isset($request->files)) $_FILES = $request->files;
        if (isset($request->cookie)) $_COOKIE = $request->cookie;
        if (isset($request->server)) $_SERVER = $request->server;

        //构造url请求路径,phalcon获取到$_GET['_url']时会定向到对应的路径，否则请求路径为'/'
        $_GET['_url'] = $request->server['request_uri'];

        $_REQUEST = array_merge($_GET, $_POST);

        //todo: necessary?
        foreach ($_SERVER as $key => $value) {
            $_SERVER[strtoupper($key)] = $value;
            unset($_SERVER[$key]);
        }
        $_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
        //$_SERVER['REQUEST_URI'] = $request->meta['uri'];
        $_SERVER['REQUEST_URI'] = $request->server['request_uri'];

        //将HTTP头信息赋值给$_SERVER超全局变量
        foreach ($request->header as $key => $value) {
            $_key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $_SERVER[$_key] = $value;
        }
        $_SERVER['REMOTE_ADDR'] = $request->server['remote_addr'];

        // swoole fix 初始化一些变量, 下面这些变量在进入真实流程时是无效的
        $_SERVER['PHP_SELF']        = '/index.php';
        $_SERVER['SCRIPT_NAME']     = '/index.php';
        $_SERVER['SCRIPT_FILENAME'] = '/index.php';
        $_SERVER['SERVER_ADDR']     = '127.0.0.1';
        $_SERVER['SERVER_NAME']     = 'localhost';
        //$_SERVER['DOCUMENT_ROOT']   = '';
        //$_SERVER['DOCUMENT_URI']    = '';

        //TODO
        //$_SESSION = $this->load($sessid);
    }


    public function unsetGlobal() {
        $_REQUEST = $_SESSION = $_COOKIE = $_FILES = $_POST = $_SERVER = $_GET = [];
    }



    public static function checkServerStatus() {

    }

    public static function cliStatus() {

    }

    public static function cliStart() {

    }

    public static function cliStop() {

    }

    public static function cliRestart() {

    }

    public static function cliReload() {

    }


    private static function handleRequestFatal($request) {
        $error = error_get_last();
        if (isset($error['type'])) {
            switch ($error['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                    $message = $error['message'];
                    $file    = $error['file'];
                    $line    = $error['line'];
                    $log     = "$message ($file:$line)\nStack trace:\n";
                    $trace   = debug_backtrace();
                    foreach ($trace as $i => $t) {
                        if (!isset($t['file'])) {
                            $t['file'] = 'unknown';
                        }
                        if (!isset($t['line'])) {
                            $t['line'] = 0;
                        }
                        if (!isset($t['function'])) {
                            $t['function'] = 'unknown';
                        }
                        $log .= "#$i {$t['file']}({$t['line']}): ";
                        if (isset($t['object']) and is_object($t['object'])) {
                            $log .= get_class($t['object']) . '->';
                        }
                        $log .= "{$t['function']}()\n";
                    }
                    if (isset($_SERVER['REQUEST_URI'])) {
                        $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
                    }

                    $request->end($log);
                    break;
                default:
                    break;
            }
        }
    }




}