<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2017/7/14
 * Time: 15:12
 * Desc:
 */

define('DS', str_replace('\\', '/', DIRECTORY_SEPARATOR));
define('PS', PATH_SEPARATOR);

define('KSERVER_NAME', 'KSS');
define('KSERVER_VERS', '0.0.0.1');

define('ROOTDIR', str_replace('\\', '/', dirname(__DIR__)) . DS ); //根目录
define('APPSDIR', ROOTDIR .'apps'       . DS );
define('RUNTDIR', ROOTDIR .'runtime'    . DS );
define('LOGDIR', RUNTDIR .'logs'    . DS ); //日志目录
define('PIDDIR', RUNTDIR .'pids'    . DS ); //pid目录

