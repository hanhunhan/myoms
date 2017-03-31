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
// $Id: Log.class.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $

/**
 +------------------------------------------------------------------------------
 * ��־������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Log.class.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $
 +------------------------------------------------------------------------------
 */
class Log {

    // ��־���� ���ϵ��£��ɵ͵���
    const EMERG   = 'EMERG';  // ���ش���: ����ϵͳ�����޷�ʹ��
    const ALERT    = 'ALERT';  // �����Դ���: ���뱻�����޸ĵĴ���
    const CRIT      = 'CRIT';  // �ٽ�ֵ����: �����ٽ�ֵ�Ĵ�������һ��24Сʱ�����������25Сʱ����
    const ERR       = 'ERR';  // һ�����: һ���Դ���
    const WARN    = 'WARN';  // �����Դ���: ��Ҫ��������Ĵ���
    const NOTICE  = 'NOTIC';  // ֪ͨ: ����������е��ǻ����������Ĵ���
    const INFO     = 'INFO';  // ��Ϣ: ���������Ϣ
    const DEBUG   = 'DEBUG';  // ����: ������Ϣ
    const SQL       = 'SQL';  // SQL��SQL��� ע��ֻ�ڵ���ģʽ����ʱ��Ч

    // ��־��¼��ʽ
    const SYSTEM = 0;
    const MAIL      = 1;
    const FILE       = 3;
    const SAPI      = 4;

    // ��־��Ϣ
    static $log =   array();

    // ���ڸ�ʽ
    static $format =  '[ c ]';

    /**
     +----------------------------------------------------------
     * ��¼��־ ���һ����δ�����õļ���
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $message ��־��Ϣ
     * @param string $level  ��־����
     * @param boolean $record  �Ƿ�ǿ�Ƽ�¼
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static function record($message,$level=self::ERR,$record=false) {
        if($record || strpos(C('LOG_LEVEL'),$level)) {
            //[sae] �²���¼ʱ�� sae_debug���¼
            self::$log[] = '###'.$_SERVER['REQUEST_URI'] . " | {$level}: {$message}###";
        }
    }

    /**
     +----------------------------------------------------------
     * ��־����
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param integer $type ��־��¼��ʽ
     * @param string $destination  д��Ŀ��
     * @param string $extra �������
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    //[sae]������־
    static function save($type='',$destination='',$extra='') {
        self::sae_set_display_errors(false);
        foreach (self::$log as $log)
            sae_debug($log);
        // ����������־����
        self::$log = array();
        self::sae_set_display_errors(true);
        //clearstatcache();
    }

    /**
     +----------------------------------------------------------
     * ��־ֱ��д��
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $message ��־��Ϣ
     * @param string $level  ��־����
     * @param integer $type ��־��¼��ʽ
     * @param string $destination  д��Ŀ��
     * @param string $extra �������
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    //[sae]��д����־��
    static function write($message,$level=self::ERR,$type='',$destination='',$extra='') {
        self::sae_set_display_errors(false);
        sae_debug('###'.$_SERVER['REQUEST_URI'] . " | {$level}: {$message}###");
        self::sae_set_display_errors(true);
        //clearstatcache();
    }
    //[sae] ���Ӵ�����Ϣ��ʾ���ƣ��ֲ�SAEƽ̨�ֶε�sae_set_display_errors�Ĳ��㡣
    static function sae_set_display_errors($bool){
        static $is_debug=null;
        if (is_null($is_debug)) {
            preg_replace('@(\w+)\=([^;]*)@e', '$appSettings[\'\\1\']="\\2";', $_SERVER['HTTP_APPCOOKIE']);
            $is_debug = in_array($_SERVER['HTTP_APPVERSION'], explode(',', $appSettings['debug'])) ? true : false;
        }
        if($is_debug)
            sae_set_display_errors ($bool);
    }
}