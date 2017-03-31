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
// $Id: phprpc.php 2504 2011-12-28 07:35:29Z liu21st $

// PHPRPCģʽ�����ļ�
return array(

    'core'         =>   array(
        THINK_PATH.'Common/functions.php',   // ϵͳ������
        CORE_PATH.'Core/Log.class.php',// ��־����
        MODE_PATH.'Phprpc/App.class.php', // Ӧ�ó�����
        MODE_PATH.'Phprpc/Action.class.php',// ��������
        CORE_PATH.'Core/Model.class.php', // ģ����
    ),

    // ��Ŀ���������ļ� [֧������ֱ�Ӷ�������ļ�������]
    'alias'         =>    array(
        'Model'         =>   MODE_PATH.'Amf/Model.class.php',
        'Db'                  =>    MODE_PATH.'Phprpc/Db.class.php',
    ), 

    // ϵͳ��Ϊ�����ļ� [���� ֧������ֱ�Ӷ�������ļ������� ]
    'extends'    =>    array(), 

    // ��ĿӦ����Ϊ�����ļ� [֧������ֱ�Ӷ�������ļ�������]
    'tags'         =>   array(), 

);