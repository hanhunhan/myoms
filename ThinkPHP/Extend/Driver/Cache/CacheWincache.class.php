<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: CacheWincache.class.php 2728 2012-02-12 04:12:51Z liu21st $

/**
 +----------------------------
 * WinCache ����������
 +----------------------------
 */
class CacheWincache extends Cache {

    /**
     +----------------------------------------------------------
     * �ܹ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($options='') {
        if ( !function_exists('wincache_ucache_info') ) {
            throw_exception(L('_NOT_SUPPERT_').':WinCache');
        }
        if(!empty($options)) {
            $this->options =  $options;
        }
        $this->options['expire'] = isset($options['expire'])?$options['expire']:C('DATA_CACHE_TIME');
        $this->options['length']  =  isset($options['length'])?$options['length']:0;
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
        N('cache_read',1);
        return wincache_ucache_exists($name)? wincache_ucache_get($name) : false;
    }

    /**
     +----------------------------------------------------------
     * д�뻺��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ���������
     * @param mixed $value  �洢����
     * @param integer $expire  ��Чʱ�䣨�룩
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function set($name, $value,$expire=null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        if(wincache_ucache_set($name, $value, $expire)) {
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
        return wincache_ucache_delete($name);
    }

}