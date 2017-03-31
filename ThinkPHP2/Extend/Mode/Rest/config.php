<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: config.php 2668 2012-01-26 13:07:16Z liu21st $

return array(
    'REST_METHOD_LIST'           => 'get,post,put,delete', // ��������������б�
    'REST_DEFAULT_METHOD'     => 'get', // Ĭ����������
    'REST_CONTENT_TYPE_LIST' => 'html,xml,json,rss', // REST�����������Դ�����б�
    'REST_DEFAULT_TYPE'          => 'html', // Ĭ�ϵ���Դ����
    'REST_OUTPUT_TYPE'           => array(  // REST�����������Դ�����б�
            'xml' => 'application/xml',
            'json' => 'application/json',
            'html' => 'text/html',
        ),
);