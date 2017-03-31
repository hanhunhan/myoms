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
// $Id: CacheDb.class.php 2654 2012-01-23 07:44:40Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ���ݿ����ͻ�����
     CREATE TABLE think_cache (
       cachekey varchar(255) NOT NULL,
       expire int(11) NOT NULL,
       data blob,
       datacrc int(32),
       UNIQUE KEY `cachekey` (`cachekey`)
     );
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: CacheDb.class.php 2654 2012-01-23 07:44:40Z liu21st $
 +------------------------------------------------------------------------------
 */
class CacheDb extends Cache {

    /**
     +----------------------------------------------------------
     * �������ݿ���� �������ݿⷽʽ��Ч
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    private $db     ;

    /**
     +----------------------------------------------------------
     * �ܹ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($options='') {
        if(empty($options)){
            $options= array (
                'db'        => C('DB_NAME'),
                'table'     => C('DATA_CACHE_TABLE'),
                'expire'    => C('DATA_CACHE_TIME'),
                'length'    => 0,
            );
        }
        $this->options = $options;
        import('Db');
        $this->db  = DB::getInstance();
        $this->connected = is_resource($this->db);
    }

    /**
     +----------------------------------------------------------
     * �Ƿ�����
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    private function isConnected() {
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
        $name  =  addslashes($name);
        N('cache_read',1);
        $result  =  $this->db->query('SELECT `data`,`datacrc` FROM `'.$this->options['table'].'` WHERE `cachekey`=\''.$name.'\' AND (`expire` =0 OR `expire`>'.time().') LIMIT 0,1');
        if(false !== $result ) {
            $result   =  $result[0];
            if(C('DATA_CACHE_CHECK')) {//��������У��
                if($result['datacrc'] != md5($result['data'])) {//У�����
                    return false;
                }
            }
            $content   =  $result['data'];
            if(C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
                //��������ѹ��
                $content   =   gzuncompress($content);
            }
            $content    =   unserialize($content);
            return $content;
        }
        else {
            return false;
        }
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
        $data   =   serialize($value);
        $name  =  addslashes($name);
        N('cache_write',1);
        if( C('DATA_CACHE_COMPRESS') && function_exists('gzcompress')) {
            //����ѹ��
            $data   =   gzcompress($data,3);
        }
        if(C('DATA_CACHE_CHECK')) {//��������У��
            $crc  =  md5($data);
        }else {
            $crc  =  '';
        }
        $expire =  !empty($expire)? $expire : $this->options['expire'];
        $expire	=	($expire==0)?0: (time()+$expire) ;//������Ч��Ϊ0��ʾ���û���
        $result  =  $this->db->query('select `cachekey` from `'.$this->options['table'].'` where `cachekey`=\''.$name.'\' limit 0,1');
        if(!empty($result) ) {
        	//���¼�¼
            $result  =  $this->db->execute('UPDATE '.$this->options['table'].' SET data=\''.$data.'\' ,datacrc=\''.$crc.'\',expire='.$expire.' WHERE `cachekey`=\''.$name.'\'');
        }else {
        	//������¼
             $result  =  $this->db->execute('INSERT INTO '.$this->options['table'].' (`cachekey`,`data`,`datacrc`,`expire`) VALUES (\''.$name.'\',\''.$data.'\',\''.$crc.'\','.$expire.')');
        }
        if($result) {
            if($this->options['length']>0) {
                // ��¼�������
                $this->queue($name);
            }
            return true;
        }else {
            return false;
        }
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
        $name  =  addslashes($name);
        return $this->db->execute('DELETE FROM `'.$this->options['table'].'` WHERE `cachekey`=\''.$name.'\'');
    }

    /**
     +----------------------------------------------------------
     * �������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public function clear() {
        return $this->db->execute('TRUNCATE TABLE `'.$this->options['table'].'`');
    }

}