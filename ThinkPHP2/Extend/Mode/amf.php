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
// $Id: amf.php 2504 2011-12-28 07:35:29Z liu21st $

// AMFģʽ�����ļ�
return array(
    'core'         =>   array(
        THINK_PATH.'Common/functions.php',   // ϵͳ������
        CORE_PATH.'Core/Log.class.php',// ��־����
        MODE_PATH.'Amf/App.class.php', // Ӧ�ó�����
        MODE_PATH.'Amf/Action.class.php',// ��������
    ),

    // ��Ŀ���������ļ� [֧������ֱ�Ӷ�������ļ�������]
    'alias'         =>    array(
        'Model'         =>   MODE_PATH.'Amf/Model.class.php',
        'Db'                  =>    MODE_PATH.'Amf/Db.class.php',
    ), 

    // ϵͳ��Ϊ�����ļ� [���� ֧������ֱ�Ӷ�������ļ������� ]
    'extends'    =>    array(), 

    // ��ĿӦ����Ϊ�����ļ� [֧������ֱ�Ӷ�������ļ�������]
    'tags'         =>   array(), 
);