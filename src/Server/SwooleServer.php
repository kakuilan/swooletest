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
     * 实例化
     * @param array $vars
     * @return SwooleServer
     */
    public static function instance(array $vars = []) {
        if(is_null(self::$instance)) {
            self::$instance = new SwooleServer($vars);
        }

        return self::$instance;
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
     * 当启动时
     */
    public function onStart() {
        $server = self::getServer();
        //TODO
        echo "Start\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    /**
     * 添加启动时外部事件
     * @param callable $func
     */
    public function addStartEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    /**
     * 当关掉时
     */
    public function onShutdown() {
        $server = self::getServer();
        //TODO
        echo "Shutdown\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    /**
     * 添加关掉时外部事件
     * @param callable $func
     */
    public function addShutdownEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onWorkerStart() {
        $server = self::getServer();
        //TODO
        echo "WorkerStart\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addWorkerStartEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onWorkerStop() {
        $server = self::getServer();
        //TODO
        echo "WorkerStop\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addWorkerStopEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onTimer() {
        $server = self::getServer();
        //TODO
        echo "Timer\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addTimerEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onConnect() {
        $server = self::getServer();
        //TODO
        echo "onConnect\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addConnectEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onReceive() {
        $server = self::getServer();
        //TODO
        echo "onReceive\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addReceiveEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onPacket() {
        $server = self::getServer();
        //TODO
        echo "onPacket\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addPacketEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onClose() {
        $server = self::getServer();
        //TODO
        echo "onClose\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addCloseEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onTask() {
        $server = self::getServer();
        //TODO
        echo "onTask\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addTaskEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onFinish() {
        $server = self::getServer();
        //TODO
        echo "onFinish\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addFinishEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onPipeMessage() {
        $server = self::getServer();
        //TODO
        echo "onPipeMessage\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addPipeMessageEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onWorkerError() {
        $server = self::getServer();
        //TODO
        echo "onWorkerError\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addWorkerErrorEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onManagerStart() {
        $server = self::getServer();
        //TODO
        echo "onManagerStart\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addManagerStartEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

        return $this;
    }


    public function onManagerStop() {
        $server = self::getServer();
        //TODO
        echo "onManagerStop\r\n";

        if(isset($this->events[__FUNCTION__])) $this->events[__FUNCTION__]();

        return $this;
    }


    public function addManagerStopEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;

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
        //$this->server->on('timer', [$this, 'onTimer']);
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