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
// $Id: TagLib.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP��ǩ��TagLib��������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Template
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: TagLib.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class TagLib {

    /**
     +----------------------------------------------------------
     * ��ǩ�ⶨ��XML�ļ�
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $xml = '';
    protected $tags = array();// ��ǩ����
    /**
     +----------------------------------------------------------
     * ��ǩ������
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $tagLib ='';

    /**
     +----------------------------------------------------------
     * ��ǩ���ǩ�б�
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $tagList = array();

    /**
     +----------------------------------------------------------
     * ��ǩ���������
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $parse = array();

    /**
     +----------------------------------------------------------
     * ��ǩ���Ƿ���Ч
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $valid = false;

    /**
     +----------------------------------------------------------
     * ��ǰģ�����
     +----------------------------------------------------------
     * @var object
     * @access protected
     +----------------------------------------------------------
     */
    protected $tpl;

    protected $comparison = array(' nheq '=>' !== ',' heq '=>' === ',' neq '=>' != ',' eq '=>' == ',' egt '=>' >= ',' gt '=>' > ',' elt '=>' <= ',' lt '=>' < ');

    /**
     +----------------------------------------------------------
     * �ܹ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct() {
        $this->tagLib  = strtolower(substr(get_class($this),6));
        $this->tpl       = Think::instance('ThinkTemplate');//ThinkTemplate::getInstance();
    }

    /**
     +----------------------------------------------------------
     * TagLib��ǩ���Է��� ���ر�ǩ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $tagStr ��ǩ����
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseXmlAttr($attr,$tag) {
        //XML������ȫ����
        $attr = str_replace('&','___', $attr);
        $xml =  '<tpl><tag '.$attr.' /></tpl>';
        $xml = simplexml_load_string($xml);
        if(!$xml) {
            throw_exception(L('_XML_TAG_ERROR_').' : '.$attr);
        }
        $xml = (array)($xml->tag->attributes());
        $array = array_change_key_case($xml['@attributes']);
        if($array) {
            $attrs  = explode(',',$this->tags[strtolower($tag)]['attr']);
            foreach($attrs as $name) {
                if( isset($array[$name])) {
                    $array[$name] = str_replace('___','&',$array[$name]);
                }
            }
            return $array;
        }
    }

    /**
     +----------------------------------------------------------
     * ������������ʽ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $condition ����ʽ��ǩ����
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseCondition($condition) {
        $condition = str_ireplace(array_keys($this->comparison),array_values($this->comparison),$condition);
        $condition = preg_replace('/\$(\w+):(\w+)\s/is','$\\1->\\2 ',$condition);
        switch(strtolower(C('TMPL_VAR_IDENTIFY'))) {
            case 'array': // ʶ��Ϊ����
                $condition = preg_replace('/\$(\w+)\.(\w+)\s/is','$\\1["\\2"] ',$condition);
                break;
            case 'obj':  // ʶ��Ϊ����
                $condition = preg_replace('/\$(\w+)\.(\w+)\s/is','$\\1->\\2 ',$condition);
                break;
            default:  // �Զ��ж��������� ֻ֧�ֶ�ά
                $condition = preg_replace('/\$(\w+)\.(\w+)\s/is','(is_array($\\1)?$\\1["\\2"]:$\\1->\\2) ',$condition);
        }
        return $condition;
    }

    /**
     +----------------------------------------------------------
     * �Զ�ʶ�𹹽�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ��������
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function autoBuildVar($name) {
        if('Think.' == substr($name,0,6)){
            // �������
            return $this->parseThinkVar($name);
        }elseif(strpos($name,'.')) {
            $vars = explode('.',$name);
            $var  =  array_shift($vars);
            switch(strtolower(C('TMPL_VAR_IDENTIFY'))) {
                case 'array': // ʶ��Ϊ����
                    $name = '$'.$var;
                    foreach ($vars as $key=>$val){
                        if(0===strpos($val,'$')) {
                            $name .= '["{'.$val.'}"]';
                        }else{
                            $name .= '["'.$val.'"]';
                        }
                    }
                    break;
                case 'obj':  // ʶ��Ϊ����
                    $name = '$'.$var;
                    foreach ($vars as $key=>$val)
                        $name .= '->'.$val;
                    break;
                default:  // �Զ��ж��������� ֻ֧�ֶ�ά
                    $name = 'is_array($'.$var.')?$'.$var.'["'.$vars[0].'"]:$'.$var.'->'.$vars[0];
            }
        }elseif(strpos($name,':')){
            // ����Ķ���ʽ֧��
            $name   =   '$'.str_replace(':','->',$name);
        }elseif(!defined($name)) {
            $name = '$'.$name;
        }
        return $name;
    }

    /**
     +----------------------------------------------------------
     * ���ڱ�ǩ�������������ģ���������
     * ��ʽ �� Think. ��ͷ�ı�����������ģ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $varStr  �����ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function parseThinkVar($varStr){
        $vars = explode('.',$varStr);
        $vars[1] = strtoupper(trim($vars[1]));
        $parseStr = '';
        if(count($vars)>=3){
            $vars[2] = trim($vars[2]);
            switch($vars[1]){
                case 'SERVER':    $parseStr = '$_SERVER[\''.$vars[2].'\']';break;
                case 'GET':         $parseStr = '$_GET[\''.$vars[2].'\']';break;
                case 'POST':       $parseStr = '$_POST[\''.$vars[2].'\']';break;
                case 'COOKIE':    $parseStr = '$_COOKIE[\''.$vars[2].'\']';break;
                case 'SESSION':   $parseStr = '$_SESSION[\''.$vars[2].'\']';break;
                case 'ENV':         $parseStr = '$_ENV[\''.$vars[2].'\']';break;
                case 'REQUEST':  $parseStr = '$_REQUEST[\''.$vars[2].'\']';break;
                case 'CONST':     $parseStr = strtoupper($vars[2]);break;
                case 'LANG':       $parseStr = 'L("'.$vars[2].'")';break;
                case 'CONFIG':    $parseStr = 'C("'.$vars[2].'")';break;
            }
        }else if(count($vars)==2){
            switch($vars[1]){
                case 'NOW':       $parseStr = "date('Y-m-d g:i a',time())";break;
                case 'VERSION':  $parseStr = 'THINK_VERSION';break;
                case 'TEMPLATE':$parseStr = 'C("TEMPLATE_NAME")';break;
                case 'LDELIM':    $parseStr = 'C("TMPL_L_DELIM")';break;
                case 'RDELIM':    $parseStr = 'C("TMPL_R_DELIM")';break;
                default:  if(defined($vars[1])) $parseStr = $vars[1];
            }
        }
        return $parseStr;
    }

    // ��ȡ��ǩ����
    public function getTags(){
        return $this->tags;
    }
}