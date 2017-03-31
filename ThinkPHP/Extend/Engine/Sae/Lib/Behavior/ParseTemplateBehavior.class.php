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
// $Id: ParseTemplateBehavior.class.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $

/**
 +------------------------------------------------------------------------------
 * ϵͳ��Ϊ��չ ģ�����
 +------------------------------------------------------------------------------
 */

class ParseTemplateBehavior extends Behavior {
    // ��Ϊ�������壨Ĭ��ֵ�� ������Ŀ�����и���
    protected $options   =  array(
        // ��������
        'TMPL_ENGINE_TYPE'		=> 'Think',     // Ĭ��ģ������ �������ý���ʹ��Thinkģ��������Ч
        'TMPL_CACHFILE_SUFFIX'  => '.php',      // Ĭ��ģ�建���׺
        'TMPL_DENY_FUNC_LIST'	=> 'echo,exit',	// ģ��������ú���
        'TMPL_DENY_PHP'  =>false, // Ĭ��ģ�������Ƿ����PHPԭ������
        'TMPL_L_DELIM'          => '{',			// ģ��������ͨ��ǩ��ʼ���
        'TMPL_R_DELIM'          => '}',			// ģ��������ͨ��ǩ�������
        'TMPL_VAR_IDENTIFY'     => 'array',     // ģ�����ʶ�������Զ��ж�,����Ϊ'obj'���ʾ����
        'TMPL_STRIP_SPACE'      => true,       // �Ƿ�ȥ��ģ���ļ������html�ո��뻻��
        'TMPL_CACHE_ON'			=> true,        // �Ƿ���ģ����뻺��,��Ϊfalse��ÿ�ζ������±���
        'TMPL_CACHE_TIME'		=>	 0,         // ģ�建����Ч�� 0 Ϊ���ã�(������Ϊֵ����λ:��)
        'TMPL_LAYOUT_ITEM'    =>   '{__CONTENT__}', // ����ģ��������滻��ʶ
        'LAYOUT_ON'           => false, // �Ƿ����ò���
        'LAYOUT_NAME'       => 'layout', // ��ǰ�������� Ĭ��Ϊlayout

        // Thinkģ�������ǩ������趨
        'TAGLIB_BEGIN'          => '<',  // ��ǩ���ǩ��ʼ���
        'TAGLIB_END'            => '>',  // ��ǩ���ǩ�������
        'TAGLIB_LOAD'           => true, // �Ƿ�ʹ�����ñ�ǩ��֮���������ǩ�⣬Ĭ���Զ����
        'TAGLIB_BUILD_IN'       => 'cx', // ���ñ�ǩ������(��ǩʹ�ò���ָ����ǩ������),�Զ��ŷָ� ע�����˳��
        'TAGLIB_PRE_LOAD'       => '',   // ��Ҫ������صı�ǩ��(��ָ����ǩ������)������Զ��ŷָ�
        );

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$_data){
        $engine  = strtolower(C('TMPL_ENGINE_TYPE'));
        if('think'==$engine){ //[sae] ����Thinkģ������
            if($this->checkCache($_data['file'])) { // ������Ч
                SaeMC::include_file(md5($_data['file']).C('TMPL_CACHFILE_SUFFIX'),$_data['var']);
            }else{
                $tpl = Think::instance('ThinkTemplate');
                // ���벢����ģ���ļ�
                $tpl->fetch($_data['file'],$_data['var']);
            }
        }else{
            // ���õ�����ģ��������������
            $class   = 'Template'.ucwords($engine);
            if(is_file(CORE_PATH.'Driver/Template/'.$class.'.class.php')) {
                // ��������
                $path = CORE_PATH;
            }else{ // ��չ����
                $path = EXTEND_PATH;
            }
            if(require_cache($path.'Driver/Template/'.$class.'.class.php')) {
                $tpl   =  new $class;
                $tpl->fetch($_data['file'],$_data['var']);
            }else {  // ��û�ж���
                throw_exception(L('_NOT_SUPPERT_').': ' . $class);
            }
        }
        //[sae] ���trace��Ϣ��
        trace(array(
            '[SAE]���Ļ���'=>$_SERVER['HTTP_APPVERSION'].'/'.RUNTIME_FILE,
            '[SAE]ģ�建��'=>$_SERVER['HTTP_APPVERSION'].'/'.md5($_data['file']).C('TMPL_CACHFILE_SUFFIX')
        ));
    }

    /**
     +----------------------------------------------------------
     * ��黺���ļ��Ƿ���Ч
     * �����Ч����Ҫ���±���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $tmplTemplateFile  ģ���ļ���
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    //[sae] ���ģ��
    protected function checkCache($tmplTemplateFile) {
        if (!C('TMPL_CACHE_ON')) // ���ȶ������趨���
            return false;
        //[sae] ����ģ��Ŀ¼����ģ������
        $tmplCacheFile = md5($tmplTemplateFile).C('TMPL_CACHFILE_SUFFIX');
        if(!SaeMC::file_exists($tmplCacheFile)){
            return false;
        //}elseif (filemtime($tmplTemplateFile) > filemtime($tmplCacheFile)) {
        }elseif (filemtime($tmplTemplateFile) > SaeMC::filemtime($tmplCacheFile)) {
            // ģ���ļ�����и����򻺴���Ҫ����
            return false;
        }elseif (C('TMPL_CACHE_TIME') != 0 && time() > SaeMC::filemtime($tmplCacheFile)+C('TMPL_CACHE_TIME')) {
            // �����Ƿ�����Ч��
            return false;
        }
        // ��������ģ��
        if(C('LAYOUT_ON')) {
            $layoutFile  =  THEME_PATH.C('LAYOUT_NAME').C('TMPL_TEMPLATE_SUFFIX');
            if(filemtime($layoutFile) > SaeMC::filemtime($tmplCacheFile)) {
                return false;
            }
        }
        // ������Ч
        return true;
    }
}