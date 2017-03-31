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
// $Id: App.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP ??車????? ?????迄??????
 * ???????????????????? ??????????Run???????
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: App.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class App {

    /**
     +----------------------------------------------------------
     * ??車???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function init() {

        // ?????????
        date_default_timezone_set(C('DEFAULT_TIMEZONE'));
        // ??????????????????????
        load_ext_file();
        // URL????
        Dispatcher::dispatch();

        if(defined('GROUP_NAME')) {
            // ??????????????
            if(is_file(CONF_PATH.GROUP_NAME.'/config.php'))
                C(include CONF_PATH.GROUP_NAME.'/config.php');
            // ??????�D?????
            if(is_file(COMMON_PATH.GROUP_NAME.'/function.php'))
                include COMMON_PATH.GROUP_NAME.'/function.php';
        }
        return ;
    }

    /**
     +----------------------------------------------------------
     * ?????車???
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    static public function exec() {
        // ??????
        if(!preg_match('/^[A-Za-z_0-9]+$/',MODULE_NAME)){
            throw_exception(L('_MODULE_NOT_EXIST_'));
        }

	
        //????Action?????????
        $group =  defined('GROUP_NAME') ? GROUP_NAME.'/' : '';

        $module  =  A($group.MODULE_NAME);
		//echo MODULE_NAME;die('cxx');
        if(!$module) {
            if(function_exists('__hack_module')) {
                // hack ????????????? ????Action????
                $module = __hack_module();
                if(!is_object($module)) {
                    // ?????????? ??????
                    return ;
                }
            }else{
                // ?????Empty???
                $module = A("Empty");
                if(!$module){
                    $msg =  L('_MODULE_NOT_EXIST_').MODULE_NAME;
                    if(APP_DEBUG) {
                        // ??礵???? ?????
                        throw_exception($msg);
                    }else{
                        if(C('LOG_EXCEPTION_RECORD')) Log::write($msg);
                        send_http_status(404);
                        exit;
                    }
                }
            }
        }
        //????????????
        $action = ACTION_NAME;
        if (method_exists($module,'_before_'.$action)) {
            // ?????辰???
            call_user_func(array(&$module,'_before_'.$action));
        }
        //??快??????
        call_user_func(array(&$module,$action));
        if (method_exists($module,'_after_'.$action)) {
            //  ??抗??????
            call_user_func(array(&$module,'_after_'.$action));
        }
        return ;
    }

    /**
     +----------------------------------------------------------
     * ?????????? ???????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function run() {
        // ???????????
        tag('app_init');
        App::init();
        // ?????????
        tag('app_begin');
        // Session?????
        session(C('SESSION_OPTIONS'));
        // ?????車???????
        G('initTime');
        App::exec();
        // ??????????
        tag('app_end');
        // ??????????
        if(C('LOG_RECORD')) Log::save();
        return ;
    }

}