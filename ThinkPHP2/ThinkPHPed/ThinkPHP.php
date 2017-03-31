<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: ThinkPHP.php 2702 2012-02-02 12:35:01Z liu21st $

// ThinkPHP ??????

//?????????????
$GLOBALS['_beginTime'] = microtime(TRUE);
// ???????????
define('MEMORY_LIMIT_ON',function_exists('memory_get_usage'));
if(MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();
if(!defined('APP_PATH')) define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']).'/');
if(!defined('RUNTIME_PATH')) define('RUNTIME_PATH',APP_PATH.'Runtime/');
if(!defined('APP_DEBUG')) define('APP_DEBUG',false); // ????????
$runtime = defined('MODE_NAME')?'~'.strtolower(MODE_NAME).'_runtime.php':'~runtime.php';
if(!defined('RUNTIME_FILE')) define('RUNTIME_FILE',RUNTIME_PATH.$runtime);
if(!APP_DEBUG && is_file(RUNTIME_FILE)) {
    // ?????????????allinone????
    require RUNTIME_FILE;
}else{
    if(version_compare(PHP_VERSION,'5.2.0','<'))  die('require PHP > 5.2.0 !');
    // ThinkPHP????????
    if(!defined('THINK_PATH')) define('THINK_PATH', dirname(__FILE__).'/');
    if(!defined('APP_NAME')) define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));
    // ????????????
    require THINK_PATH."Common/runtime.php";
    // ?????????????
    G('loadTime');
    // ??????
    Think::Start();
}