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
// $Id: Log.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ?????????
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Log.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class Log {

    // ??????? ??????¡ê???????
    const EMERG   = 'EMERG';  // ???????: ????????????????
    const ALERT    = 'ALERT';  // ?????????: ???????????????
    const CRIT      = 'CRIT';  // ????????: ????????????????????24§³????????????25§³?????
    const ERR       = 'ERR';  // ??????: ????????
    const WARN    = 'WARN';  // ?????????: ???????????????
    const NOTICE  = 'NOTIC';  // ??: ??????????§Ö?????????????????
    const INFO     = 'INFO';  // ???: ??????????
    const DEBUG   = 'DEBUG';  // ????: ???????
    const SQL       = 'SQL';  // SQL??SQL??? ??????????????????§¹

    // ?????????
    const SYSTEM = 0;
    const MAIL      = 1;
    const FILE       = 3;
    const SAPI      = 4;

    // ??????
    static $log =   array();

    // ??????
    static $format =  '[ c ]';

    /**
     +----------------------------------------------------------
     * ?????? ????????¦Ä??????????
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $message ??????
     * @param string $level  ???????
     * @param boolean $record  ????????
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
     * ???????
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param integer $type ?????????
     * @param string $destination  §Õ?????
     * @param string $extra ???????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static function save($type='',$destination='',$extra='') {
        $type = $type?$type:C('LOG_TYPE');
        if(self::FILE == $type) { // ???????????????
            if(empty($destination))
                $destination = LOG_PATH.date('y_m_d').".log";
            //???????????§³???????????§³??????????????????
            if(is_file($destination) && floor(C('LOG_FILE_SIZE')) <= filesize($destination) )
                  rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        }else{
            $destination   =   $destination?$destination:C('LOG_DEST');
            $extra   =  $extra?$extra:C('LOG_EXTRA');
        }
        error_log(implode("",self::$log), $type,$destination ,$extra);
        // ???????????????
        self::$log = array();
        //clearstatcache();
    }

    /**
     +----------------------------------------------------------
     * ??????§Õ??
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $message ??????
     * @param string $level  ???????
     * @param integer $type ?????????
     * @param string $destination  §Õ?????
     * @param string $extra ???????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    static function write($message,$level=self::ERR,$type='',$destination='',$extra='') {
        $now = date(self::$format);
        $type = $type?$type:C('LOG_TYPE');
        if(self::FILE == $type) { // ????????????
            if(empty($destination))
                $destination = LOG_PATH.date('y_m_d').".log";
            //???????????§³???????????§³??????????????????
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