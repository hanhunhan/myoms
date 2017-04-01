<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: CacheApachenote.class.php 2504 2011-12-28 07:35:29Z liu21st $

/**
 +------------------------------------------------------------------------------
 * Apachenote������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: CacheApachenote.class.php 2504 2011-12-28 07:35:29Z liu21st $
 +------------------------------------------------------------------------------
 */
class CacheApachenote extends Cache {

    /**
     +----------------------------------------------------------
     * �ܹ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($options='') {
        if(empty($options)){
            $options = array(
                'host' => '127.0.0.1',
                'port' => 1042,
                'timeout' => 10,
                'length'   =>0
            );
        }
        $this->handler = null;
        $this->open();
        $this->options = $options;
    }

    /**
     +----------------------------------------------------------
     * �Ƿ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function isConnected() {
        return $this->connected;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ���������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
     public function get($name) {
         $this->open();
         $s = 'F' . pack('N', strlen($name)) . $name;
         fwrite($this->handler, $s);

         for ($data = ''; !feof($this->handler);) {
             $data .= fread($this->handler, 4096);
         }
        N('cache_read',1);
         $this->close();
         return $data === '' ? '' : unserialize($data);
     }

    /**
     +----------------------------------------------------------
     * д�뻺��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ���������
     * @param mixed $value  �洢����
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function set($name, $value) {
        N('cache_write',1);
        $this->open();
        $value = serialize($value);
        $s = 'S' . pack('NN', strlen($name), strlen($value)) . $name . $value;

        fwrite($this->handler, $s);
        $ret = fgets($this->handler);
        $this->close();
        if($ret === "OK\n") {
            if($this->options['length']>0) {
                // ��¼�������
                $this->queue($name);
            }
            return true;
        }
        return false;
    }

    /**
     +----------------------------------------------------------
     * ɾ������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ���������
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
     public function rm($name) {
         $this->open();
         $s = 'D' . pack('N', strlen($name)) . $name;
         fwrite($this->handler, $s);
         $ret = fgets($this->handler);
         $this->close();
         return $ret === "OK\n";
     }

    /**
     +----------------------------------------------------------
     * �رջ���
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     */
     private function close() {
         fclose($this->handler);
         $this->handler = false;
     }

    /**
     +----------------------------------------------------------
     * �򿪻���
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     */
     private function open() {
         if (!is_resource($this->handler)) {
             $this->handler = fsockopen($this->options['host'], $this->options['port'], $_, $_, $this->options['timeout']);
             $this->connected = is_resource($this->handler);
         }
     }

}