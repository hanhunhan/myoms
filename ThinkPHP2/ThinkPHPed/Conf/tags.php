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
// $Id: tags.php 2716 2012-02-07 06:35:12Z liu21st $

// ϵͳĬ�ϵĺ�����Ϊ��չ�б��ļ�
return array(
    'app_init'=>array(
    ),
    'app_begin'=>array(
        'ReadHtmlCache', // ��ȡ��̬����
        'CheckTemplate', // ģ����
    ),
    'route_check'=>array(
        'CheckRoute', // ·�ɼ��
    ), 
    'app_end'=>array(),
    'path_info'=>array(),
    'action_begin'=>array(),
    'action_end'=>array(),
    'view_begin'=>array(),
    'view_template'=>array(
        'LocationTemplate', // �Զ���λģ���ļ�
    ),
    'view_parse'=>array(
        'ParseTemplate', // ģ����� ֧��PHP������ģ������͵�����ģ������
    ),
    'view_filter'=>array(
        'ContentReplace', // ģ������滻
        'TokenBuild',   // ������
        'WriteHtmlCache', // д�뾲̬����
        'ShowRuntime', // ����ʱ����ʾ
    ),
    'view_end'=>array(
        'ShowPageTrace', // ҳ��Trace��ʾ
    ),
);