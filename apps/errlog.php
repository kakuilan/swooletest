<?php
/**
 * Copyright (c) 2016 LKK/lianq.net All rights reserved
 * User: kakuilan@163.com
 * Date: 2016/12/25
 * Time: 16:03
 * Desc: -
 */

function phperrorHandler() {
    $logFile = LOGDIR . 'phperr_'. date('Ymd').'.log';

    ini_set('log_errors', 1); //设置错误信息输出到文件
    ini_set('ignore_repeated_errors', 1);//不重复记录出现在同一个文件中的同一行代码上的错误信息

    $user_defined_err = error_get_last();//获取最后发生的错误
    if ($user_defined_err['type'] > 0) {
        if (!is_dir(LOGDIR) ) {
            if(!mkdir(LOGDIR, 0755, true) ) return false;
        }

        switch ($user_defined_err['type']) {
            case 1:
                $user_defined_errType = '致命的运行时错误(E_ERROR)';
                break;
            case 2:
                $user_defined_errType = '非致命的运行时错误(E_WARNING)';
                break;
            case 4:
                $user_defined_errType = '编译时语法解析错误(E_PARSE)';
                break;
            case 8:
                $user_defined_errType = '运行时提示(E_NOTICE)';
                break;
            case 16:
                $user_defined_errType = 'PHP内部错误(E_CORE_ERROR)';
                break;
            case 32:
                $user_defined_errType = 'PHP内部警告(E_CORE_WARNING)';
                break;
            case 64:
                $user_defined_errType = 'Zend脚本引擎内部错误(E_COMPILE_ERROR)';
                break;
            case 128:
                $user_defined_errType = 'Zend脚本引擎内部警告(E_COMPILE_WARNING)';
                break;
            case 256:
                $user_defined_errType = '用户自定义错误(E_USER_ERROR)';
                break;
            case 512:
                $user_defined_errType = '用户自定义警告(E_USER_WARNING)';
                break;
            case 1024:
                $user_defined_errType = '用户自定义提示(E_USER_NOTICE)';
                break;
            case 2048:
                $user_defined_errType = '代码提示(E_STRICT)';
                break;
            case 4096:
                $user_defined_errType = '可以捕获的致命错误(E_RECOVERABLE_ERROR)';
                break;
            case 8191:
                $user_defined_errType = '所有错误警告(E_ALL)';
                break;
            default:
                $user_defined_errType = '未知类型';
                break;
        }

        $msg = sprintf('[%s] %s %s %s line:%s',
            date("Y-m-d H:i:s"),
            $user_defined_errType,
            $user_defined_err['message'],
            $user_defined_err['file'],
            $user_defined_err['line']);

        //必须显式地记录错误
        error_log($msg."\r\n", 3, $logFile);
    }

    return null;
}

register_shutdown_function('phperrorHandler');