<?php

/**
 * +---------------------------------------------------
 * | ��Memcache�����ķ�װ�������ļ�����
 * +---------------------------------------------------
 * @author luofei614<www.3g4k.com>
 */
if (!class_exists('SaeMC')) {

//[sae]��Memcache�����ķ�װ�����뻺�棬ģ�滺�����Memcache�У���wrappers����ʽ��
    class SaeMC {

        static public $handler;
        static private $current_include_file = null;
        static private $contents = array();
        static private $filemtimes = array();

        //�����ļ�����
        static public function set($filename, $content) {
            $time=time();
            self::$handler->set($_SERVER['HTTP_APPVERSION'] . '/' . $filename, $time . $content, MEMCACHE_COMPRESSED, 0);
            self::$contents[$filename]=$content;
            self::$filemtimes[$filename]=$time;
        }

        //�����ļ�
        static public function include_file($_filename,$_vars=null) {
            self::$current_include_file = 'saemc://' . $_SERVER['HTTP_APPVERSION'] . '/' . $_filename;
            $_content = isset(self::$contents[$_filename]) ? self::$contents[$_filename] : self::getValue($_filename, 'content');
            if(!is_null($_vars))
                extract($_vars, EXTR_OVERWRITE);
   
            if (!$_content)
                exit('<br /><b>SAE_Parse_error</b>: failed to open stream: No such file ' . self::$current_include_file);
            if (@(eval(' ?>' . $_content)) === false)
                self::error();
            self::$current_include_file = null;
            unset(self::$contents[$_filename]); //�ͷ��ڴ�
        }

        static private function getValue($filename, $type='mtime') {
            $content = self::$handler->get($_SERVER['HTTP_APPVERSION'] . '/' . $filename);
            if (!$content)
                return false;
            $ret = array(
                'mtime' => substr($content, 0, 10),
                'content' => substr($content, 10)
            );
            self::$contents[$filename] = $ret['content'];
            self::$filemtimes[$filename] = $ret['mtime'];
            return $ret[$type];
        }

        //����ļ��޸�ʱ��
        static public function filemtime($filename) {
            if (!isset(self::$filemtimes[$filename]))
                return self::getValue($filename, 'mtime');
            return self::$filemtimes[$filename];
        }

        //ɾ���ļ�
        static public function unlink($filename) {
            if (isset(self::$contents[$filename]))
                unset(self::$contents[$filename]);
            if (isset(self::$filemtimes[$filename]))
                unset(self::$filemtimes[$filename]);
            return self::$handler->delete($_SERVER['HTTP_APPVERSION'] . '/' . $filename);
        }

        static public function file_exists($filename) {
            return self::filemtime($filename) === false ? false : true;
        }

        static function error() {
            $error = error_get_last();
            if (!is_null($error)) {
                $file = strpos($error['file'], 'eval()') !== false ? self::$current_include_file : $error['file'];
                exit("<br /><b>SAE_error</b>:  {$error['message']} in <b>" . $file . "</b> on line <b>{$error['line']}</b><br />");
            }
        }

    }

    register_shutdown_function(array('SaeMC', 'error'));
    //[sae] ��ʼ��memcache
    if (!(SaeMC::$handler = @(memcache_init()))) {
        header('Content-Type:text/html; charset=utf-8');
        exit('<div style=\'font-weight:bold;float:left;width:430px;text-align:center;border:1px solid silver;background:#E8EFFF;padding:8px;color:red;font-size:14px;font-family:Tahoma\'>����Memcache��û�г�ʼ�������¼SAEƽ̨���г�ʼ��~</div>');
    }
}