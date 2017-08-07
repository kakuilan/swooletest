<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/7
 * Time: 23:14
 * Desc: -
 */


namespace Kswoole\Server;

use \Lkk\LkkService;

class TimerTask extends LkkService {

    public function __construct(array $vars = []) {
        parent::__construct($vars);
    }


    public function dumpTest() {
        $time = getMillisecond();
        $msg = "timer task callback: time[{$time}]\r\n";
        print_r($msg);
    }




}