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
// $Id: tags.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $

// ϵͳĬ�ϵĺ�����Ϊ��չ�б��ļ�
//[sae]�������������SAE��ʱ������ģʽҲ���ض�Ӧ�ļ���
alias_import(array(
    'ParseTemplateBehavior'=>SAE_PATH.'Lib/Behavior/ParseTemplateBehavior.class.php',
    'ReadHtmlCacheBehavior'=>SAE_PATH.'Lib/Behavior/ReadHtmlCacheBehavior.class.php',
    'WriteHtmlCacheBehavior'=>SAE_PATH.'Lib/Behavior/WriteHtmlCacheBehavior.class.php'
));
return array(
    'app_init'=>array(
    ),
    'app_begin'=>array(
        'ReadHtmlCache'=>SAE_PATH.'Lib/Behavior/ReadHtmlCacheBehavior.class.php', // ��ȡ��̬����
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
        'ParseTemplate'=>SAE_PATH.'Lib/Behavior/ParseTemplateBehavior.class.php', //[sae] ģ����� ֧��PHP������ģ������͵�����ģ������
    ),
    'view_filter'=>array(
        'ContentReplace', // ģ������滻
        'TokenBuild',   // ������
        'WriteHtmlCache'=>SAE_PATH.'Lib/Behavior/WriteHtmlCacheBehavior.class.php', // д�뾲̬����
        'ShowRuntime', // ����ʱ����ʾ
    ),
    'view_end'=>array(
        'ShowPageTrace', // ҳ��Trace��ʾ
    ),
);