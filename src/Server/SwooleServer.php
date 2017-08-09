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
use \Lkk\Helpers\ValidateHelper;
use \JJG\Ping;
use Phalcon\Di;
use Phalcon\Loader;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Router;
use Phalcon\Http\Request;
use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Kswoole\Server\TimerTaskManager;

class SwooleServer extends LkkService {

    public $conf;
    private $server;
    private $events;
    private $requests; //请求资源
    private $splqueue; //标准库队列,非持久化工作
    private $redqueue; //redis持久化队列

    private $servName; //服务名
    private $listenIP; //监听IP
    private $listenPort; //监听端口

    //定时任务管理器
    private $timerTaskManager;

    //命令行操作列表
    public static $cliOperations = [
        'status',
        'start',
        'stop',
        'restart',
        'reload',
        'kill',
    ];
    private static $cliOperate; //当前命令操作
    private static $daemonize; //是否以守护进程启动
    private static $pidFile;   //pid文件

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
        return (is_null(self::$instance) || !is_object(self::$instance)) ? null : self::$instance->server;
    }


    //获取定时任务管理器
    public static function getTimerTaskManager() {
        return (is_null(self::$instance) || !is_object(self::$instance)) ? null : self::$instance->timerTaskManager;
    }


    private function setSplQueue() {
        $this->splqueue = new \SplQueue();
        //设置迭代后数据删除
        $this->splqueue->setIteratorMode(\SplDoublyLinkedList::IT_MODE_FIFO | \SplDoublyLinkedList::IT_MODE_DELETE);
    }


    public static function getSplQueue() {
        return (is_null(self::$instance) || !is_object(self::$instance)) ? null : self::$instance->splqueue;
    }


    private function setRedQueue() {
        //TODO
    }


    public static function getRedQueue() {
        return (is_null(self::$instance) || !is_object(self::$instance)) ? null : self::$instance->redqueue;
    }


    private function setSwooleRequest($request) {
        $requestId = self::getRequestUuid();
        $this->requests[$requestId] = $request;
    }


    private function unsetSwooleRequest($requestId='') {
        if(empty($requestId)) $requestId = self::getRequestUuid();
        unset($this->requests[$requestId]);
    }


    public static function getSwooleRequest($requestId='') {
        $res = null;
        if(is_object(self::$instance) && !empty(self::$instance)) {
            if(empty($requestId)) $requestId = self::getRequestUuid();
            $res = isset(self::$instance->requests[$requestId]) ? self::$instance->requests[$requestId] : null;
        }

        return $res;
    }


    //用法
    public static function cliUsage() {
        $operates = implode(' | ', self::$cliOperations);
        echo "usage:\r\nphp app/index.php {$operates} [-d]\r\n";
    }


    //解析CLI命令参数
    public static function parseCommands() {
        //销毁旧对象
        self::destroy();

        global $argv;
        self::$cliOperate = isset($argv[1]) ? strtolower($argv[1]) : '';
        self::$daemonize = (isset($argv[2]) && '-d'==strtolower($argv[2])) ? 1 : 0;

        if(!in_array(self::$cliOperate, self::$cliOperations)) {
            self::cliUsage();
            exit(1);
        }
    }



    public static function getDaemonize() {
        return intval(self::$daemonize);
    }


    /**
     * 设置配置
     * @param array $conf
     */
    public function setConf(array $conf) {
        $this->conf = $conf;
        $this->servName = $this->conf['server_name'];
        $this->listenIP = $this->conf['http_server']['host'];
        $this->listenPort = $this->conf['http_server']['port'];

        self::$pidFile = self::getPidPath($conf);
        var_dump('pid:', self::$pidFile);

        return $this;
    }


    //检查扩展
    public static function checkExtensions() {
        $res = true;
        //检查是否已安装swoole和phalcon
        if(!extension_loaded('swoole')) {
            print_r("no swoole extension!\n");
            $res = false;
        }elseif (!extension_loaded('phalcon')) {
            print_r("no phalcon extension!\n");
            $res = false;
        }elseif (!extension_loaded('inotify')) {
            print_r("no inotify extension!\n");
            $res = false;
        }elseif (!extension_loaded('redis')) {
            print_r("no redis extension!\n");
            $res = false;
        }elseif (!extension_loaded('pdo')) {
            print_r("no pdo extension!\n");
            $res = false;
        }elseif (!class_exists('swoole_redis')) {
            print_r("Swoole compilation is missing --enable-async-redis!\n");
            $res = false;
        }

        return $res;
    }


    //执行
    public function run() {
        $chkExts = self::checkExtensions();
        if(!$chkExts) exit(1);

        $pidExis = file_exists(self::$pidFile);
        $masterIsAlive = false;
        $masterPid = $managerPid = 0;
        if($pidExis) {
            $pids = explode(',', file_get_contents(self::$pidFile));
            $masterPid = $pids[0];
            $managerPid = $pids[1];
            $masterIsAlive = $masterPid && @posix_kill($masterPid, 0);
        }

        $binded  = ValidateHelper::isPortBinded('127.0.0.1', $this->listenPort);
        /*$ping = new Ping('127.0.0.1');
        $ping->setPort($this->listenPort);
        $binded = $ping->ping('fsockopen');*/
        var_dump('$binded', $binded);

        $msg = '';
        switch (self::$cliOperate) {
            case 'status' : //查看服务状态
                if($masterIsAlive) {
                    $msg .= "Service $this->servName is running...\r\n";
                }else{
                    $msg .= "Service $this->servName not running!!!\r\n";
                }

                if($binded) {
                    $msg .= "Port $this->listenPort is binded...\r\n";
                }else{
                    $msg .= "Port $this->listenPort not binded!!!\r\n";
                }

                echo $msg;
                break;
            case 'start' :
                if($masterIsAlive) {
                    $msg .= "Service $this->servName already running...\r\n";
                    echo $msg;
                    exit(1);
                }elseif ($binded) {
                    $msg .= "Port $this->listenPort already binded...\r\n";
                    echo $msg;
                    exit(1);
                }

                $this->initServer()->startServer();

                break;
            case 'stop' :
                if(!$binded) {
                    $msg = "Service $this->servName not running!!!\r\n";
                    echo $msg;
                    exit(1);
                }

                @unlink(self::$pidFile);
                echo("Service $this->servName is stoping ...\r\n");
                $masterPid && posix_kill($masterPid, SIGTERM);
                $timeout = 5;
                $startTime = time();
                while (1) {
                    $masterIsAlive = $masterPid && posix_kill($masterPid, 0);
                    if ($masterIsAlive) {
                        if (time() - $startTime >= $timeout) {
                            echo("Service $this->servName stop fail\r\n");
                            exit;
                        }
                        // Waiting amoment.
                        usleep(10000);
                        continue;
                    }
                    echo("Service $this->servName stop success\r\n");
                    break;
                }
                exit(0);
                break;
            case 'restart' :
                @unlink(self::$pidFile);
                echo("Service $this->servName is stoping ...\r\n");
                $masterPid && posix_kill($masterPid, SIGTERM);
                $timeout = 5;
                $startTime = time();
                while (1) {
                    $masterIsAlive = $masterPid && posix_kill($masterPid, 0);
                    if ($masterIsAlive) {
                        if (time() - $startTime >= $timeout) {
                            echo("Service $this->servName stop fail\r\n");
                            exit;
                        }
                        // Waiting amoment.
                        usleep(10000);
                        continue;
                    }
                    echo("Service $this->servName stop success\r\n");
                    break;
                }

                self::$daemonize = true;
                $this->initServer()->startServer();

                break;
            case 'reload' :
                posix_kill($managerPid, SIGUSR1);
                echo("Service $this->servName reload\r\n");
                exit(0);
                break;
            case 'kill' :
                @unlink(self::$pidFile);
                $bash = "ps -ef|grep {$this->servName}|grep -v grep|cut -c 9-15|xargs kill -9";
                exec($bash);
                break;
            default :
                self::cliUsage();
                exit(1);
                break;
        }

        return $this;
    }


    //设置进程标题
    public static function setProcessTitle($title) {
        // >=php 5.5
        if (function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        } // Need proctitle when php<=5.5 .
        else {
            @swoole_set_process_name($title);
        }
    }



    //获取PID文件路径
    public static function getPidPath($conf) {
        $res = '';
        if(empty($conf)) return $res;

        $fileName = strtolower($conf['server_name']) .'-'. $conf['http_server']['host'] .'-'. $conf['http_server']['port'] .'.pid';
        $res = rtrim(str_replace('\\', '/', $conf['pid_dir']), '/') . DS . $fileName;
        return $res;
    }



    /**
     * 初始化服务
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
        $servCnf['daemonize'] = self::getDaemonize();
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
        self::setProcessTitle($this->servName.'-Master');
        self::setMasterPid($serv->master_pid, $serv->manager_pid);

        //TODO
        $modName = php_sapi_name();
        echo "Master Start:{$modName}\r\n";

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
        echo "Master Shutdown\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }



    public function onManagerStart($serv) {
        self::setProcessTitle($this->servName.'-Manager');

        //TODO
        echo "Manager Start\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

    }



    public function onManagerStop($serv) {
        //TODO
        echo "Manager Stop\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

    }



    public function onWorkerStart($serv, $workerId) {
        self::setProcessTitle($this->servName.'-Worker');
        self::setWorketPid($serv->worker_pid);

        //最后一个worker处理启动定时器
        if ($workerId == $this->conf['server_conf']['worker_num'] - 1) {
            //启动定时器任务
            $this->timerTaskManager = new TimerTaskManager(['timerTasks'=>$this->conf['timer_tasks']]);
        }

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
        register_shutdown_function('\Kswoole\Server\SwooleServer::handleRequestFatal');

        if ($request->server['request_uri'] == '/favicon.ico' || $request->server['path_info'] == '/favicon.ico') {
            return $response->end();
        }elseif (preg_match('/(.css|.js|.gif|.png|.jpg|.jpeg|.ttf|.woff|.ico)$/i', $request->server['request_uri']) === 1) {
            return $response->end();
        }

        $this->setGlobal($request);

        $requestId = self::getRequestUuid();
        $GLOBALS[$requestId] = microtime(true);
        var_dump($requestId, $request);


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

        //处理请求
        //ob_start();
        try {
            $loader = new Loader();
            $loader->registerDirs(
                [
                    APPSDIR . 'controllers' .DS
                ]
            );
            $loader->register();

            $di = new Di();

            // Registering a router
            $di->set("router", Router::class);

            // Registering a dispatcher
            $di->set("dispatcher", MvcDispatcher::class);

            // Registering a Http\Response
            $di->set("response", Response::class);

            // Registering a Http\Request
            $di->set("request", Request::class);

            $di->set(
                "view",
                function () {
                    $view = new View();
                    $view->setRenderLevel(View::LEVEL_NO_RENDER);
                    $view->disable();
                    return $view;
                }
            );

            $app = new Application($di);
            //$resStr = 'Hello World';
            //TODO Allowed memory错误
            $resStr = $app->handle($request->server['request_uri'])->getContent();
        } catch (\Exception $e) {
            $resStr = $e->getMessage();
        }
        //$result = ob_get_contents();
        //ob_end_clean();
        $response->end($resStr);
        //$response->end('hello world');

        unset($GLOBALS[$requestId]);
    }


    //获取请求的UUID
    public static function getRequestUuid() {
        $arr = array_merge($_GET, $_COOKIE, $_SERVER);
        sort($arr);
        $res = isset($_SERVER['REQUEST_TIME_FLOAT']) ?
            (sprintf('%.0f', $_SERVER['REQUEST_TIME_FLOAT'] * 1000000) .crc32(md5(serialize($arr))))
            : md5(serialize($arr));
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


    public function onTask($serv, $taskId, $fromId, $taskData) {
        self::setProcessTitle($this->servName.'-Tasker');

        //TODO
        echo "onTask\r\n";

        //检查任务类型
        if(is_array($taskData) && isset($taskData['type'])) {
            switch ($taskData['type']) {
                case '' :default :

                    break;
                case \Kswoole\Server\ServerConst::SERVER_TASK_TIMER : //定时任务
                    call_user_func_array($taskData['message']['callback'], $taskData['message']['params']);
                    break;
            }
        }


        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
    }


    public function onFinish($serv, $taskId, $taskData) {
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

        echo("Service $this->servName start success\r\n");

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


    public static function handleRequestFatal() {
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

                    break;
                default:
                    break;
            }
        }
    }


    public static function setMasterPid($masterPid, $managerPid) {
        file_put_contents(self::$pidFile, $masterPid);
        file_put_contents(self::$pidFile, ',' . $managerPid, FILE_APPEND);
    }


    public static function setWorketPid($workerPid) {
        file_put_contents(self::$pidFile, ',' . $workerPid, FILE_APPEND);
    }





}