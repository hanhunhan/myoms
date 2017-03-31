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
// $Id$

/**
 +------------------------------------------------------------------------------
 * ThinkPHP ???????? ??????????
 +------------------------------------------------------------------------------
 */
if (!defined('THINK_PATH')) exit();
//  ?·Ú???
define('THINK_VERSION', '3.0RC2');
define('THINK_RELEASE', '20120207');

//   ?????
if(version_compare(PHP_VERSION,'5.4.0','<') ) {
    @set_magic_quotes_runtime (0);
    define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?True:False);
}
define('IS_CGI',substr(PHP_SAPI, 0,3)=='cgi' ? 1 : 0 );
define('IS_WIN',strstr(PHP_OS, 'WIN') ? 1 : 0 );
define('IS_CLI',PHP_SAPI=='cli'? 1   :   0);

if(!IS_CLI) {
    // ????????
    if(!defined('_PHP_FILE_')) {
        if(IS_CGI) {
            //CGI/FASTCGI????
            $_temp  = explode('.php',$_SERVER["PHP_SELF"]);
            define('_PHP_FILE_',  rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
        }else {
            define('_PHP_FILE_',    rtrim($_SERVER["SCRIPT_NAME"],'/'));
        }
    }
    if(!defined('__ROOT__')) {
        // ???URL????
        if( strtoupper(APP_NAME) == strtoupper(basename(dirname(_PHP_FILE_))) ) {
            $_root = dirname(dirname(_PHP_FILE_));
        }else {
            $_root = dirname(_PHP_FILE_);
        }
        define('__ROOT__',   (($_root=='/' || $_root=='\\')?'':$_root));
    }

    //????URL??
    define('URL_COMMON',      0);   //?????
    define('URL_PATHINFO',    1);   //PATHINFO??
    define('URL_REWRITE',     2);   //REWRITE??
    define('URL_COMPAT',      3);   // ??????
}

// ¡¤?????? ??????????????????? ????¡¤??????????????/ ??¦Â
if(!defined('CORE_PATH')) define('CORE_PATH',THINK_PATH.'Lib/'); // ???????????
if(!defined('EXTEND_PATH')) define('EXTEND_PATH',THINK_PATH.'Extend/'); // ???????
if(!defined('MODE_PATH')) define('MODE_PATH',EXTEND_PATH.'Mode/'); // ??????
if(!defined('VENDOR_PATH')) define('VENDOR_PATH',EXTEND_PATH.'Vendor/'); // ???????????
if(!defined('LIBRARY_PATH')) define('LIBRARY_PATH',EXTEND_PATH.'Library/'); // ????????
if(!defined('COMMON_PATH')) define('COMMON_PATH',    APP_PATH.'Common/'); // ?????????
if(!defined('LIB_PATH')) define('LIB_PATH',    APP_PATH.'Lib/'); // ????????
if(!defined('CONF_PATH')) define('CONF_PATH',  APP_PATH.'Conf/'); // ?????????
if(!defined('LANG_PATH')) define('LANG_PATH', APP_PATH.'Lang/'); // ??????????
if(!defined('TMPL_PATH')) define('TMPL_PATH',APP_PATH.'Tpl/'); // ????????
if(!defined('HTML_PATH')) define('HTML_PATH',APP_PATH.'Html/'); // ????????
if(!defined('LOG_PATH')) define('LOG_PATH',  RUNTIME_PATH.'Logs'); // ????????
if(!defined('TEMP_PATH')) define('TEMP_PATH', RUNTIME_PATH.'Temp'); // ?????????
if(!defined('DATA_PATH')) define('DATA_PATH', RUNTIME_PATH.'Data'); // ?????????
if(!defined('CACHE_PATH')) define('CACHE_PATH',   RUNTIME_PATH.'Cache'); // ?????½¨????

// ??????????????????? ???????????????
function load_runtime_file() {
    // ????????????????
    require THINK_PATH.'Common/common.php';
    // ??????????????§Ò?
    $list = array(
        CORE_PATH.'Core/Think.class.php',
        CORE_PATH.'Core/ThinkException.class.php',  // ????????
        CORE_PATH.'Core/Behavior.class.php',
    );
    // ??????????§Ò?
    foreach ($list as $key=>$file){
        if(is_file($file))  require_cache($file);
    }
    // ????????????????
    alias_import(include THINK_PATH.'Conf/alias.php');

    // ?????????? ??????????????????
    if(!is_dir(LIB_PATH)) {
        // ???????????
        build_app_dir();
    }elseif(!is_dir(CACHE_PATH)){
        // ??üv????
        check_runtime();
    }elseif(APP_DEBUG){
        // ???????§Ý??????????
        if(is_file(RUNTIME_FILE))   unlink(RUNTIME_FILE);
    }
}

// ??üv????(Runtime) ??????????????????
function check_runtime() {
    if(!is_dir(RUNTIME_PATH)) {
        mkdir(RUNTIME_PATH);
    }elseif(!is_writeable(RUNTIME_PATH)) {
        header("Content-Type:text/html; charset=utf-8");
        exit('?? [ '.RUNTIME_PATH.' ] ????§Õ??');
    }
    mkdir(CACHE_PATH);  // ??½¨????
    if(!is_dir(LOG_PATH))	mkdir(LOG_PATH);    // ?????
    if(!is_dir(TEMP_PATH))  mkdir(TEMP_PATH);	// ?????????
    if(!is_dir(DATA_PATH))	mkdir(DATA_PATH);	// ?????????
    return true;
}

// ??????????
function build_runtime_cache($append='') {
    // ??????????
    $defs = get_defined_constants(TRUE);
    $content    =  '$GLOBALS[\'_beginTime\'] = microtime(TRUE);';
    if(defined('RUNTIME_DEF_FILE')) { // ??????????????????
        file_put_contents(RUNTIME_DEF_FILE,'<?php '.array_define($defs['user']));
        $content  .=  'require \''.RUNTIME_DEF_FILE.'\';';
    }else{
        $content  .= array_define($defs['user']);
    }
    // ??????????????§Ò?
    $list = array(
        THINK_PATH.'Common/common.php',
        CORE_PATH.'Core/Think.class.php',
        CORE_PATH.'Core/ThinkException.class.php',
        CORE_PATH.'Core/Behavior.class.php',
    );
    foreach ($list as $file){
        $content .= compile($file);
    }
    // ?????????????????
    if(C('APP_TAGS_ON')) {
        $content .= build_tags_cache();
    }
    $alias = include THINK_PATH.'Conf/alias.php';
    $content .= 'alias_import('.var_export($alias,true).');';
    // ???????????????????¨°???
    $content .= $append."\nL(".var_export(L(),true).");C(".var_export(C(),true).');G(\'loadTime\');Think::Start();';
    file_put_contents(RUNTIME_FILE,strip_whitespace('<?php '.$content));
}

// ???????????????
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

// ???????????
function build_app_dir() {
    // ??§Õ??????????????????
    if(!is_dir(APP_PATH)) mk_dir(APP_PATH,0777);
    if(is_writeable(APP_PATH)) {
        $dirs  = array(
            LIB_PATH,
            RUNTIME_PATH,
            CONF_PATH,
            COMMON_PATH,
            LANG_PATH,
            CACHE_PATH,
            TMPL_PATH,
            TMPL_PATH.C('DEFAULT_THEME').'/',
            LOG_PATH,
            TEMP_PATH,
            DATA_PATH,
            LIB_PATH.'Model/',
            LIB_PATH.'Action/',
            LIB_PATH.'Behavior/',
            LIB_PATH.'Widget/',
            );
        foreach ($dirs as $dir){
            if(!is_dir($dir))  mk_dir($dir,0777);
        }
        // ?????§Õ??
        if(!defined('BUILD_DIR_SECURE')) define('BUILD_DIR_SECURE',false);
        if(BUILD_DIR_SECURE) {
            if(!defined('DIR_SECURE_FILENAME')) define('DIR_SECURE_FILENAME','index.html');
            if(!defined('DIR_SECURE_CONTENT')) define('DIR_SECURE_CONTENT',' ');
            // ???§Õ??????????
            $content = DIR_SECURE_CONTENT;
            $a = explode(',', DIR_SECURE_FILENAME);
            foreach ($a as $filename){
                foreach ($dirs as $dir)
                    file_put_contents($dir.$filename,$content);
            }
        }
        // §Õ?????????
        if(!is_file(CONF_PATH.'config.php'))
            file_put_contents(CONF_PATH.'config.php',"<?php\nreturn array(\n\t//'??????'=>'?????'\n);\n?>");
        // §Õ?????Action
        if(!is_file(LIB_PATH.'Action/IndexAction.class.php'))
            build_first_action();
    }else{
        header("Content-Type:text/html; charset=utf-8");
        exit('?????????§Õ???????????????<BR>??????????????????????????????~');
    }
}

// ????????Action
function build_first_action() {
    $content = file_get_contents(THINK_PATH.'Tpl/default_index.tpl');
    file_put_contents(LIB_PATH.'Action/IndexAction.class.php',$content);
}

// ????????????????
load_runtime_file();