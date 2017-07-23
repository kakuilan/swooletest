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
        return is_null(self::$instance) ? null : self::$instance->server;
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

        //var_dump($request);
        $date = date('Y-m-d H:i:s');
        $uniqid = uniqid('', true);
        //var_dump(',------------------------------,', $date, $uniqid, $_POST);
        $_POST['date'] = $date;
        $_POST['uniqid'] = $uniqid;
        usleep(1000);

        //var_dump('==========================:', $date, $uniqid, $_POST);
        if(!isset($_POST['uniqid']) || $uniqid != $_POST['uniqid']) {
            var_dump('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!', $date, $uniqid, $_POST);
        }


        $response->end('hello world');
        $_POST = [];
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
        $_GET = $_POST = $_REQUEST = $_SERVER = $_COOKIE = $_FILES = [];

        if ($request->get) $_GET = $request->get;
        if ($request->post) $_POST = $request->post;
        if ($request->file) $_FILES = $request->file;
        if ($request->cookie) $_COOKIE = $request->cookie;
        if ($request->server) $_SERVER = $request->server;

        //构造url请求路径,phalcon获取到$_GET['_url']时会定向到对应的路径，否则请求路径为'/'
        $_GET['_url'] = $request->server['request_uri'];

        $_REQUEST = array_merge($request->get, $request->post, $request->cookie);
        $_SERVER['REQUEST_URI'] = $request->meta['uri'];


        /**
         * 将HTTP头信息赋值给$_SERVER超全局变量
         */
        foreach ($request->head as $key => $value) {
            $_key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
            $_SERVER[$_key] = $value;
        }
        $_SERVER['REMOTE_ADDR'] = $request->remote_ip;
    }


    public function unsetGlobal() {
        $_REQUEST = $_SESSION = $_COOKIE = $_FILES = $_POST = $_SERVER = $_GET = [];
    }


}