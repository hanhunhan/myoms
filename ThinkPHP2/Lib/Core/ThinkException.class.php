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
// $Id: ThinkException.class.php 2791 2012-02-29 10:08:57Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHPϵͳ�쳣����
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Exception
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: ThinkException.class.php 2791 2012-02-29 10:08:57Z liu21st $
 +------------------------------------------------------------------------------
 */
class ThinkException extends Exception {

    /**
     +----------------------------------------------------------
     * �쳣����
     +----------------------------------------------------------
     * @var string
     * @access private
     +----------------------------------------------------------
     */
    private $type;

    // �Ƿ���ڶ��������Ϣ
    private $extra;

    /**
     +----------------------------------------------------------
     * �ܹ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $message  �쳣��Ϣ
     +----------------------------------------------------------
     */
    public function __construct($message,$code=0,$extra=false) {
        parent::__construct($message,$code);
        $this->type = get_class($this);
        $this->extra = $extra;
    }

    /**
     +----------------------------------------------------------
     * �쳣��� �����쳣�������ͨ��__toString�����������
     * ÿ���쳣����д��ϵͳ��־
     * �÷������Ա���������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function __toString() {
        $trace = $this->getTrace();
        if($this->extra)
            // ͨ��throw_exception�׳����쳣Ҫȥ������ĵ�����Ϣ
            array_shift($trace);
        $this->class = $trace[0]['class'];
        $this->function = $trace[0]['function'];
        $this->file = $trace[0]['file'];
        $this->line = $trace[0]['line'];
        $file   =   file($this->file);
        $traceInfo='';
        $time = date('y-m-d H:i:m');
        foreach($trace as $t) {
            $traceInfo .= '['.$time.'] '.$t['file'].' ('.$t['line'].') ';
            $traceInfo .= $t['class'].$t['type'].$t['function'].'(';
            $traceInfo .= implode(', ', $t['args']);
            $traceInfo .=")\n";
        }
        $error['message']   = $this->message;
        $error['type']      = $this->type;
        $error['detail']    = L('_MODULE_').'['.MODULE_NAME.'] '.L('_ACTION_').'['.ACTION_NAME.']'."\n";
        $error['detail']   .=   ($this->line-2).': '.$file[$this->line-3];
        $error['detail']   .=   ($this->line-1).': '.$file[$this->line-2];
        $error['detail']   .=   '<font color="#FF6600" >'.($this->line).': <strong>'.$file[$this->line-1].'</strong></font>';
        $error['detail']   .=   ($this->line+1).': '.$file[$this->line];
        $error['detail']   .=   ($this->line+2).': '.$file[$this->line+1];
        $error['class']     =   $this->class;
        $error['function']  =   $this->function;
        $error['file']      = $this->file;
        $error['line']      = $this->line;
        $error['trace']     = $traceInfo;

        // ��¼ Exception ��־
        if(C('LOG_EXCEPTION_RECORD')) {
            Log::Write('('.$this->type.') '.$this->message);
        }
        return $error ;
    }

}