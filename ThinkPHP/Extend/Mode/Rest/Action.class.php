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
// $Id: Action.class.php 2832 2012-03-21 01:05:36Z huangdijia $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP RESTFul ���������� ������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author   liu21st <liu21st@gmail.com>
 * @version  $Id: Action.class.php 2832 2012-03-21 01:05:36Z huangdijia $
 +------------------------------------------------------------------------------
 */
abstract class Action {

    // ��ǰAction����
    private $name =  '';
    // ��ͼʵ��
    protected $view   =  null;
    protected $_method =  ''; // ��ǰ��������
    protected $_type = ''; // ��ǰ��Դ����
    // �������
    protected $_types = array();

   /**
     +----------------------------------------------------------
     * �ܹ����� ȡ��ģ�����ʵ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        //ʵ������ͼ��
        $this->view       = Think::instance('View');

        defined('__EXT__') or define('__EXT__','');
        if(''== __EXT__ || false === stripos(C('REST_CONTENT_TYPE_LIST'),__EXT__)) {
            // ��Դ����û��ָ�����߷Ƿ� ����Ĭ����Դ���ͷ���
            $this->_type   =  C('REST_DEFAULT_TYPE');
        }else{
            $this->_type   =  __EXT__;
        }

        // ����ʽ���
        $method  =  strtolower($_SERVER['REQUEST_METHOD']);
        if(false === stripos(C('REST_METHOD_LIST'),$method)) {
            // ����ʽ�Ƿ� ����Ĭ�����󷽷�
            $method = C('REST_DEFAULT_METHOD');
        }
        $this->_method = $method;
        // �����������Դ����
        $this->_types  = C('REST_OUTPUT_TYPE');

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
            if(method_exists($this,$method.'_'.$this->_method.'_'.$this->_type)) { // RESTFul����֧��
                $fun  =  $method.'_'.$this->_method.'_'.$this->_type;
                $this->$fun();
            }elseif($this->_method == C('REST_DEFAULT_METHOD') && method_exists($this,$method.'_'.$this->_type) ){
                $fun  =  $method.'_'.$this->_type;
                $this->$fun();
            }elseif($this->_type == C('REST_DEFAULT_TYPE') && method_exists($this,$method.'_'.$this->_method) ){
                $fun  =  $method.'_'.$this->_method;
                $this->$fun();
            }elseif(method_exists($this,'_empty')) {
                // ���������_empty���� �����
                $this->_empty($method,$args);
            }elseif(file_exists_case(C('TMPL_FILE_NAME'))){
                // ����Ƿ����Ĭ��ģ�� �����ֱ�����ģ��
                $this->display();
            }else{
                // �׳��쳣
                throw_exception(L('_ERROR_ACTION_').ACTION_NAME);
            }
        }else{
            switch(strtolower($method)) {
                // ��ȡ���� ֧�ֹ��˺�Ĭ��ֵ ���÷�ʽ $this->_post($key,$filter,$default);
                case '_get': $input =& $_GET;break;
                case '_post':$input =& $_POST;break;
                case '_put':
                case '_delete':parse_str(file_get_contents('php://input'), $input);break;
                case '_request': $input =& $_REQUEST;break;
                case '_session': $input =& $_SESSION;break;
                case '_cookie':  $input =& $_COOKIE;break;
                case '_server':  $input =& $_SERVER;break;
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
     * ģ����ʾ
     * �������õ�ģ��������ʾ������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $templateFile ָ��Ҫ���õ�ģ���ļ�
     * Ĭ��Ϊ�� ��ϵͳ�Զ���λģ���ļ�
     * @param string $charset �������
     * @param string $contentType �������
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function display($templateFile='',$charset='',$contentType='') {
        $this->view->display($templateFile,$charset,$contentType);
    }

    /**
     +----------------------------------------------------------
     * ģ�������ֵ
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $name Ҫ��ʾ��ģ�����
     * @param mixed $value ������ֵ
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
     * ����ҳ�������CONTENT_TYPE�ͱ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $type content_type ���Ͷ�Ӧ����չ��
     * @param string $charset ҳ���������
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function setContentType($type, $charset=''){
        if(headers_sent()) return;
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        $type = strtolower($type);
        if(isset($this->_types[$type])) //����content_type
            header('Content-Type: '.$this->_types[$type].'; charset='.$charset);
    }

    /**
     +----------------------------------------------------------
     * �����������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data Ҫ���ص�����
     * @param String $type �������� JSON XML
     * @param integer $code HTTP״̬
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function response($data,$type='',$code=200) {
        $this->sendHttpStatus($code);
        exit($this->encodeData($data,strtolower($type)));
    }

    /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data Ҫ���ص�����
     * @param String $type �������� JSON XML
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function encodeData($data,$type='') {
        if(empty($data))  return '';
        if(empty($type)) $type =  $this->_type;
        if('json' == $type) {
            // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
            $data = json_encode($data);
        }elseif('xml' == $type){
            // ����xml��ʽ����
            $data = xml_encode($data);
        }elseif('php'==$type){
            $data = serialize($data);
        }// Ĭ��ֱ�����
        $this->setContentType($type);
        header('Content-Length: ' . strlen($data));
        return $data;
    }

    // ����Http״̬��Ϣ
    protected function sendHttpStatus($code) {
        static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        if(isset($_status[$code])) {
            header('HTTP/1.1 '.$code.' '.$_status[$code]);
            // ȷ��FastCGIģʽ������
            header('Status:'.$code.' '.$_status[$code]);
        }
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
