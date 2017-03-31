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
// $Id: cli.php 2702 2012-02-02 12:35:01Z liu21st $

// ������ģʽ�����ļ�
return array(
    'core'         =>   array(
        MODE_PATH.'Cli/functions.php',   // ������ϵͳ������
        MODE_PATH.'Cli/Log.class.php',
        MODE_PATH.'Cli/App.class.php',
        MODE_PATH.'Cli/Action.class.php',
    ),

    // ��Ŀ���������ļ� [֧������ֱ�Ӷ�������ļ�������]
    'alias'         =>    array(
        'Model'    =>   MODE_PATH.'Cli/Model.class.php',
        'Db'        =>    MODE_PATH.'Cli/Db.class.php',
        'Cache'         => CORE_PATH.'Core/Cache.class.php',
        'Debug'         => CORE_PATH.'Util/Debug.class.php',
    ), 

    // ϵͳ��Ϊ�����ļ� [���� ֧������ֱ�Ӷ�������ļ������� ]
    'extends'    =>    array(), 

    // ��ĿӦ����Ϊ�����ļ� [֧������ֱ�Ӷ�������ļ�������]
    'tags'         =>   array(), 

);