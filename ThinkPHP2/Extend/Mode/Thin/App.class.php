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
 * ThinkPHP ����ģʽӦ�ó�����
 +------------------------------------------------------------------------------
 */
class App {

    /**
     +----------------------------------------------------------
     * Ӧ�ó����ʼ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function run() {

        // ȡ��ģ��Ͳ�������
        define('MODULE_NAME',   App::getModule());       // Module����
        define('ACTION_NAME',   App::getAction());        // Action����

        // ��¼Ӧ�ó�ʼ��ʱ��
        if(C('SHOW_RUN_TIME'))  $GLOBALS['_initTime'] = microtime(TRUE);
        // ִ�в���
        R(MODULE_NAME.'/'.ACTION_NAME);
        // ������־��¼
        if(C('LOG_RECORD')) Log::save();
        return ;
    }

    /**
     +----------------------------------------------------------
     * ���ʵ�ʵ�ģ������
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static private function getModule() {
        $var  =  C('VAR_MODULE');
        $module = !empty($_POST[$var]) ?
            $_POST[$var] :
            (!empty($_GET[$var])? $_GET[$var]:C('DEFAULT_MODULE'));
        if(C('URL_CASE_INSENSITIVE')) {
            // URL��ַ�����ִ�Сд
            define('P_MODULE_NAME',strtolower($module));
            // ����ʶ��ʽ index.php/user_type/index/ ʶ�� UserTypeAction ģ��
            $module = ucfirst(parse_name(strtolower($module),1));
        }
        unset($_POST[$var],$_GET[$var]);
        return $module;
    }

    /**
     +----------------------------------------------------------
     * ���ʵ�ʵĲ�������
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static private function getAction() {
        $var  =  C('VAR_ACTION');
        $action   = !empty($_POST[$var]) ?
            $_POST[$var] :
            (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_ACTION'));
        unset($_POST[$var],$_GET[$var]);
        return $action;
    }

};