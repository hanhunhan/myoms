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
 * ϵͳ��Ϊ��չ ҳ��Trace��ʾ���
 +------------------------------------------------------------------------------
 */
class ShowPageTraceBehavior extends Behavior {
    // ��Ϊ��������
    protected $options   =  array(
        'SHOW_PAGE_TRACE'        => false,   // ��ʾҳ��Trace��Ϣ
    );

    // ��Ϊ��չ��ִ����ڱ�����run
    public function run(&$params){
        if(C('SHOW_PAGE_TRACE')) {
            echo $this->showTrace();
        }
    }

    /**
     +----------------------------------------------------------
     * ��ʾҳ��Trace��Ϣ
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     */
    private function showTrace() {
         // ϵͳĬ����ʾ��Ϣ
        $log  =   Log::$log;
        $files =  get_included_files();
        $trace   =  array(
            '����ʱ��'=>  date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']),
            '��ǰҳ��'=>  __SELF__,
            '����Э��'=>  $_SERVER['SERVER_PROTOCOL'].' '.$_SERVER['REQUEST_METHOD'],
            '������Ϣ'=>  $this->showTime(),
            '�ỰID'    =>  session_id(),
            '��־��¼'=>  count($log)?count($log).'����־<br/>'.implode('<br/>',$log):'����־��¼',
            '�����ļ�'=>  count($files).str_replace("\n",'<br/>',substr(substr(print_r($files,true),7),0,-2)),
            );

        // ��ȡ��Ŀ�����Trace�ļ�
        $traceFile  =   CONF_PATH.'trace.php';
        if(is_file($traceFile)) {
            // �����ʽ return array('��ǰҳ��'=>$_SERVER['PHP_SELF'],'ͨ��Э��'=>$_SERVER['SERVER_PROTOCOL'],...);
            $trace   =  array_merge(include $traceFile,$trace);
        }
        // ����trace��Ϣ
        trace($trace);
        // ����Traceҳ��ģ��
        ob_start();
        include C('TMPL_TRACE_FILE')?C('TMPL_TRACE_FILE'):THINK_PATH.'Tpl/page_trace.tpl';
        return ob_get_clean();
    }

    /**
     +----------------------------------------------------------
     * ��ʾ����ʱ�䡢���ݿ����������������ڴ�ʹ����Ϣ
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function showTime() {
        // ��ʾ����ʱ��
        G('beginTime',$GLOBALS['_beginTime']);
        G('viewEndTime');
        $showTime   =   'Process: '.G('beginTime','viewEndTime').'s ';
        // ��ʾ��ϸ����ʱ��
        $showTime .= '( Load:'.G('beginTime','loadTime').'s Init:'.G('loadTime','initTime').'s Exec:'.G('initTime','viewStartTime').'s Template:'.G('viewStartTime','viewEndTime').'s )';
        // ��ʾ���ݿ��������
        if(class_exists('Db',false) ) {
            $showTime .= ' | DB :'.N('db_query').' queries '.N('db_write').' writes ';
        }
        // ��ʾ�����д����
        if( class_exists('Cache',false)) {
            $showTime .= ' | Cache :'.N('cache_read').' gets '.N('cache_write').' writes ';
        }
        // ��ʾ�ڴ濪��
        if(MEMORY_LIMIT_ON ) {
            $showTime .= ' | UseMem:'. number_format((memory_get_usage() - $GLOBALS['_startUseMems'])/1024).' kb';
        }
        // ��ʾ�ļ�������
        $showTime .= ' | LoadFile:'.count(get_included_files());
        // ��ʾ�������ô��� �Զ��庯��,���ú���
        $fun  =  get_defined_functions();
        $showTime .= ' | CallFun:'.count($fun['user']).','.count($fun['internal']);
        return $showTime;
    }
}