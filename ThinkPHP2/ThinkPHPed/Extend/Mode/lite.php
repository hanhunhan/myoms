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
// $Id: lite.php 2702 2012-02-02 12:35:01Z liu21st $

// Liteģʽ�����ļ�
return array(
    'core'         =>   array(
        THINK_PATH.'Common/functions.php',   // ϵͳ������
        CORE_PATH.'Core/Log.class.php',// ��־����
        MODE_PATH.'Lite/App.class.php', // Ӧ�ó�����
        MODE_PATH.'Lite/Action.class.php',// ��������
        MODE_PATH.'Lite/Dispatcher.class.php',
    ),

    // ��Ŀ���������ļ� [֧������ֱ�Ӷ�������ļ�������]
    'alias'         =>    array(
        'Model'         =>   MODE_PATH.'Lite/Model.class.php',
        'Db'                  =>    MODE_PATH.'Lite/Db.class.php',
        'ThinkTemplate' => CORE_PATH.'Template/ThinkTemplate.class.php',
        'TagLib'        => CORE_PATH.'Template/TagLib.class.php',
        'Cache'         => CORE_PATH.'Core/Cache.class.php',
        'Debug'         => CORE_PATH.'Util/Debug.class.php',
        'Session'       => CORE_PATH.'Util/Session.class.php',
        'TagLibCx'      => CORE_PATH.'Driver/TagLib/TagLibCx.class.php',
    ), 

    // ϵͳ��Ϊ�����ļ� [���� ֧������ֱ�Ӷ�������ļ������� ]
    'extends'    =>    MODE_PATH.'Lite/tags.php',

);