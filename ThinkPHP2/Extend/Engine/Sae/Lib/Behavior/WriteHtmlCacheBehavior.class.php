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
// $Id: WriteHtmlCacheBehavior.class.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ��̬����д��
 * �������ò������£�
 +------------------------------------------------------------------------------
 */
class WriteHtmlCacheBehavior extends Behavior {

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$content){
        if(C('HTML_CACHE_ON') && defined('HTML_FILE_NAME'))  {
            //��̬�ļ�д��
            // �������HTML���� ��鲢��дHTML�ļ�
            // û��ģ��Ĳ��������ɾ�̬�ļ�
            //[sae] ���ɾ�̬����
            $kv = Think::instance('SaeKVClient');
            if (!$kv->init())
                halt('��û�г�ʼ��KVDB������SAEƽ̨���г�ʼ��');
            trace('[SAE]��̬����',HTML_FILE_NAME);
            $kv->set(HTML_FILE_NAME,time().$content);
        }
    }
}