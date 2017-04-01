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
// $Id: TemplateEase.class.php 2653 2012-01-23 06:38:07Z liu21st $

/**
 +------------------------------------------------------------------------------
 * EaseTemplateģ�����������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: TemplateEase.class.php 2653 2012-01-23 06:38:07Z liu21st $
 +------------------------------------------------------------------------------
 */
class TemplateEase {
    /**
     +----------------------------------------------------------
     * ��Ⱦģ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile ģ���ļ���
     * @param array $var ģ�����
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function fetch($templateFile,$var) {
        $templateFile = substr($templateFile,strlen(TMPL_PATH),-5);
        $CacheDir = substr(CACHE_PATH,0,-1);
        $TemplateDir = substr(TMPL_PATH,0,-1);
        vendor('EaseTemplate.template#ease');
        if(C('TMPL_ENGINE_CONFIG')) {
            $config  =  C('TMPL_ENGINE_CONFIG');
        }else{
            $config  =                    array(
            'CacheDir'=>$CacheDir,
            'TemplateDir'=>$TemplateDir,
            'TplType'=>'html'
             );
        }
        $tpl = new EaseTemplate($config);
        $tpl->set_var($var);
        $tpl->set_file($templateFile);
        $tpl->p();
    }
}