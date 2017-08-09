<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/9
 * Time: 22:39
 * Desc: -任务
 */


namespace Kswoole\Server;

use \Lkk\LkkService;

class Task extends LkkService {

    public function dumpTest($title='none') {
        $time = getMillisecond();
        $msg = "timer task callback: time[{$time}] title[{$title}]\r\n";
        print_r($msg);
    }



}