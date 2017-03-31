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
// $Id: TemplateLite.class.php 2653 2012-01-23 06:38:07Z liu21st $

/**
 +------------------------------------------------------------------------------
 * TemplateLiteģ�����������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: TemplateLite.class.php 2653 2012-01-23 06:38:07Z liu21st $
 +------------------------------------------------------------------------------
 */
class TemplateLite {
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
        $templateFile=substr($templateFile,strlen(TMPL_PATH));
        vendor("TemplateLite.class#template");
        $tpl = new Template_Lite();
        if(C('TMPL_ENGINE_CONFIG')) {
            $config  =  C('TMPL_ENGINE_CONFIG');
            foreach ($config as $key=>$val){
                $tpl->{$key}   =  $val;
            }
        }else{
            $tpl->template_dir = TMPL_PATH;
            $tpl->compile_dir = CACHE_PATH ;
            $tpl->cache_dir = TEMP_PATH ;
        }
        $tpl->assign($var);
        $tpl->display($templateFile);
    }
}