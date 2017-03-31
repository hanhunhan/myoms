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
// $Id: rest.php 2702 2012-02-02 12:35:01Z liu21st $

// RESTģʽ�����ļ�
return array(

    'core'         =>   array(
        THINK_PATH.'Common/functions.php', // ��׼ģʽ������
        CORE_PATH.'Core/Log.class.php',    // ��־������
        CORE_PATH.'Core/Dispatcher.class.php', // URL������
        CORE_PATH.'Core/App.class.php',   // Ӧ�ó�����
        CORE_PATH.'Core/View.class.php',  // ��ͼ��
        MODE_PATH.'Rest/Action.class.php',// ��������
    ),

    // ϵͳ��Ϊ�����ļ� [���� ֧������ֱ�Ӷ�������ļ������� ]
    'extends'    =>    MODE_PATH.'Rest/tags.php',

    // ģʽ�����ļ�  [֧������ֱ�Ӷ�������ļ�������]��������ͬ�򸲸���Ŀ�����ļ��е����ã�
    'config'   =>   MODE_PATH.'Rest/config.php',
);