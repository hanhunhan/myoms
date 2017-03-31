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
     * ִ��Ӧ�ó���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function run() {

        if(C('URL_MODEL')==1) {// PATHINFO ģʽURL���� ���� index.php module/action/id/4
            $depr = C('URL_PATHINFO_DEPR');
            $path   = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
            if(!empty($path)) {
                $params = explode($depr,trim($path,$depr));
            }
            // ȡ��ģ��Ͳ�������
            define('MODULE_NAME',   !empty($params)?array_shift($params):C('DEFAULT_MODULE'));
            define('ACTION_NAME',  !empty($params)?array_shift($params):C('DEFAULT_ACTION'));
            if(count($params)>1) {
                // ����ʣ����� ������GET��ʽ��ȡ
                preg_replace('@(\w+),([^,\/]+)@e', '$_GET[\'\\1\']="\\2";', implode(',',$params));
            }
        }else{// Ĭ��URLģʽ ���� index.php module action id 4
            // ȡ��ģ��Ͳ�������
            define('MODULE_NAME',   isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:C('DEFAULT_MODULE'));
            define('ACTION_NAME',    isset($_SERVER['argv'][2])?$_SERVER['argv'][2]:C('DEFAULT_ACTION'));
            if($_SERVER['argc']>3) {
                // ����ʣ����� ������GET��ʽ��ȡ
                preg_replace('@(\w+),([^,\/]+)@e', '$_GET[\'\\1\']="\\2";', implode(',',array_slice($_SERVER['argv'],3)));
            }
        }

        // ִ�в���
        $module  =  A(MODULE_NAME);
        if(!$module) {
            // �Ƿ���Emptyģ��
            $module = A("Empty");
            if(!$module){
                // ģ�鲻���� �׳��쳣
                throw_exception(L('_MODULE_NOT_EXIST_').MODULE_NAME);
            }
        }
        call_user_func(array(&$module,ACTION_NAME));
        // ������־��¼
        if(C('LOG_RECORD')) Log::save();
        return ;
    }

};