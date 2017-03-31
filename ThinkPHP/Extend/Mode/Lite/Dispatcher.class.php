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
// $Id: Dispatcher.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP���õ�Dispatcher�� ���ھ���ģʽ
 * ���URL������·�ɺ͵���
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Dispatcher.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class Dispatcher {

    /**
     +----------------------------------------------------------
     * URLӳ�䵽������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function dispatch() {
        $urlMode  =  C('URL_MODEL');
        if($urlMode == URL_COMPAT || !empty($_GET[C('VAR_PATHINFO')])){
            // ����ģʽ�ж�
            define('PHP_FILE',_PHP_FILE_.'?'.C('VAR_PATHINFO').'=');
            $_SERVER['PATH_INFO']   = $_GET[C('VAR_PATHINFO')];
            unset($_GET[C('VAR_PATHINFO')]);
        }elseif($urlMode == URL_REWRITE ) {
            //��ǰ��Ŀ��ַ
            $url    =   dirname(_PHP_FILE_);
            if($url == '/' || $url == '\\')
                $url    =   '';
            define('PHP_FILE',$url);
        }else {
            //��ǰ��Ŀ��ַ
            define('PHP_FILE',_PHP_FILE_);
        }

        // ����PATHINFO��Ϣ
        tag('path_info');
        // ����PATHINFO��Ϣ
        $depr = C('URL_PATHINFO_DEPR');
        if(!empty($_SERVER['PATH_INFO'])) {
            if(C('URL_HTML_SUFFIX') && !empty($_SERVER['PATH_INFO'])) {
                $_SERVER['PATH_INFO'] = preg_replace('/\.'.trim(C('URL_HTML_SUFFIX'),'.').'$/', '', $_SERVER['PATH_INFO']);
            }
            if(!self::routerCheck()){   // ���·�ɹ��� ���û����Ĭ�Ϲ������URL
                $paths = explode($depr,trim($_SERVER['PATH_INFO'],'/'));
                $var  =  array();
                if (C('APP_GROUP_LIST') && !isset($_GET[C('VAR_GROUP')])){
                    $var[C('VAR_GROUP')] = in_array(strtolower($paths[0]),explode(',',strtolower(C('APP_GROUP_LIST'))))? array_shift($paths) : '';
                }
                if(!isset($_GET[C('VAR_MODULE')])) {// ��û�ж���ģ������
                    $var[C('VAR_MODULE')]  =   array_shift($paths);
                }
                $var[C('VAR_ACTION')]  =   array_shift($paths);
                // ����ʣ���URL����
                $res = preg_replace('@(\w+)'.$depr.'([^'.$depr.'\/]+)@e', '$var[\'\\1\']="\\2";', implode($depr,$paths));
                $_GET   =  array_merge($var,$_GET);
            }
        }

        // ��ȡ���� ģ��Ͳ�������
        if (C('APP_GROUP_LIST')) {
            define('GROUP_NAME', self::getGroup(C('VAR_GROUP')));
        }
        define('MODULE_NAME',self::getModule(C('VAR_MODULE')));
        define('ACTION_NAME',self::getAction(C('VAR_ACTION')));
        // URL����
        define('__SELF__',$_SERVER['REQUEST_URI']);
        // ��ǰ��Ŀ��ַ
        define('__APP__',PHP_FILE);
        // ��ǰģ��ͷ����ַ
        $module = defined('P_MODULE_NAME')?P_MODULE_NAME:MODULE_NAME;
        if(defined('GROUP_NAME')) {
            $group   = C('URL_CASE_INSENSITIVE') ?strtolower(GROUP_NAME):GROUP_NAME;
            define('__GROUP__', GROUP_NAME == C('DEFAULT_GROUP') ?__APP__ : __APP__.'/'.$group);
            define('__URL__', __GROUP__.$depr.$module);
        }else{
            define('__URL__',__APP__.'/'.$module);
        }
        // ��ǰ������ַ
        define('__ACTION__',__URL__.$depr.ACTION_NAME);
        //��֤$_REQUEST����ȡֵ
        $_REQUEST = array_merge($_POST,$_GET);
    }

    /**
     +----------------------------------------------------------
     * ·�ɼ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static public function routerCheck() {
        $return   =  false;
        // ·�ɼ���ǩ
        tag('route_check',$return);
        return $return;
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
    static private function getModule($var) {
        $module = (!empty($_GET[$var])? $_GET[$var]:C('DEFAULT_MODULE'));
        unset($_GET[$var]);
        if(C('URL_CASE_INSENSITIVE')) {
            // URL��ַ�����ִ�Сд
            define('P_MODULE_NAME',strtolower($module));
            // ����ʶ��ʽ index.php/user_type/index/ ʶ�� UserTypeAction ģ��
            $module = ucfirst(parse_name(P_MODULE_NAME,1));
        }
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
    static private function getAction($var) {
        $action   = !empty($_POST[$var]) ?
            $_POST[$var] :
            (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_ACTION'));
        unset($_POST[$var],$_GET[$var]);
        define('P_ACTION_NAME',$action);
        return C('URL_CASE_INSENSITIVE')?strtolower($action):$action;
    }

    /**
     +----------------------------------------------------------
     * ���ʵ�ʵķ�������
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    static private function getGroup($var) {
        $group   = (!empty($_GET[$var])?$_GET[$var]:C('DEFAULT_GROUP'));
        unset($_GET[$var]);
        return ucfirst(strtolower($group));
    }

}