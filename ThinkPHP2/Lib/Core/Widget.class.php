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
// $Id: Widget.class.php 2783 2012-02-25 06:49:45Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Widget�� ������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: Widget.class.php 2783 2012-02-25 06:49:45Z liu21st $
 +------------------------------------------------------------------------------
 */
abstract class Widget {

    // ʹ�õ�ģ������ ÿ��Widget���Ե������ò���ϵͳӰ��
    protected $template =  '';

    /**
     +----------------------------------------------------------
     * ��Ⱦ��� render������WidgetΨһ�Ľӿ�
     * ʹ���ַ������� �������κ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data  Ҫ��Ⱦ������
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    abstract public function render($data);

    /**
     +----------------------------------------------------------
     * ��Ⱦģ����� ��render�����ڲ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile  ģ���ļ�
     * @param mixed $var  ģ�����
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function renderFile($templateFile='',$var='') {
        ob_start();
        ob_implicit_flush(0);
        if(!file_exists_case($templateFile)){
            // �Զ���λģ���ļ�
            $name   = substr(get_class($this),0,-6);
            $filename   =  empty($templateFile)?$name:$templateFile;
            $templateFile = LIB_PATH.'Widget/'.$name.'/'.$filename.C('TMPL_TEMPLATE_SUFFIX');
            if(!file_exists_case($templateFile))
                throw_exception(L('_TEMPLATE_NOT_EXIST_').'['.$templateFile.']');
        }
        $template   =  strtolower($this->template?$this->template:(C('TMPL_ENGINE_TYPE')?C('TMPL_ENGINE_TYPE'):'php'));
        if('php' == $template) {
            // ʹ��PHPģ��
            if(!empty($var)) extract($var, EXTR_OVERWRITE);
            // ֱ������PHPģ��
            include $templateFile;
        }elseif('think'==$template){ // ����Thinkģ������
            if($this->checkCache($templateFile)) { // ������Ч
                // �ֽ����������ģ�建��
                extract($var, EXTR_OVERWRITE);
                //����ģ�滺���ļ�
                include C('CACHE_PATH').md5($templateFile).C('TMPL_CACHFILE_SUFFIX');
            }else{
                $tpl = Think::instance('ThinkTemplate');
                // ���벢����ģ���ļ�
                $tpl->fetch($templateFile,$var);
            }
        }else{
            $class   = 'Template'.ucwords($template);
            if(is_file(CORE_PATH.'Driver/Template/'.$class.'.class.php')) {
                // ��������
                $path = CORE_PATH;
            }else{ // ��չ����
                $path = EXTEND_PATH;
            }
            require_cache($path.'Driver/Template/'.$class.'.class.php');
            $tpl   =  new $class;
            $tpl->fetch($templateFile,$var);
        }
        $content = ob_get_clean();
        return $content;
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
    protected function checkCache($tmplTemplateFile) {
        if (!C('TMPL_CACHE_ON')) // ���ȶ������趨���
            return false;
        $tmplCacheFile = C('CACHE_PATH').md5($tmplTemplateFile).C('TMPL_CACHFILE_SUFFIX');
        if(!is_file($tmplCacheFile)){
            return false;
        }elseif (filemtime($tmplTemplateFile) > filemtime($tmplCacheFile)) {
            // ģ���ļ�����и����򻺴���Ҫ����
            return false;
        }elseif (C('TMPL_CACHE_TIME') != 0 && time() > filemtime($tmplCacheFile)+C('TMPL_CACHE_TIME')) {
            // �����Ƿ�����Ч��
            return false;
        }
        // ������Ч
        return true;
    }
}