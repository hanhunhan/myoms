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
// $Id: runtime.php 2821 2012-03-16 06:17:49Z luofei614@gmail.com $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP ����ʱ�ļ� ������ټ���
 +------------------------------------------------------------------------------
 */
if (!defined('THINK_PATH')) exit();
if (version_compare(PHP_VERSION, '5.2.0', '<')) die('require PHP > 5.2.0 !');
//  �汾��Ϣ
define('THINK_VERSION', '3.0');
define('THINK_RELEASE', '20120313');

//   ϵͳ��Ϣ
if(version_compare(PHP_VERSION,'5.4.0','<') ) {
    //[sae]�²�֧���������  
    //@set_magic_quotes_runtime (0);
    define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
}
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

// ��Ŀ����
defined('APP_NAME') or  define('APP_NAME', basename(dirname($_SERVER['SCRIPT_FILENAME'])));
if(!IS_CLI) {
    // ��ǰ�ļ���
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGIģʽ��
            $_temp  = explode('.php',$_SERVER['PHP_SELF']);
            define('_PHP_FILE_',  rtrim(str_replace($_SERVER['HTTP_HOST'],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER['SCRIPT_NAME'],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        // ��վURL��Ŀ¼
        if( strtoupper(APP_NAME) == strtoupper(basename(dirname(_PHP_FILE_))) ) {
            $_root = dirname(dirname(_PHP_FILE_));
        }else {
            $_root = dirname(_PHP_FILE_);
        }
        define('__ROOT__',   (($_root=='/' || $_root=='\\')?'':$_root));
    }

    //֧�ֵ�URLģʽ
    define('URL_COMMON',      0);   //��ͨģʽ
    define('URL_PATHINFO',    1);   //PATHINFOģʽ
    define('URL_REWRITE',     2);   //REWRITEģʽ
    define('URL_COMPAT',      3);   // ����ģʽ
}

// ·������ ��������ļ������¶��� ����·��������������/ ��β
defined('CORE_PATH') or define('CORE_PATH',THINK_PATH.'Lib/'); // ϵͳ�������Ŀ¼
defined('EXTEND_PATH') or define('EXTEND_PATH',THINK_PATH.'Extend/'); // ϵͳ��չĿ¼
defined('MODE_PATH') or define('MODE_PATH',EXTEND_PATH.'Mode/'); // ģʽ��չĿ¼
defined('ENGINE_PATH') or define('ENGINE_PATH',EXTEND_PATH.'Engine/'); // ������չĿ¼// ϵͳģʽĿ¼
defined('VENDOR_PATH') or define('VENDOR_PATH',EXTEND_PATH.'Vendor/'); // ���������Ŀ¼
defined('LIBRARY_PATH') or define('LIBRARY_PATH',EXTEND_PATH.'Library/'); // ��չ���Ŀ¼
defined('COMMON_PATH') or define('COMMON_PATH',    APP_PATH.'Common/'); // ��Ŀ����Ŀ¼
defined('LIB_PATH') or define('LIB_PATH',    APP_PATH.'Lib/'); // ��Ŀ���Ŀ¼
defined('CONF_PATH') or define('CONF_PATH',  APP_PATH.'Conf/'); // ��Ŀ����Ŀ¼
defined('LANG_PATH') or define('LANG_PATH', APP_PATH.'Lang/'); // ��Ŀ���԰�Ŀ¼
defined('TMPL_PATH') or define('TMPL_PATH',APP_PATH.'Tpl/'); // ��Ŀģ��Ŀ¼
defined('HTML_PATH') or define('HTML_PATH',$_SERVER['HTTP_APPVERSION'].'/html/'); //[sae] ��Ŀ��̬Ŀ¼,��̬�ļ���浽KVDB
defined('LOG_PATH') or define('LOG_PATH',  RUNTIME_PATH.'Logs/'); // ��Ŀ��־Ŀ¼
defined('TEMP_PATH') or define('TEMP_PATH', RUNTIME_PATH.'Temp/'); // ��Ŀ����Ŀ¼
defined('DATA_PATH') or define('DATA_PATH', RUNTIME_PATH.'Data/'); // ��Ŀ����Ŀ¼
defined('CACHE_PATH') or define('CACHE_PATH',   RUNTIME_PATH.'Cache/'); // ��Ŀģ�建��Ŀ¼

// Ϊ�˷��㵼���������� ����VendorĿ¼��include_path
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);

// ��������ʱ����Ҫ���ļ� �������Զ�Ŀ¼����
function load_runtime_file() {
    //[sae] ����ϵͳ����������
    require SAE_PATH.'Common/common.php';
    //[sae] ��ȡ���ı����ļ��б�
    $list = array(
        SAE_PATH.'Lib/Core/Think.class.php',
        CORE_PATH.'Core/ThinkException.class.php',  // �쳣������
        CORE_PATH.'Core/Behavior.class.php',
    );
    // ����ģʽ�ļ��б�
    foreach ($list as $key=>$file){
        if(is_file($file))  require_cache($file);
    }
    //[sae] ����ϵͳ����������
    alias_import(include SAE_PATH.'Conf/alias.php');
    //[sae]��sae�²���Ŀ¼�ṹ���м��
    if(APP_DEBUG){
        //[sae] ����ģʽ�л�ɾ�����뻺��
        if(SaeMC::file_exists(RUNTIME_FILE)) SaeMC::unlink(RUNTIME_FILE) ;
    }
}

//[sae]�£�����Ҫ���ɼ��runtimeĿ¼����

// �������뻺��
function build_runtime_cache($append='') {
    // ���ɱ����ļ�
    $defs = get_defined_constants(TRUE);
    $content    =  '$GLOBALS[\'_beginTime\'] = microtime(TRUE);';
    //[sae]����SaeMC����
    $content.=compile(SAE_PATH.'Lib/Core/SaeMC.class.php');
    if(defined('RUNTIME_DEF_FILE')) { //[sae] �����ĳ����ļ��ⲿ����
        SaeMC::set(RUNTIME_DEF_FILE, '<?php '.array_define($defs['user']));
        $content  .=  'SaeMC::include_file(\''.RUNTIME_DEF_FILE.'\');';
    }else{
        $content  .= array_define($defs['user']);
    }
    $content    .= 'set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);';
    //[sae] ��ȡ���ı����ļ��б�
    $list = array(
        SAE_PATH.'Common/common.php',
        SAE_PATH.'Lib/Core/Think.class.php',
        CORE_PATH.'Core/ThinkException.class.php',
        CORE_PATH.'Core/Behavior.class.php',
    );
    foreach ($list as $file){
        $content .= compile($file);
    }
    // ϵͳ��Ϊ��չ�ļ�ͳһ����
    if(C('APP_TAGS_ON')) {
        $content .= build_tags_cache();
    }
    //[sae] ����SAE��alias
    $alias = include SAE_PATH.'Conf/alias.php';
    $content .= 'alias_import('.var_export($alias,true).');';
    // ������Ĭ�����԰������ò���
    $content .= $append."\nL(".var_export(L(),true).");C(".var_export(C(),true).');G(\'loadTime\');Think::Start();';
    //[sae] ���ɱ��뻺���ļ�
    SaeMC::set(RUNTIME_FILE, strip_whitespace('<?php '.$content));
}

// ����ϵͳ��Ϊ��չ���
function build_tags_cache() {
    $tags = C('extends');
    $content = '';
    foreach ($tags as $tag=>$item){
        foreach ($item as $key=>$name) {
            $content .= is_int($key)?compile(CORE_PATH.'Behavior/'.$name.'Behavior.class.php'):compile($name);
        }
    }
    return $content;
}
//[sae]�£�����Ҫ����Ŀ¼�ṹ����
// ��������ʱ�����ļ�
load_runtime_file();
// ��¼�����ļ�ʱ��
G('loadTime');
// ִ�����
Think::Start();