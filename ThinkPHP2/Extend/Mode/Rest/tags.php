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
// $Id: tags.php 2802 2012-03-06 06:19:07Z liu21st $

// Rest ϵͳ��Ϊ��չ�б��ļ�
return array(
    'app_begin'=>array(
        'ReadHtmlCache', // ��ȡ��̬����
    ),
    'route_check'=>array(
        'CheckRestRoute', // ·�ɼ��
    ), 
    'view_end'=>array(
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
        'TokenBuild',   // ������
        'WriteHtmlCache', // д�뾲̬����
        'ShowRuntime', // ����ʱ����ʾ
    ),
    'path_info'=>array(
        'CheckUrlExt'
    ),
);