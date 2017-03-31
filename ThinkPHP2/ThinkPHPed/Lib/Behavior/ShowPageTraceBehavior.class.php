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
// $Id: ShowPageTraceBehavior.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ???????? ???Trace??????
 +------------------------------------------------------------------------------
 */
class ShowPageTraceBehavior extends Behavior {
    // ???????????
    protected $options   =  array(
        'SHOW_PAGE_TRACE'        => false,   // ??????Trace???
    );

    // ???????????????????run
    public function run(&$params){
        if(C('SHOW_PAGE_TRACE')) {
            echo $this->showTrace();
        }
    }

    /**
     +----------------------------------------------------------
     * ??????Trace???
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     */
    private function showTrace() {
         // ???????????
        $log  =   Log::$log;
        $files =  get_included_files();
        $trace   =  array(
            '???????'=>  date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']),
            '??????'=>  __SELF__,
            '????§¿??'=>  $_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'],
            '???????'=>  $this->showTime(),
            '??ID'    =>  session_id(),
            '??????'=>  count($log)?count($log).'?????<br/>'.implode('<br/>',$log):'????????',
            '???????'=>  count($files).str_replace("\n",'<br/>',substr(substr(print_r($files,true),7),0,-2)),
            );

        // ???????????Trace???
        $traceFile  =   CONF_PATH.'trace.php';
        if(is_file($traceFile)) {
            // ?????? return array('??????'=>$_SERVER['PHP_SELF'],'???§¿??'=>$_SERVER['SERVER_PROTOCOL'],...);
            $trace   =  array_merge(include $traceFile,$trace);
        }
        // ????trace???
        trace($trace);
        // ????Trace??????
        ob_start();
        include C('TMPL_TRACE_FILE')?C('TMPL_TRACE_FILE'):THINK_PATH.'Tpl/page_trace.tpl';
        return ob_get_clean();
    }

    /**
     +----------------------------------------------------------
     * ??????????????????????????????????????
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function showTime() {
        // ??????????
        G('beginTime',$GLOBALS['_beginTime']);
        G('viewEndTime');
        $showTime   =   'Process: '.G('beginTime','viewEndTime').'s ';
        // ?????????????
        $showTime .= '( Load:'.G('beginTime','loadTime').'s Init:'.G('loadTime','initTime').'s Exec:'.G('initTime','viewStartTime').'s Template:'.G('viewStartTime','viewEndTime').'s )';
        // ???????????????
        if(class_exists('Db',false) ) {
            $showTime .= ' | DB :'.N('db_query').' queries '.N('db_write').' writes ';
        }
        // ????????§Õ????
        if( class_exists('Cache',false)) {
            $showTime .= ' | Cache :'.N('cache_read').' gets '.N('cache_write').' writes ';
        }
        // ?????žD??
        if(MEMORY_LIMIT_ON ) {
            $showTime .= ' | UseMem:'. number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024).' kb';
        }
        // ????????????
        $showTime .= ' | LoadFile:'.count(get_included_files());
        // ?????????????? ????Žï??,???¨²???
        $fun  =  get_defined_functions();
        $showTime .= ' | CallFun:'.count($fun['user']).','.count($fun['internal']);
        return $showTime;
    }
}