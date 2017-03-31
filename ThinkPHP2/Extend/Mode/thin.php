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
// $Id: thin.php 2702 2012-02-02 12:35:01Z liu21st $

// ���ģʽ���Ķ����ļ��б�
return array(

    'core'         =>   array(
        THINK_PATH.'Common/functions.php',   // ϵͳ������
        CORE_PATH.'Core/Log.class.php',// ��־����
        MODE_PATH.'Thin/App.class.php', // Ӧ�ó�����
        MODE_PATH.'Thin/Action.class.php',// ��������
    ),

    // ��Ŀ���������ļ� [֧������ֱ�Ӷ�������ļ�������]
    'alias'         =>    array(
        'Model'         =>   MODE_PATH.'Thin/Model.class.php',
        'Db'                  =>    MODE_PATH.'Thin/Db.class.php',
    ), 

    // ϵͳ��Ϊ�����ļ� [���� ֧������ֱ�Ӷ�������ļ������� ]
    'extends'    =>    array(), 

    // ��ĿӦ����Ϊ�����ļ� [֧������ֱ�Ӷ�������ļ�������]
    'tags'         =>   array(), 

);