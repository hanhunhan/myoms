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
// $Id: tags.php 2702 2012-02-02 12:35:01Z liu21st $

// ������Ϊ��չ�б��ļ�
return array(
    'app_begin'=>array(
        'CheckTemplate', // ģ����
    ),
    'route_check'=>array('CheckRoute', // ·�ɼ��
    ), 
    'app_end'=>array(
        'ShowPageTrace', // ҳ��Trace��ʾ
    ),
    'view_template'=>array(
        'LocationTemplate', // �Զ���λģ���ļ�
    ),
    'view_parse'=>array(
        'ParseTemplate', // ģ����� ֧��PHP������ģ������͵�����ģ������
    ),
    'view_filter'=>array(
        'ContentReplace', // ģ������滻
        'ShowRuntime', // ����ʱ����ʾ
    ),
);