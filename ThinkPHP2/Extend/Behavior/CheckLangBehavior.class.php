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
// $Id: CheckLangBehavior.class.php 2735 2012-02-15 03:11:13Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ���Լ�� ���Զ��������԰�
 +------------------------------------------------------------------------------
 */
class CheckLangBehavior extends Behavior {
    // ��Ϊ�������壨Ĭ��ֵ�� ������Ŀ�����и���
    protected $options   =  array(
            'LANG_SWITCH_ON'        => false,   // Ĭ�Ϲر����԰�����
            'LANG_AUTO_DETECT'      => true,   // �Զ�������� ���������Թ��ܺ���Ч
            'LANG_LIST' => 'zh-cn', // �����л��������б� �ö��ŷָ�
            'VAR_LANGUAGE'          => 'l',		// Ĭ�������л�����
        );

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$params){
        // ������̬����
        $this->checkLanguage();
    }

    /**
     +----------------------------------------------------------
     * ���Լ��
     * ��������֧�����ԣ����Զ��������԰�
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function checkLanguage() {
        // ���������԰����ܣ��������ؿ�������ļ�ֱ�ӷ���
        if (!C('LANG_SWITCH_ON')){
            return;
        }
        $langSet = C('DEFAULT_LANG');
        // ���������԰�����
        // �����Ƿ������Զ�������û�ȡ����ѡ��
        if (C('LANG_AUTO_DETECT')){
            if(isset($_GET[C('VAR_LANGUAGE')])){
                $langSet = $_GET[C('VAR_LANGUAGE')];// url�����������Ա���
                cookie('think_language',$langSet,3600);
            }elseif(cookie('think_language')){// ��ȡ�ϴ��û���ѡ��
                $langSet = cookie('think_language');
            }elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){// �Զ�������������
                preg_match('/^([a-z\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
                $langSet = $matches[1];
                cookie('think_language',$langSet,3600);
            }
            if(false === stripos(C('LANG_LIST'),$langSet)) { // �Ƿ����Բ���
                $langSet = C('DEFAULT_LANG');
            }
        }
        // ���嵱ǰ����
        define('LANG_SET',strtolower($langSet));
        // ��ȡ��Ŀ�������԰�
        if (is_file(LANG_PATH.LANG_SET.'/common.php'))
            L(include LANG_PATH.LANG_SET.'/common.php');
        $group = '';
        // ��ȡ��ǰ���鹫�����԰�
        if (defined('GROUP_NAME')){
            if (is_file(LANG_PATH.LANG_SET.'/'.GROUP_NAME.'.php'))
                L(include LANG_PATH.LANG_SET.'/'.GROUP_NAME.'.php');
            $group = GROUP_NAME.C('TMPL_FILE_DEPR');
        }
        // ��ȡ��ǰģ�����԰�
        if (is_file(LANG_PATH.LANG_SET.'/'.$group.strtolower(MODULE_NAME).'.php'))
            L(include LANG_PATH.LANG_SET.'/'.$group.strtolower(MODULE_NAME).'.php');
    }
}