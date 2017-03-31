<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: App.class.php 2504 2011-12-28 07:35:29Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP AMFģʽӦ�ó�����
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

    	//�������
    	Vendor('phpRPC.phprpc_server');
    	//ʵ����phprpc
    	$server = new PHPRPC_Server();
        $actions =  explode(',',C('APP_PHPRPC_ACTIONS'));
        foreach ($actions as $action){
       	    //$server -> setClass($action.'Action'); 
			$temp = $action.'Action';
			$methods = get_class_methods($temp);
			$server->add($methods,new $temp);
		}
        if(APP_DEBUG) {
            $server->setDebugMode(true);
        }
        $server->setEnableGZIP(true);
		$server->start();
		//C('PHPRPC_COMMENT',$server->comment());
		echo $server->comment();
        // ������־��¼
        if(C('LOG_RECORD')) Log::save();
        return ;
    }

};