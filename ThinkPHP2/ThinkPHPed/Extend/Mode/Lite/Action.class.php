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
 * ThinkPHP Action���������� ����ģʽ
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id: Action.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
abstract class Action {

    // ��ǰAction����
    private $name =  '';
    protected $tVar        =  array(); // ģ���������

   /**
     +----------------------------------------------------------
     * �ܹ����� ȡ��ģ�����ʵ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        tag('action_begin');
        //��������ʼ��
        if(method_exists($this,'_initialize'))
            $this->_initialize();
    }

   /**
     +----------------------------------------------------------
     * ��ȡ��ǰAction����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
    protected function getActionName() {
        if(empty($this->name)) {
            // ��ȡAction����
            $this->name     =   substr(get_class($this),0,-6);
        }
        return $this->name;
    }

    /**
     +----------------------------------------------------------
     * �Ƿ�AJAX����
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
            // �ж�Ajax��ʽ�ύ
            return true;
        return false;
    }

    /**
     +----------------------------------------------------------
     * ģ�������ֵ
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

    public function __set($name,$value) {
        $this->assign($name,$value);
    }

    /**
     +----------------------------------------------------------
     * ȡ��ģ�������ֵ
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

    /**
     +----------------------------------------------------------
     * ħ������ �в����ڵĲ�����ʱ��ִ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ������
     * @param array $args ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME)) {
            if(method_exists($this,'_empty')) {
                // ���������_empty���� �����
                $this->_empty($method,$args);
            }elseif(file_exists_case(C('TEMPLATE_NAME'))){
                // ����Ƿ����Ĭ��ģ�� �����ֱ�����ģ��
                $this->display();
            }else{
                // �׳��쳣
                throw_exception(L('_ERROR_ACTION_').ACTION_NAME);
            }
        }else{
            switch(strtolower($method)) {
                // �ж��ύ��ʽ
                case 'ispost':
                case 'isget':
                case 'ishead':
                case 'isdelete':
                case 'isput':
                    return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
                // ��ȡ���� ֧�ֹ��˺�Ĭ��ֵ ���÷�ʽ $this->_post($key,$filter,$default);
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
            if(isset($input[$args[0]])) { // ȡֵ����
                $data	 =	 $input[$args[0]];
                $fun  =  $args[1]?$args[1]:C('DEFAULT_FILTER');
                $data	 =	 $fun($data); // ��������
            }else{ // ����Ĭ��ֵ
                $data	 =	 isset($args[2])?$args[2]:NULL;
            }
            return $data;
        }
    }

    /**
     +----------------------------------------------------------
     * ����������ת�Ŀ�ݷ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message ������Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean $ajax �Ƿ�ΪAjax��ʽ
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function error($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }

    /**
     +----------------------------------------------------------
     * �����ɹ���ת�Ŀ�ݷ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $message ��ʾ��Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean $ajax �Ƿ�ΪAjax��ʽ
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function success($message,$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }

    /**
     +----------------------------------------------------------
     * Ajax��ʽ�������ݵ��ͻ���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data Ҫ���ص�����
     * @param String $info ��ʾ��Ϣ
     * @param boolean $status ����״̬
     * @param String $status ajax�������� JSON XML
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function ajaxReturn($data,$info='',$status=1,$type='') {
        $result  =  array();
        $result['status']  =  $status;
        $result['info'] =  $info;
        $result['data'] = $data;
        //��չajax��������, ��Action�ж���function ajaxAssign(&$result){} ���� ��չajax�������ݡ�
        if(method_exists($this,"ajaxAssign")) 
            $this->ajaxAssign($result);
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        if(strtoupper($type)=='JSON') {
            // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
            header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($result));
        }elseif(strtoupper($type)=='XML'){
            // ����xml��ʽ����
            header("Content-Type:text/xml; charset=utf-8");
            exit(xml_encode($result));
        }
    }

    /**
     +----------------------------------------------------------
     * Action��ת(URL�ض��� ֧��ָ��ģ�����ʱ��ת
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $url ��ת��URL���ʽ
     * @param array $params ����URL����
     * @param integer $delay ��ʱ��ת��ʱ�� ��λΪ��
     * @param string $msg ��ת��ʾ��Ϣ
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
     * Ĭ����ת���� ֧�ִ��������ȷ��ת
     * ����ģ����ʾ Ĭ��ΪpublicĿ¼�����successҳ��
     * ��ʾҳ��Ϊ������ ֧��ģ���ǩ
     +----------------------------------------------------------
     * @param string $message ��ʾ��Ϣ
     * @param Boolean $status ״̬
     * @param string $jumpUrl ҳ����ת��ַ
     * @param Boolean $ajax �Ƿ�ΪAjax��ʽ
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=false) {
        // �ж��Ƿ�ΪAJAX����
        if($ajax || $this->isAjax()) $this->ajaxReturn($ajax,$message,$status);
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // ��ʾ����
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //��������˹رմ��ڣ�����ʾ��Ϻ��Զ��رմ���
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // ״̬
        //��֤������ܾ�̬����Ӱ��
        C('HTML_CACHE_ON',false);
        if($status) { //���ͳɹ���Ϣ
            $this->assign('message',$message);// ��ʾ��Ϣ
            // �ɹ�������Ĭ��ͣ��1��
            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"1");
            // Ĭ�ϲ����ɹ��Զ����ز���ǰҳ��
            if(!$this->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            $this->display(C('TMPL_ACTION_SUCCESS'));
        }else{
            $this->assign('error',$message);// ��ʾ��Ϣ
            //��������ʱ��Ĭ��ͣ��3��
            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"3");
            // Ĭ�Ϸ�������Ļ��Զ�������ҳ
            if(!$this->get('jumpUrl')) $this->assign('jumpUrl',"javascript:history.back(-1);");
            $this->display(C('TMPL_ACTION_ERROR'));
            // ��ִֹ��  �����������ִ��
            exit ;
        }
    }

    /**
     +----------------------------------------------------------
     * ����ģ���ҳ����� ���Է����������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile ģ���ļ���
     * @param string $charset ģ������ַ���
     * @param string $contentType �������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function display($templateFile='',$charset='',$contentType='') {
        G('viewStartTime');
        // ��ͼ��ʼ��ǩ
        tag('view_begin',$templateFile);
        // ��������ȡģ������
        $content = $this->fetch($templateFile);
        // ���ģ������
        $this->show($content,$charset,$contentType);
        // ��ͼ������ǩ
        tag('view_end');
    }

    /**
     +----------------------------------------------------------
     * ��������ı����԰���Html
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $content �������
     * @param string $charset ģ������ַ���
     * @param string $contentType �������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function show($content,$charset='',$contentType=''){
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        if(empty($contentType)) $contentType = C('TMPL_CONTENT_TYPE');
        // ��ҳ�ַ�����
        header("Content-Type:".$contentType."; charset=".$charset);
        header("Cache-control: private");  //֧��ҳ�����
        header("X-Powered-By:TOPThink/".THINK_VERSION);
        // ���ģ���ļ�
        echo $content;
    }

    /**
     +----------------------------------------------------------
     * �����ͻ�ȡģ������ �������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $templateFile ģ���ļ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function fetch($templateFile='') {
        // ģ���ļ�������ǩ
        tag('view_template',$templateFile);
        // ģ���ļ�������ֱ�ӷ���
        if(!is_file($templateFile)) return NULL;
        // ҳ�滺��
        ob_start();
        ob_implicit_flush(0);
        // ��ͼ������ǩ
        $params = array('var'=>$this->tVar,'file'=>$templateFile);
        $result   =  tag('view_parse',$params);
        if(false === $result) { // δ������Ϊ �����PHPԭ��ģ��
            // ģ�����б����ֽ��Ϊ��������
            extract($this->tVar, EXTR_OVERWRITE);
            // ֱ������PHPģ��
            include $templateFile;
        }
        // ��ȡ����ջ���
        $content = ob_get_clean();
        // ���ݹ��˱�ǩ
        tag('view_filter',$content);
        // ���ģ���ļ�
        return $content;
    }

   /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // ������־
        if(C('LOG_RECORD')) Log::save();
        // ִ�к�������
        tag('action_end');
    }
}