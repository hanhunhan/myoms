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
// $Id: BrowserCheckBehavior.class.php 2616 2012-01-16 08:36:46Z liu21st $

class BrowserCheckBehavior extends Behavior {
    protected $options   =  array(
            'LIMIT_REFLESH_TIMES'=>10,
        );
    public function run(&$params) {
        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            //	����ҳ���ˢ�»���
            $guid	=	md5($_SERVER['PHP_SELF']);
            // ���ҳ��ˢ�¼��
            if(Cookie::is_set('_last_visit_time_'.$guid) && Cookie::get('_last_visit_time_'.$guid)>time()-C('LIMIT_REFLESH_TIMES')) {
                // ҳ��ˢ�¶�ȡ���������
                header('HTTP/1.1 304 Not Modified');
                exit;
            }else{
                // ���浱ǰ��ַ����ʱ��
                Cookie::set('_last_visit_time_'.$guid, $_SERVER['REQUEST_TIME'],$_SERVER['REQUEST_TIME']+3600);
                //header('Last-Modified:'.(date('D,d M Y H:i:s',$_SERVER['REQUEST_TIME']-C('LIMIT_REFLESH_TIMES'))).' GMT');
            }
        }
    }
}