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
// $Id: Widget.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Widget?? ??????
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: Widget.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
abstract class Widget {

    // ??????????? ???Widget??????????¨°????????
    protected $template =  '';

    /**
     +----------------------------------------------------------
     * ?????? render??????Widget¦·?????
     * ???????????? ???????¦Ê????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data  ??????????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    abstract public function render($data);

    /**
     +----------------------------------------------------------
     * ????????? ??render???????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile  ??????
     * @param mixed $var  ??????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function renderFile($templateFile='',$var='') {
        ob_start();
        ob_implicit_flush(0);
        if(!file_exists_case($templateFile)){
            // ?????¦Ë??????
            $name   = substr(get_class($this),0,-6);
            $filename   =  empty($templateFile)?$name:$templateFile;
            $templateFile = LIB_PATH.'Widget/'.$name.'/'.$filename.C('TMPL_TEMPLATE_SUFFIX');
            if(!file_exists_case($templateFile))
                throw_exception(L('_TEMPLATE_NOT_EXIST_').'['.$templateFile.']');
        }
        $template   =  $this->template?$this->template:strtolower(C('TMPL_ENGINE_TYPE')?C('TMPL_ENGINE_TYPE'):'php');
        if('php' == $template) {
            // ???PHP???
            if(!empty($var)) extract($var, EXTR_OVERWRITE);
            // ???????PHP???
            include $templateFile;
        }elseif('think'==$template){ // ????Think???????
            if($this->checkCache($templateFile)) { // ??????§¹
                // ??????????????½¨??
                extract($var, EXTR_OVERWRITE);
                //??????H?????
                include C('CACHE_PATH').md5($templateFile).C('TMPL_CACHFILE_SUFFIX');
            }else{
                $tpl = Think::instance('ThinkTemplate');
                // ??????????????
                $tpl->fetch($templateFile,$var);
            }
        }else{
            $class   = 'Template'.ucwords($template);
            if(is_file(CORE_PATH.'Driver/Template/'.$class.'.class.php')) {
                // ????????
                $path = CORE_PATH;
            }else{ // ???????
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
     * ??üv??????????§¹
     * ?????§¹????????¡À???
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $tmplTemplateFile  ????????
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    protected function checkCache($tmplTemplateFile) {
        if (!C('TMPL_CACHE_ON')) // ??????????Ú…???
            return false;
        $tmplCacheFile = C('CACHE_PATH').md5($tmplTemplateFile).C('TMPL_CACHFILE_SUFFIX');
        if(!is_file($tmplCacheFile)){
            return false;
        }elseif (filemtime($tmplTemplateFile) > filemtime($tmplCacheFile)) {
            // ??????????§Ú??????????????
            return false;
        }elseif (C('TMPL_CACHE_TIME') != 0 && time() > filemtime($tmplCacheFile)+C('TMPL_CACHE_TIME')) {
            // ???????????§¹??
            return false;
        }
        // ??????§¹
        return true;
    }
}