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
                'host' => '127.0.0.1',
                'port' => 6666,
                'mode' => SWOOLE_PROCESS,
                'sock_type' => SWOOLE_SOCK_TCP,
            ],

            //子服务,跑长连接或推送等
            'sub_server' => [
                'host' => '127.0.0.1',
                'port' => 6667,
                'mode' => null, //SWOOLE_PROCESS,
                'sock_type' => null, //SWOOLE_SOCK_TCP,
            ],

        ];
    }


    /**
     * 初始化
     * @return \swoole_server
     */
    public function init() {
        $mainCnf = $this->conf['main_server'];
        $subCnf = $this->conf['sub_server'];
        $this->server = new \swoole_server($mainCnf['host'], $mainCnf['port'], $mainCnf['mode'], $mainCnf['sock_type']);
        if(!empty($subCnf)) {
            $this->server->addlistener($subCnf['host'], $subCnf['port'], $subCnf['mode'], $subCnf['sock_type']);
        }

        return $this->server;
    }


    /**
     * 内部调用
     */
    public function onStart() {
        $server = self::getServer();
        //TODO
        echo "start\r\n";

        $this->events[__FUNCTION__]();
    }


    /**
     * 添加启动时外部事件
     * @param callable $func
     */
    public function addStartEvent(callable $func) {
        $eveName = str_ireplace(['add','Event'], ['on',''], __FUNCTION__);
        $this->events[$eveName] = $func;
    }


    public function onShutdown() {

    }


    public function addShutdownEvent() {
        
    }


    public function onWorkerStart() {

    }


    public function addWorkerStartEvent() {

    }


    public function onWorkerStop() {

    }


    public function addWorkerStopEvent() {

    }


    public function onTimer() {

    }


    public function addTimerEvent() {

    }


    public function onConnect() {

    }


    public function addConnectEvent() {

    }


    public function onReceive() {

    }


    public function addReceiveEvent() {

    }


    public function onPacket() {

    }


    public function addPacketEvent() {

    }


    public function onClose() {

    }


    public function addCloseEvent() {

    }


    public function onTask() {

    }


    public function addTaskEvent() {

    }


    public function onFinish() {

    }


    public function addFinishEvent() {

    }


    public function onPipeMessage() {

    }


    public function addPipeMessageEvent() {

    }


    public function onWorkerError() {

    }


    public function addWorkerErrorEvent() {

    }


    public function onManagerStart() {

    }


    public function addManagerStartEvent() {

    }


    public function onManagerStop() {

    }


    public function addManagerStopEvent() {

    }


    public function bindEvents() {
        $this->server->on('Start', [$this, 'onStart']);


    }


    public function start() {
        $this->bindEvents();

    }




}