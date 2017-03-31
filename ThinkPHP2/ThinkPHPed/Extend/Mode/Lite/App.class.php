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
 * ThinkPHP Ӧ�ó����� ����ģʽ
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
     * ����Ӧ��ʵ�� ����ļ�ʹ�õĿ�ݷ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function run() {
        // ����ϵͳʱ��
        date_default_timezone_set(C('DEFAULT_TIMEZONE'));
        // ���ض�̬��Ŀ�����ļ�������
        load_ext_file();
        // ��Ŀ��ʼ����ǩ
        tag('app_init');
        // URL����
        Dispatcher::dispatch();
        // ��Ŀ��ʼ��ǩ
        tag('app_begin');
         // Session��ʼ�� ֧�������ͻ���
        if(isset($_REQUEST[C("VAR_SESSION_ID")]))
            session_id($_REQUEST[C("VAR_SESSION_ID")]);
        if(C('SESSION_AUTO_START'))  session_start();
        // ��¼Ӧ�ó�ʼ��ʱ��
        if(C('SHOW_RUN_TIME')) G('initTime');
        App::exec();
        // ��Ŀ������ǩ
        tag('app_end');
        // ������־��¼
        if(C('LOG_RECORD')) Log::save();
        return ;
    }

    /**
     +----------------------------------------------------------
     * ִ��Ӧ�ó���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    static public function exec() {
        // ��ȫ���
        if(!preg_match('/^[A-Za-z_0-9]+$/',MODULE_NAME)){
            throw_exception(L('_MODULE_NOT_EXIST_'));
        }
        //����Action������ʵ��
        $group =  defined('GROUP_NAME') ? GROUP_NAME.'/' : '';
        $module  =  A($group.MODULE_NAME);
        if(!$module) {
            // �Ƿ���Emptyģ��
            $module = A("Empty");
            if(!$module)
                // ģ�鲻���� �׳��쳣
                throw_exception(L('_MODULE_NOT_EXIST_').MODULE_NAME);
        }
        //ִ�е�ǰ����
        call_user_func(array(&$module,ACTION_NAME));
        return ;
    }
}