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
// $Id: DbMysql.class.php 2706 2012-02-04 03:39:48Z liu21st $

define('CLIENT_MULTI_RESULTS', 131072);
/**
 +------------------------------------------------------------------------------
 * Mysql���ݿ�������
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Db
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: DbMysql.class.php 2706 2012-02-04 03:39:48Z liu21st $
 +------------------------------------------------------------------------------
 */
class DbMysql extends Db{

    /**
     +----------------------------------------------------------
     * �ܹ����� ��ȡ���ݿ�������Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $config ���ݿ���������
     +----------------------------------------------------------
     */
    public function __construct($config=''){
        if ( !extension_loaded('mysql') ) {
            throw_exception(L('_NOT_SUPPERT_').':mysql');
        }
        if(!empty($config)) {
            $this->config   =   $config;
            if(empty($this->config['params'])) {
                $this->config['params'] =   array();
            }
        }
    }

    /**
     +----------------------------------------------------------
     * �������ݿⷽ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function connect($config='',$linkNum=0,$force=false) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            // �������˿ںŵ�socket�������
            $host = $config['hostname'].($config['hostport']?":{$config['hostport']}":'');
            // �Ƿ�����
            $pconnect   = !empty($config['params']['persist'])? $config['params']['persist']:$this->pconnect;
            if($pconnect) {
                $this->linkID[$linkNum] = mysql_pconnect( $host, $config['username'], $config['password'],CLIENT_MULTI_RESULTS);
            }else{
                $this->linkID[$linkNum] = mysql_connect( $host, $config['username'], $config['password'],true,CLIENT_MULTI_RESULTS);
            }
            if ( !$this->linkID[$linkNum] || (!empty($config['database']) && !mysql_select_db($config['database'], $this->linkID[$linkNum])) ) {
                throw_exception(mysql_error());
            }
            $dbVersion = mysql_get_server_info($this->linkID[$linkNum]);
            if ($dbVersion >= "4.1") {
                //ʹ��UTF8��ȡ���ݿ� ��Ҫmysql 4.1.0����֧��
                mysql_query("SET NAMES '".C('DB_CHARSET')."'", $this->linkID[$linkNum]);
            }
            //���� sql_model
            if($dbVersion >'5.0.1'){
                mysql_query("SET sql_mode=''",$this->linkID[$linkNum]);
            }
            // ������ӳɹ�
            $this->connected    =   true;
            // ע�����ݿ�����������Ϣ
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        return $this->linkID[$linkNum];
    }

    /**
     +----------------------------------------------------------
     * �ͷŲ�ѯ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function free() {
        mysql_free_result($this->queryID);
        $this->queryID = null;
    }

    /**
     +----------------------------------------------------------
     * ִ�в�ѯ �������ݼ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sqlָ��
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function query($str) {
        if(0===stripos($str, 'call')){ // �洢���̲�ѯ֧��
            $this->close();
        }
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( $this->queryID ) {    $this->free();    }
        N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->queryID = mysql_query($str, $this->_linkID);
        $this->debug();
        if ( false === $this->queryID ) {
            $this->error();
            return false;
        } else {
            $this->numRows = mysql_num_rows($this->queryID);
            return $this->getAll();
        }
    }

    /**
     +----------------------------------------------------------
     * ִ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  sqlָ��
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function execute($str) {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( $this->queryID ) {    $this->free();    }
        N('db_write',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $result =   mysql_query($str, $this->_linkID) ;
        $this->debug();
        if ( false === $result) {
            $this->error();
            return false;
        } else {
            $this->numRows = mysql_affected_rows($this->_linkID);
            $this->lastInsID = mysql_insert_id($this->_linkID);
            return $this->numRows;
        }
    }

    /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function startTrans() {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        //����rollback ֧��
        if ($this->transTimes == 0) {
            mysql_query('START TRANSACTION', $this->_linkID);
        }
        $this->transTimes++;
        return ;
    }

    /**
     +----------------------------------------------------------
     * ���ڷ��Զ��ύ״̬����Ĳ�ѯ�ύ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = mysql_query('COMMIT', $this->_linkID);
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ����ع�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = mysql_query('ROLLBACK', $this->_linkID);
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ������еĲ�ѯ����
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    private function getAll() {
        //�������ݼ�
        $result = array();
        if($this->numRows >0) {
            while($row = mysql_fetch_assoc($this->queryID)){
                $result[]   =   $row;
            }
            mysql_data_seek($this->queryID,0);
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݱ���ֶ���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function getFields($tableName) {
        $result =   $this->query('SHOW COLUMNS FROM '.$this->parseKey($tableName));
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[$val['Field']] = array(
                    'name'    => $val['Field'],
                    'type'    => $val['Type'],
                    'notnull' => (bool) ($val['Null'] === ''), // not null is empty, null is yes
                    'default' => $val['Default'],
                    'primary' => (strtolower($val['Key']) == 'pri'),
                    'autoinc' => (strtolower($val['Extra']) == 'auto_increment'),
                );
            }
        }
        return $info;
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݿ�ı���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function getTables($dbName='') {
        if(!empty($dbName)) {
           $sql    = 'SHOW TABLES FROM '.$dbName;
        }else{
           $sql    = 'SHOW TABLES ';
        }
        $result =   $this->query($sql);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    /**
     +----------------------------------------------------------
     * �滻��¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ����
     * @param array $options �������ʽ
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function replace($data,$options=array()) {
        foreach ($data as $key=>$val){
            $value   =  $this->parseValue($val);
            if(is_scalar($value)) { // ���˷Ǳ�������
                $values[]   =  $value;
                $fields[]     =  $this->parseKey($key);
            }
        }
        $sql   =  'REPLACE INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        return $this->execute($sql);
    }

    /**
     +----------------------------------------------------------
     * �����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $datas ����
     * @param array $options �������ʽ
     * @param boolean $replace �Ƿ�replace
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function insertAll($datas,$options=array(),$replace=false) {
        if(!is_array($datas[0])) return false;
        $fields = array_keys($datas[0]);
        array_walk($fields, array($this, 'parseKey'));
        $values  =  array();
        foreach ($datas as $data){
            $value   =  array();
            foreach ($data as $key=>$val){
                $val   =  $this->parseValue($val);
                if(is_scalar($val)) { // ���˷Ǳ�������
                    $value[]   =  $val;
                }
            }
            $values[]    = '('.implode(',', $value).')';
        }
        $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES '.implode(',',$values);
        return $this->execute($sql);
    }

    /**
     +----------------------------------------------------------
     * �ر����ݿ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function close() {
        if ($this->_linkID){
            mysql_close($this->_linkID);
        }
        $this->_linkID = null;
    }

    /**
     +----------------------------------------------------------
     * ���ݿ������Ϣ
     * ����ʾ��ǰ��SQL���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function error() {
        $this->error = mysql_error($this->_linkID);
        if($this->debug && '' != $this->queryStr){
            $this->error .= "\n [ SQL��� ] : ".$this->queryStr;
        }
        return $this->error;
    }

    /**
     +----------------------------------------------------------
     * SQLָ�ȫ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $str  SQL�ַ���
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function escapeString($str) {
        if($this->_linkID) {
            return mysql_real_escape_string($str,$this->_linkID);
        }else{
            return mysql_escape_string($str);
        }
    }

    /**
     +----------------------------------------------------------
     * �ֶκͱ����������`
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseKey(&$key) {
        $key   =  trim($key);
        if( false !== strpos($key,' ') || false !== strpos($key,',') || false !== strpos($key,'*') ||  false !== strpos($key,'(') || false !== strpos($key,'.') || false !== strpos($key,'`')) {
            //�������* ���� ʹ����sql���� ��������
        }else{
            $key = '`'.$key.'`';
        }
        return $key;
    }
}