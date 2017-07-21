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
    private static $instance;


    /**
     * 构造函数
     * SwooleServer constructor.
     * @param array $vars
     */
    public function __construct(array $vars = []) {
        parent::__construct($vars);

        $this->conf = self::getConf();

    }



    /**
     * 获取SWOOLE服务
     * @return mixed
     */
    public static function getServer() {
        return is_null(self::$instance) ? null : self::$instance->server;
    }


    /**
     * 获取配置
     * @return array
     */
    public static function getConf() {
        return [
            'server_name' => 'Kserver',

            //主服务
            'main_server' => [
                'host' => '0.0.0.0',
                'port' => 6666,
                'mode' => SWOOLE_PROCESS,
                'sock_type' => SWOOLE_SOCK_TCP,
            ],

            //子服务,跑长连接或推送等
            'sub_server' => [
                'host' => '0.0.0.0',
                'port' => 6667,
                'sock_type' => SWOOLE_SOCK_TCP,
            ],

        ];
    }


    /**
     * 初始化
     * @return $this
     */
    public function init() {
        $mainCnf = $this->conf['main_server'];
        $subCnf = $this->conf['sub_server'];
        $this->server = new \swoole_server($mainCnf['host'], $mainCnf['port'], $mainCnf['mode'], $mainCnf['sock_type']);
        if(!empty($subCnf)) {
            $this->server->addListener($subCnf['host'], $subCnf['port'], $subCnf['sock_type']);
        }

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
        echo "Start\r\n";

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



    public function onReceive($serv, $fd, $fromId, $data) {
        //TODO
        echo "onReceive:{$fromId}\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        $length = unpack('N', $data)[1];
        $data = unserialize(substr($data, -$length));
        var_dump($data);

        $chunk = $response = '11111111111';
        $serv->send($fd, pack('N', strlen($chunk)), $fromId);
        $serv->send($fd, $chunk, $fromId);

        return $this;
    }



    public function onPacket($serv, $data, $clientInfo) {
        //TODO
        echo "onPacket\r\n";

        $extEvent = $this->getExtEvent(__FUNCTION__);
        if($extEvent) {
            call_user_func_array($extEvent['func'], $extEvent['parm']);
        }

        return $this;
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
        $this->server->on('Receive', [$this, 'onReceive']);
        $this->server->on('Packet', [$this, 'onPacket']);
        $this->server->on('Close', [$this, 'onClose']);
        $this->server->on('Task', [$this, 'onTask']);
        $this->server->on('Finish', [$this, 'onFinish']);
        $this->server->on('PipeMessage', [$this, 'onPipeMessage']);
        $this->server->on('WorkerError', [$this, 'onWorkerError']);
        $this->server->on('ManagerStart', [$this, 'onManagerStart']);
        $this->server->on('ManagerStop', [$this, 'onManagerStop']);

        return $this;
    }


    public function start() {
        $this->bindEvents();
        $this->server->start();

        return $this;
    }




}