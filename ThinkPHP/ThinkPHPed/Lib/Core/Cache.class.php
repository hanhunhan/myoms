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
// $Id: Cache.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ?????????
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Util
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Cache.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class Cache {

    /**
     +----------------------------------------------------------
     * ???????
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $connected  ;

    /**
     +----------------------------------------------------------
     * ???????
     +----------------------------------------------------------
     * @var string
     * @access protected
     +----------------------------------------------------------
     */
    protected $handler    ;

    /**
     +----------------------------------------------------------
     * ???????????
     +----------------------------------------------------------
     * @var integer
     * @access protected
     +----------------------------------------------------------
     */
    protected $options = array();

    /**
     +----------------------------------------------------------
     * ???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $type ????????
     * @param array $options  ????????
     +----------------------------------------------------------
     * @return object
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect($type='',$options=array()) {
        if(empty($type))  $type = C('DATA_CACHE_TYPE');
        $type = strtolower(trim($type));
        $class = 'Cache'.ucwords($type);
        if(is_file(CORE_PATH.'Driver/Cache/'.$class.'.class.php')) {
            // ????????
            $path = CORE_PATH;
        }else{ // ???????
            $path = EXTEND_PATH;
        }
        if(require_cache($path.'Driver/Cache/'.$class.'.class.php'))
            $cache = new $class($options);
        else
            throw_exception(L('_CACHE_TYPE_INVALID_').':'.$type);
        return $cache;
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __set($name,$value) {
        return $this->set($name,$value);
    }

    public function __unset($name) {
        $this->rm($name);
    }
    public function setOptions($name,$value) {
        $this->options[$name]   =   $value;
    }

    public function getOptions($name) {
        return $this->options[$name];
    }

    /**
     +----------------------------------------------------------
     * ???????????
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    static function getInstance() {
       $param = func_get_args();
        return get_instance_of(__CLASS__,'connect',$param);
    }

    // ???§Ý???
    protected function queue($key) {
        static $_handler = array(
            'file'=>array('F','F'),
            'xcache'=>array('xcache_get','xcache_set'),
            'apc'=>array('apc_fetch','apc_store'),
        );
        $queue  =  isset($this->options['queue'])?$this->options['queue']:'file';
        $fun  =  $_handler[$queue];
        $value   =  $fun[0]('think_queue');
        if(!$value) {
            $value   =  array();
        }
        // ????
        array_push($value,$key);
        if(count($value) > $this->options['length']) {
            // ????
            $key =  array_shift($value);
            // ???????
            $this->rm($key);
        }
        return $fun[1]('think_queue',$value);
    }
}