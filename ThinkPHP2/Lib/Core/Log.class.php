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
// $Id: Log.class.php 2791 2012-02-29 10:08:57Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ��־������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Log.class.php 2791 2012-02-29 10:08:57Z liu21st $
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
            $now = date(self::$format);
            self::$log[] =   "{$now} ".$_SERVER['REQUEST_URI']." | {$level}: {$message}\r\n";
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
    static function save($type='',$destination='',$extra='') {
        $type = $type?$type:C('LOG_TYPE');
        if(self::FILE == $type) { // �ļ���ʽ��¼��־��Ϣ
            if(empty($destination))
                $destination = LOG_PATH.date('y_m_d').'.log';
            //�����־�ļ���С���������ô�С�򱸷���־�ļ���������
            if(is_file($destination) && floor(C('LOG_FILE_SIZE')) <= filesize($destination) )
                  rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        }else{
            $destination   =   $destination?$destination:C('LOG_DEST');
            $extra   =  $extra?$extra:C('LOG_EXTRA');
        }
        error_log(implode('',self::$log), $type,$destination ,$extra);
        // ����������־����
        self::$log = array();
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
    static function write($message,$level=self::ERR,$type='',$destination='',$extra='') {
        $now = date(self::$format);
        $type = $type?$type:C('LOG_TYPE');
        if(self::FILE == $type) { // �ļ���ʽ��¼��־
            if(empty($destination))
                $destination = LOG_PATH.date('y_m_d').'.log';
            //�����־�ļ���С���������ô�С�򱸷���־�ļ���������
            if(is_file($destination) && floor(C('LOG_FILE_SIZE')) <= filesize($destination) )
                  rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        }else{
            $destination   =   $destination?$destination:C('LOG_DEST');
            $extra   =  $extra?$extra:C('LOG_EXTRA');
        }
        error_log("{$now} ".$_SERVER['REQUEST_URI']." | {$level}: {$message}\r\n", $type,$destination,$extra );
        //clearstatcache();
    }
}