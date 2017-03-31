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
// $Id: Action.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP Action?????????? ??????
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id: Action.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
abstract class Action {

    // ??????????
    protected $view   =  null;
    // ???Action????
    private $name =  '';

   /**
     +----------------------------------------------------------
     * ??????? ????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        tag('action_begin');
        //??????????
        $this->view       = Think::instance('View');
        //???????????
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

   /**
     +----------------------------------------------------------
     * ??????Action????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
    protected function getActionName() {
        if(empty($this->name)) {
            // ???Action????
            $this->name     =   substr(get_class($this),0,-6);
        }
        return $this->name;
    }

    /**
     +----------------------------------------------------------
     * ???AJAX????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    protected function isAjax() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
            if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
                return true;
        }
        if(!empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')]))
            // ?§Ø?Ajax?????
            return true;
        return false;
    }

    /**
     +----------------------------------------------------------
     * ??????
     * ?????????????????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $templateFile ???????????????
     * ?????? ?????????¦Ë??????
     * @param string $charset ???????
     * @param string $contentType ???????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function display($templateFile='',$charset='',$contentType='') {
        $this->view->display($templateFile,$charset,$contentType);
    }

    /**
     +----------------------------------------------------------
     *  ?????????????
     * ????????????????fetch??????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $templateFile ???????????????
     * ?????? ?????????¦Ë??????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function fetch($templateFile='') {
        return $this->view->fetch($templateFile);
    }

    /**
     +----------------------------------------------------------
     *  ??????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @htmlfile ??????????????
     * @htmlpath ??????????¡¤??
     * @param string $templateFile ???????????????
     * ?????? ?????????¦Ë??????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function buildHtml($htmlfile='',$htmlpath='',$templateFile='') {
        $content = $this->fetch($templateFile);
        $htmlpath   = !empty($htmlpath)?$htmlpath:HTML_PATH;
        $htmlfile =  $htmlpath.$htmlfile.C('HTML_FILE_SUFFIX');
        if(!is_dir(dirname($htmlfile)))
            // ?????????????? ????
            mk_dir(dirname($htmlfile));
        if(false === file_put_contents($htmlfile,$content))
            throw_exception(L('_CACHE_WRITE_ERROR_').':'.$htmlfile);
        return $content;
    }

    /**
     +----------------------------------------------------------
     * ?????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $name ????????????
     * @param mixed $value ???????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function assign($name,$value='') {
        $this->view->assign($name,$value);
    }

    public function __set($name,$value) {
        $this->view->assign($name,$value);
    }

    /**
     +----------------------------------------------------------
     * ????????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $name ??????????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return $this->view->get($name);
    }

    /**
     +----------------------------------------------------------
     * ??????? ?§Ó?????????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ??????
     * @param array $args ????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME)) {
            if(method_exists($this,'_empty')) {
                // ?????????_empty???? ?????
                $this->_empty($method,$args);
            }elseif(file_exists_case(C('TEMPLATE_NAME'))){
                // ??????????????? ??????????????
                $this->display();
            }elseif(function_exists('__hack_action')) {
                // hack ??????????????
                __hack_action();
            }elseif(APP_DEBUG) {
                // ?????
                throw_exception(L('_ERROR_ACTION_').ACTION_NAME);
            }else{
                if(C('LOG_EXCEPTION_RECORD')) Log::write(L('_ERROR_ACTION_').ACTION_NAME);
                send_http_status(404);
                exit;
            }
        }else{
            switch(strtolower($method)) {
                // ?§Ø??????
                case 'ispost':
                case 'isget':
                case 'ishead':
                case 'isdelete':
                case 'isput':
                    return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
                // ??????? ??????????? ???¡Â?? $this->_post($key,$filter,$default);
                case '_get':      $input =& $_GET;break;
                case '_post':$input =& $_POST;break;
                case '_put': parse_str(file_get_contents('php://input'), $input);break;
                case '_request': $input =& $_REQUEST;break;
                case '_session': $input =& $_SESSION;break;
                case '_cookie':  $input =& $_COOKIE;break;
                case '_server':  $input =& $_SERVER;break;
                case '_globals':  $input =& $GLOBALS;break;
                default:
                    throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            }
            if(isset($input[$args[0]])) { // ??????
                $data	 =	 $input[$args[0]];
                $fun  =  $args[1]?$args[1]:C('DEFAULT_FILTER');
                $data	 =	 $fun($data); // ????????
            }else{ // ????????
                $data	 =	 isset($args[2])?$args[2]:NULL;
            }
            return $data;
        }
    }

    /**
     +----------------------------------------------------------
     * ??????????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message ???????
     * @param string $jumpUrl ?????????
     * @param Boolean $ajax ????Ajax???
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function error($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     +----------------------------------------------------------
     * ?????????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message ??????
     * @param string $jumpUrl ?????????
     * @param Boolean $ajax ????Ajax???
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function success($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     +----------------------------------------------------------
     * Ajax?????????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data ??????????
     * @param String $info ??????
     * @param boolean $status ??????
     * @param String $status ajax???????? JSON XML
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function ajaxReturn($data,$info='',$status=1,$type='') {
		$info = iconv("gb2312","utf-8",$info);
		$data = iconv("gb2312","utf-8",$data);
        $result  =  array();
        $result['status']  =  $status;
        $result['info'] =  $info;
        $result['data'] = $data;
        //???ajax????????, ??Action?§Ø???function ajaxAssign(&$result){} ???? ???ajax?????????
        if(method_exists($this,"ajaxAssign")) 
            $this->ajaxAssign($result);
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        if(strtoupper($type)=='JSON') {
            // ????JSON????????????? ?????????
            header("Content-Type:text/html; charset=gb2312");
            exit(json_encode($result));
        }elseif(strtoupper($type)=='XML'){
            // ????xml???????
            header("Content-Type:text/xml; charset=gb2312");
            exit(xml_encode($result));
        }elseif(strtoupper($type)=='EVAL'){
            // ???????§Ö?js???
            header("Content-Type:text/html; charset=gb2312");
            exit($data);
        }else{
            // TODO ???????????
        }
    }

    /**
     +----------------------------------------------------------
     * Action???(URL????? ????????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $url ?????URL????
     * @param array $params ????URL????
     * @param integer $delay ??????????? ??¦Ë???
     * @param string $msg ?????????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

    /**
     +----------------------------------------------------------
     * ?????????? ???????????????
     * ?????????? ????public???????success???
     * ????????????? ????????
     +----------------------------------------------------------
     * @param string $message ??????
     * @param Boolean $status ??
     * @param string $jumpUrl ?????????
     * @param Boolean $ajax ????Ajax???
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
		
        // ?§Ø?????AJAX????
        if($ajax || $this->isAjax()) $this->ajaxReturn($ajax,$message,$status);
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // ???????
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //?????????????????????????????????
        if($this->view->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // ??
        //???????????????????
        C('HTML_CACHE_ON',false);
        if($status) { //?????????
            $this->assign('message',$message);// ??????
            // ???????????????1??
            if(!$this->view->get('waitSecond'))    $this->assign('waitSecond',"1");
            // ???????????????????????
            if(!$this->view->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }else{
            $this->assign('error',$message);// ??????
            //?????????????????3??
            if(!$this->view->get('waitSecond'))    $this->assign('waitSecond',"3");
            // ??????????????????????
            if(!$this->view->get('jumpUrl')) $this->assign('jumpUrl',"javascript:history.back(-1);");
            $this->display(C('TMPL_ACTION_ERROR'));
            // ??????  ??????????????
            exit ;
        }
    }

   /**
     +----------------------------------------------------------
     * ????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // ???????
        if(C('LOG_RECORD')) Log::save();
        // ??§Ü???????
        tag('action_end');
    }
}