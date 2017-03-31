<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: Sae.php 2793 2012-03-02 05:34:40Z liu21st $
// Sae��ThinkPHP ����ļ�
//[sae]����SAE_PATH
defined('ENGINE_PATH') or define('ENGINE_PATH',dirname(__FILE__).'/');
define('SAE_PATH', ENGINE_PATH.'Sae/');
//[sae]�ж��Ƿ�������SAE�ϡ�
if (!isset($_SERVER['HTTP_APPCOOKIE'])) {
    define('IS_SAE', FALSE);
    defined('THINK_PATH') or  define('THINK_PATH', dirname(dirname(dirname(__FILE__))) . '/');
    defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/');
    //����ƽ������
    require SAE_PATH.'Common/sae_functions.php';
    //����ģ����
    if (!defined('SAE_APPNAME'))
        require SAE_PATH.'SaeImit.php';
    require THINK_PATH . 'ThinkPHP.php';
    exit();
}
define('IS_SAE', TRUE);
require SAE_PATH.'Lib/Core/SaeMC.class.php';
//��¼��ʼ����ʱ��
$GLOBALS['_beginTime'] = microtime(TRUE);
// ��¼�ڴ��ʼʹ��
define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
if (MEMORY_LIMIT_ON) $GLOBALS['_startUseMems'] = memory_get_usage();
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . '/');
//[sae] �ж��Ƿ��ֶ�������ĿĿ¼
if (!is_dir(APP_PATH . '/Lib/')) {
    header('Content-Type:text/html; charset=utf-8');
    exit('<div style=\'font-weight:bold;float:left;width:430px;text-align:center;border:1px solid silver;background:#E8EFFF;padding:8px;color:red;font-size:14px;font-family:Tahoma\'>sae���������ֶ�������ĿĿ¼~</div>');
}
defined('RUNTIME_PATH') or  define('RUNTIME_PATH', APP_PATH . 'Runtime/');
defined('APP_DEBUG') or    define('APP_DEBUG', false); // �Ƿ����ģʽ
$runtime = defined('MODE_NAME') ? '~' . strtolower(MODE_NAME) . '_runtime.php' : '~runtime.php';
defined('RUNTIME_FILE') or define('RUNTIME_FILE', RUNTIME_PATH . $runtime);
//[sae] ������ı��뻺��
if (!APP_DEBUG && SaeMC::file_exists(RUNTIME_FILE)) {
    // ����ģʽֱ������allinone����
    SaeMC::include_file(RUNTIME_FILE);
} else {
    // ThinkPHPϵͳĿ¼����
    defined('THINK_PATH') or  define('THINK_PATH', dirname(dirname(dirname(__FILE__))) . '/');
    //[sae] ��������ʱ�ļ�
    require SAE_PATH.'Common/runtime.php';
}