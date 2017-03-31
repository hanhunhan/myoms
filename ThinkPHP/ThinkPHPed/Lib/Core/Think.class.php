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
// $Id: Think.class.php 2704 2012-02-03 05:44:08Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Portal??
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Think.class.php 2704 2012-02-03 05:44:08Z liu21st $
 +------------------------------------------------------------------------------
 */
class Think {

    private static $_instance = array();

    /**
     +----------------------------------------------------------
     * ??¨®???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function Start() {
        // ?Ú…???????????
        set_error_handler(array('Think','appError'));
        set_exception_handler(array('Think','appException'));
        // ???AUTOLOAD????
        spl_autoload_register(array('Think', 'autoload'));
        //[RUNTIME]
        Think::buildApp();         // ????????
        //[/RUNTIME]
        // ???????
        App::run();
        return ;
    }

    //[RUNTIME]
    /**
     +----------------------------------------------------------
     * ?????????? ???????
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static private function buildApp() {
        // ????????????????
        C(include THINK_PATH.'Conf/convention.php');

        // ?????????
        if(defined('MODE_NAME')) { // ???????¨°????????
            $mode   = include MODE_PATH.strtolower(MODE_NAME).'.php';
        }else{
            $mode   =  array();
        }

        // ?????????????
        if(isset($mode['config'])) {
            C( is_array($mode['config'])?$mode['config']:include $mode['config'] );
        }

        // ??????????????
        if(is_file(CONF_PATH.'config.php'))
            C(include CONF_PATH.'config.php');

        // ?????????????
        L(include THINK_PATH.'Lang/'.strtolower(C('DEFAULT_LANG')).'.php');

        // ???????????????
        if(C('APP_TAGS_ON')) {
            if(isset($mode['extends'])) {
                C('extends',is_array($mode['extends'])?$mode['extends']:include $mode['extends']);
            }else{ // ??????????????????
                C('extends', include THINK_PATH.'Conf/tags.php');
            }
        }

        // ??????????????
        if(isset($mode['tags'])) {
            C('tags', is_array($mode['tags'])?$mode['tags']:include $mode['tags']);
        }elseif(is_file(CONF_PATH.'tags.php')){
            // ?????????????????tags???????
            C('tags', include CONF_PATH.'tags.php');
        }

        $compile   = '';
        // ??????????????§Ò?
        if(isset($mode['core'])) {
            $list   =  $mode['core'];
        }else{
            $list  =  array(
                THINK_PATH.'Common/functions.php', // ???????????
                CORE_PATH.'Core/Log.class.php',    // ?????????
                CORE_PATH.'Core/Dispatcher.class.php', // URL??????
                CORE_PATH.'Core/App.class.php',   // ??¨®?????
                CORE_PATH.'Core/Action.class.php', // ????????
                CORE_PATH.'Core/View.class.php',  // ?????
            );
        }
        // ?????????????§Ò????
        if(is_file(CONF_PATH.'core.php')) {
            $list  =  array_merge($list,include CONF_PATH.'core.php');
        }
        foreach ($list as $file){
            if(is_file($file))  {
                require_cache($file);
                if(!APP_DEBUG)   $compile .= compile($file);
            }
        }

        // ??????????????
        if(is_file(COMMON_PATH.'common.php')) {
            include COMMON_PATH.'common.php';
            // ???????
            if(!APP_DEBUG)  $compile   .= compile(COMMON_PATH.'common.php');
        }

        // ??????????????
        if(isset($mode['alias'])) {
            $alias = is_array($mode['alias'])?$mode['alias']:include $mode['alias'];
            alias_import($alias);
            if(!APP_DEBUG) $compile .= 'alias_import('.var_export($alias,true).');';
        }
        // ???????????????
        if(is_file(CONF_PATH.'alias.php')){ 
            $alias = include CONF_PATH.'alias.php';
            alias_import($alias);
            if(!APP_DEBUG) $compile .= 'alias_import('.var_export($alias,true).');';
        }

        if(APP_DEBUG) {
            // ???????????????????????
            C(include THINK_PATH.'Conf/debug.php');
            // ????????????????
            $status  =  C('APP_STATUS');
            // ??????????????????
            if(is_file(CONF_PATH.$status.'.php'))
                // ???????????????????????
                C(include CONF_PATH.$status.'.php');
        }else{
            // ????????????????????
            build_runtime_cache($compile);
        }
        return ;
    }
    //[/RUNTIME]

    /**
     +----------------------------------------------------------
     * ?????????ThinkPHP???
     * ??????????????????¡¤??
     +----------------------------------------------------------
     * @param string $class ????????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public static function autoload($class) {
        // ????????????????
        if(alias_import($class)) return ;

        if(substr($class,-8)=="Behavior") { // ???????
            if(require_cache(CORE_PATH.'Behavior/'.$class.'.class.php') 
                || require_cache(EXTEND_PATH.'Behavior/'.$class.'.class.php') 
                || require_cache(LIB_PATH.'Behavior/'.$class.'.class.php')
                || (defined('MODE_NAME') && require_cache(MODE_PATH.ucwords(MODE_NAME).'/Behavior/'.$class.'.class.php'))) {
                return ;
            }
        }elseif(substr($class,-5)=="Model"){ // ???????
            if(require_cache(LIB_PATH.'Model/'.$class.'.class.php')
                || require_cache(EXTEND_PATH.'Model/'.$class.'.class.php') ) {
                return ;
            }
        }elseif(substr($class,-6)=="Action"){ // ?????????
            if((defined('GROUP_NAME') && require_cache(LIB_PATH.'Action/'.GROUP_NAME.'/'.$class.'.class.php'))
                || require_cache(LIB_PATH.'Action/'.$class.'.class.php')
                || require_cache(EXTEND_PATH.'Action/'.$class.'.class.php') ) {
                return ;
            }
        }

        // ???????????¡¤?????y??§Ô???????
        $paths  =   explode(',',C('APP_AUTOLOAD_PATH'));
        foreach ($paths as $path){
            if(import($path.'.'.$class))
                // ???????????????
                return ;
        }
    }

    /**
     +----------------------------------------------------------
     * ????????? ???????????????
     +----------------------------------------------------------
     * @param string $class ????????
     * @param string $method ???????????
     +----------------------------------------------------------
     * @return object
     +----------------------------------------------------------
     */
    static public function instance($class,$method='') {
        $identify   =   $class.$method;
        if(!isset(self::$_instance[$identify])) {
            if(class_exists($class)){
                $o = new $class();
                if(!empty($method) && method_exists($o,$method))
                    self::$_instance[$identify] = call_user_func_array(array(&$o, $method));
                else
                    self::$_instance[$identify] = $o;
            }
            else
                halt(L('_CLASS_NOT_EXIST_').':'.$class);
        }
        return self::$_instance[$identify];
    }

    /**
     +----------------------------------------------------------
     * ???????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $e ??????
     +----------------------------------------------------------
     */
    static public function appException($e) {
        halt($e->__toString());
    }

    /**
     +----------------------------------------------------------
     * ??????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param int $errno ????????
     * @param string $errstr ???????
     * @param string $errfile ???????
     * @param int $errline ????????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function appError($errno, $errstr, $errfile, $errline) {
      switch ($errno) {
          case E_ERROR:
          case E_USER_ERROR:
            $errorStr = "[$errno] $errstr ".basename($errfile)." ?? $errline ??.";
            if(C('LOG_RECORD')) Log::write($errorStr,Log::ERR);
            halt($errorStr);
            break;
          case E_STRICT:
          case E_USER_WARNING:
          case E_USER_NOTICE:
          default:
            $errorStr = "[$errno] $errstr ".basename($errfile)." ?? $errline ??.";
            Log::record($errorStr,Log::NOTICE);
            break;
      }
    }

    /**
     +----------------------------------------------------------
     * ???????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name ????????
     * @param $value  ?????
     +----------------------------------------------------------
     */
    public function __set($name ,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
    }

    /**
     +----------------------------------------------------------
     * ??????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $name ????????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return isset($this->$name)?$this->$name:null;
    }
}