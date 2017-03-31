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
// $Id: View.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP ??????
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author liu21st <liu21st@gmail.com>
 * @version  $Id: View.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class View {
    protected $tVar        =  array(); // ??????????

    /**
     +----------------------------------------------------------
     * ?????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $name
     * @param mixed $value
     +----------------------------------------------------------
     */
    public function assign($name,$value=''){
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }elseif(is_object($name)){
            foreach($name as $key =>$val)
                $this->tVar[$key] = $val;
        }else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     +----------------------------------------------------------
     * ????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function get($name){
        if(isset($this->tVar[$name]))
            return $this->tVar[$name];
        else
            return false;
    }

    /* ????????????? */
    public function getAllVar(){
        return $this->tVar;
    }

    // ??????????§Ö???????
    public function traceVar(){
        foreach ($this->tVar as $name=>$val){
            dump($val,1,'['.$name.']<br/>');
        }
    }

    /**
     +----------------------------------------------------------
     * ?????????????? ??????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile ????????
     * @param string $charset ???????????
     * @param string $contentType ???????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function display($templateFile='',$charset='',$contentType='') {
        G('viewStartTime');
        // ?????????
        tag('view_begin',$templateFile);
        // ????????????????
        $content = $this->fetch($templateFile);
        // ??????????
        $this->show($content,$charset,$contentType);
        // ??????????
        tag('view_end');
    }

    /**
     +----------------------------------------------------------
     * ?????????????????Html
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $content ???????
     * @param string $charset ???????????
     * @param string $contentType ???????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function show($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        // ??????????
        header('Content-Type:'.$contentType.'; charset='.$charset);
        header('Cache-control: private');  //?????????
        header('X-Powered-By:ThinkPHP');
        // ?????????
        echo $content;
    }

    /**
     +----------------------------------------------------------
     * ??????????????? ???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile ????????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function fetch($templateFile='') {
        // ?????????????
        tag('view_template',$templateFile);
        // ??????????????????
        if(!is_file($templateFile)) return NULL;
        // ??H??
        ob_start();
        ob_implicit_flush(0);
        if('php' == strtolower(C('TMPL_ENGINE_TYPE'))) { // ???PHP??????
            // ??????§Ò????????????????
            extract($this->tVar, EXTR_OVERWRITE);
            // ???????PHP???
            include $templateFile;
        }else{
            // ??????????
            $params = array('var'=>$this->tVar,'file'=>$templateFile);
            tag('view_parse',$params);
        }
        // ???????????
        $content = ob_get_clean();
        // ?????????
        tag('view_filter',$content);
        // ?????????
        return $content;
    }
}