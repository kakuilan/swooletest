<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/7
 * Time: 23:14
 * Desc: -定时任务管理器
 */


namespace Kswoole\Server;

use \Lkk\LkkService;
use \Kswoole\Server\SwooleServer;

class TimerTaskManager extends LkkService {

    public $timerTasks;
    private $timerId;

    public function __construct(array $vars = []) {
        parent::__construct($vars);

        //TODO读数据表中的任务,加进来

        $this->startTimerTasks();
    }


    //开始定时任务
    public function startTimerTasks() {
        //定时器/秒
        $this->deliveryTimerTask();
        $this->timerId = swoole_timer_tick(1000, function () {
            $this->deliveryTimerTask();
        });
    }


    //停止定时任务
    public function stopTimerTasks() {
        swoole_timer_clear($this->timerId);
    }


    //投递定时任务
    public function deliveryTimerTask() {
        if(!empty($this->timerTasks)) {
            foreach ($this->timerTasks as $taskData) {
                if(!empty($taskData)) {
                    SwooleServer::getServer()->task($taskData);
                }
            }
        }
    }


    //新加一个定时任务
    public function addTask($taskData) {
        array_push($this->timerTasks, $taskData);
    }


    //重启定时任务
    public function restartTimer() {
        $this->stopTimerTasks();
        $this->startTimerTasks();
    }






}