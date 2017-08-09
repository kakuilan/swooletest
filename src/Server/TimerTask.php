<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/7
 * Time: 23:14
 * Desc: -定时任务
 */


namespace Kswoole\Server;

use \Lkk\LkkService;
use \Kswoole\Server\SwooleServer;

class TimerTask extends LkkService {

    public $timerTasks;

    public function __construct(array $vars = []) {
        parent::__construct($vars);

        //定时器/秒
        $this->deliveryTimerTask();
        swoole_timer_tick(1000, function () {
            $this->deliveryTimerTask();
        });
    }


    //投递定时任务
    public function deliveryTimerTask() {
        if(!empty($this->timerTasks)) {
            foreach ($this->timerTasks as $timerTask) {
                if(!empty($timerTask)) {
                    $server = SwooleServer::instance();
                    $server->task($timerTask);
                }
            }
        }
    }







}