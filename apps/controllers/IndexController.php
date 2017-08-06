<?php
/**
 * Copyright (c) 2017 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2017/8/6
 * Time: 16:13
 * Desc: -
 */


use Phalcon\Mvc\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $this->view->disable();
        $msg = 'Weclcom Phalcon Swoole! ' .date('Y-m-d H:i:s');
        return $msg;
    }



}
