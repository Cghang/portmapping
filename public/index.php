<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 定义静态资源路径
define('ROOT', 'http://php.xzcoder.com/portmapping/');
define('RES', ROOT . 'public/static');

//上传路径
define('UPLOAD_PATH', __DIR__ . '/uploads/');

//默认日期格式
define('DATE_FORMAT', '\'Y-m-d H:i:s\'');

//隧道协议常量
define('TCP_CODE', 1);
define('HTTP_CODE', 2);

//分页默认页面大小
define('DEFAULT_PAGE_SIZE', 3);

//启用、禁用常量
define('ENABLE', 1);
define('DISABLE', 0);

//订单类型常量
define('ORDER_TYPE_OPEN', 1);
define('ORDER_TYPE_KEEP', 2);
define('ORDER_TYPE_OPEN_TEXT', '隧道开通');
define('ORDER_TYPE_KEEP_TEXT', '隧道续费');

//订单状态
define('ORDER_WAIT', 0);
define('ORDER_OVER', 1);


// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
