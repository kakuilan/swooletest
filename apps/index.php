<?php
/**
 * Created by PhpStorm.
 * User: kakuilan@163.com
 * Date: 2017/7/14
 * Time: 15:18
 * Desc:
 */

require __DIR__ . './define.php';
require __DIR__ . './errlog.php';

//载入命名空间
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('Apps\\', APPSDIR);
