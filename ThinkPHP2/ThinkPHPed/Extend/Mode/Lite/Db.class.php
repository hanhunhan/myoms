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
// $Id: Db.class.php 2702 2012-02-02 12:35:01Z liu21st $

define('CLIENT_MULTI_RESULTS', 131072);
/**
 +------------------------------------------------------------------------------
 * ThinkPHP ����ģʽ���ݿ��м��ʵ���� ֻ֧��Mysql
 +------------------------------------------------------------------------------
 */
class Db {

    static private $_instance = null;
    // �Ƿ��Զ��ͷŲ�ѯ���
    protected $autoFree         = false;
    // �Ƿ���ʾ������Ϣ ������û�����־�ļ���¼sql���
    public $debug             = false;
    // �Ƿ�ʹ����������
    protected $pconnect         = false;
    // ��ǰSQLָ��
    protected $queryStr          = '';
    // ������ID
    protected $lastInsID         = null;
    // ���ػ���Ӱ���¼��
    protected $numRows        = 0;
    // �����ֶ���
    protected $numCols          = 0;
    // ����ָ����
    protected $transTimes      = 0;
    // ������Ϣ
    protected $error              = '';
    // ��ǰ����ID
    protected $linkID            =   null;
    // ��ǰ��ѯID
    protected $queryID          = null;
    // �Ƿ��Ѿ��������ݿ�
    protected $connected       = false;
    // ���ݿ����Ӳ�������
    protected $config             = '';
    // ���ݿ���ʽ
    protected $comparison      = array('eq'=>'=','neq'=>'!=','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE');
    // ��ѯ���ʽ
    protected $selectSql  =     'SELECT%DISTINCT% %FIELDS% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT%';
    /**
     +----------------------------------------------------------
     * �ܹ�����
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
        $this->config   =   $this->parseConfig($config);
        if(APP_DEBUG) {
            $this->debug  =  true;
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
    public function connect() {
        if(!$this->connected) {
            $config =   $this->config;
            // �������˿ںŵ�socket�������
            $host = $config['hostname'].($config['hostport']?":{$config['hostport']}":'');
            $pconnect   = !empty($config['params']['persist'])? $config['params']['persist']:$this->pconnect;
            if($pconnect) {
                $this->linkID = mysql_pconnect( $host, $config['username'], $config['password'],CLIENT_MULTI_RESULTS);
            }else{
                $this->linkID = mysql_connect( $host, $config['username'], $config['password'],true,CLIENT_MULTI_RESULTS);
            }
            if ( !$this->linkID || (!empty($config['database']) && !mysql_select_db($config['database'], $this->linkID)) ) {
                throw_exception(mysql_error());
            }
            $dbVersion = mysql_get_server_info($this->linkID);
            if ($dbVersion >= "4.1") {
                //ʹ��UTF8��ȡ���ݿ� ��Ҫmysql 4.1.0����֧��
                mysql_query("SET NAMES '".C('DB_CHARSET')."'", $this->linkID);
            }
            //���� sql_model
            if($dbVersion >'5.0.1'){
                mysql_query("SET sql_mode=''",$this->linkID);
            }
            // ������ӳɹ�
            $this->connected    =   true;
            // ע�����ݿ�����������Ϣ
            unset($this->config);
        }
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
        $this->queryID = 0;
    }

    /**
     +----------------------------------------------------------
     * ִ�в�ѯ ��Ҫ��� SELECT, SHOW ��ָ��
     * �������ݼ�
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
    public function query($str='') {
        $this->connect();
        if ( !$this->linkID ) return false;
        if ( $str != '' ) $this->queryStr = $str;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( $this->queryID ) {    $this->free();    }
        N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->queryID = mysql_query($this->queryStr, $this->linkID);
        $this->debug();
        if ( !$this->queryID ) {
            if ( $this->debug )
                throw_exception($this->error());
            else
                return false;
        } else {
            $this->numRows = mysql_num_rows($this->queryID);
            return $this->getAll();
        }
    }

    /**
     +----------------------------------------------------------
     * ִ����� ��� INSERT, UPDATE �Լ�DELETE
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
    public function execute($str='') {
        $this->connect();
        if ( !$this->linkID ) return false;
        if ( $str != '' ) $this->queryStr = $str;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( $this->queryID ) {    $this->free();    }
        N('db_write',1);
        $result =   mysql_query($this->queryStr, $this->linkID) ;
        $this->debug();
        if ( false === $result) {
            if ( $this->debug )
                throw_exception($this->error());
            else
                return false;
        } else {
            $this->numRows = mysql_affected_rows($this->linkID);
            $this->lastInsID = mysql_insert_id($this->linkID);
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
        $this->connect(true);
        if ( !$this->linkID ) return false;
        //����rollback ֧��
        if ($this->transTimes == 0) {
            mysql_query('START TRANSACTION', $this->linkID);
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
            $result = mysql_query('COMMIT', $this->linkID);
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
                return false;
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
            $result = mysql_query('ROLLBACK', $this->linkID);
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
                return false;
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ������еĲ�ѯ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getAll() {
        if ( !$this->queryID ) {
            throw_exception($this->error());
            return false;
        }
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
        $result =   $this->query('SHOW COLUMNS FROM '.$tableName);
        $info   =   array();
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
     * �ر����ݿ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function close() {
        if (!empty($this->queryID))
            mysql_free_result($this->queryID);
        if ($this->linkID && !mysql_close($this->linkID)){
            throw_exception($this->error());
        }
        $this->linkID = 0;
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
        $this->error = mysql_error($this->linkID);
        if($this->queryStr!=''){
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
        return mysql_escape_string($str);
    }

   /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __destruct() {
        // �ر�����
        $this->close();
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݿ���ʵ��
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @return mixed �������ݿ�������
     +----------------------------------------------------------
     */
    public static function getInstance($db_config='') {
		if ( self::$_instance==null ){
			self::$_instance = new Db($db_config);
		}
		return self::$_instance;
    }

    /**
     +----------------------------------------------------------
     * �������ݿ�������Ϣ��֧�������DSN
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param mixed $db_config ���ݿ�������Ϣ
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    private function parseConfig($db_config='') {
        if ( !empty($db_config) && is_string($db_config)) {
            // ���DSN�ַ�������н���
            $db_config = $this->parseDSN($db_config);
        }else if(empty($db_config)){
            // �������Ϊ�գ���ȡ�����ļ�����
            $db_config = array (
                'dbms'        =>   C('DB_TYPE'),
                'username'  =>   C('DB_USER'),
                'password'   =>   C('DB_PWD'),
                'hostname'  =>   C('DB_HOST'),
                'hostport'    =>   C('DB_PORT'),
                'database'   =>   C('DB_NAME'),
                'dsn'          =>   C('DB_DSN'),
                'params'     =>   C('DB_PARAMS'),
            );
        }
        return $db_config;
    }

    /**
     +----------------------------------------------------------
     * DSN����
     * ��ʽ�� mysql://username:passwd@localhost:3306/DbName
     +----------------------------------------------------------
     * @static
     * @access public
     +----------------------------------------------------------
     * @param string $dsnStr
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseDSN($dsnStr) {
        if( empty($dsnStr) ){return false;}
        $info = parse_url($dsnStr);
        if($info['scheme']){
            $dsn = array(
            'dbms'        => $info['scheme'],
            'username'  => isset($info['user']) ? $info['user'] : '',
            'password'   => isset($info['pass']) ? $info['pass'] : '',
            'hostname'  => isset($info['host']) ? $info['host'] : '',
            'hostport'    => isset($info['port']) ? $info['port'] : '',
            'database'   => isset($info['path']) ? substr($info['path'],1) : ''
            );
        }else {
            preg_match('/^(.*?)\:\/\/(.*?)\:(.*?)\@(.*?)\:([0-9]{1, 6})\/(.*?)$/',trim($dsnStr),$matches);
            $dsn = array (
            'dbms'        => $matches[1],
            'username'  => $matches[2],
            'password'   => $matches[3],
            'hostname'  => $matches[4],
            'hostport'    => $matches[5],
            'database'   => $matches[6]
            );
        }
        return $dsn;
     }

    /**
     +----------------------------------------------------------
     * ���ݿ���� ��¼��ǰSQL
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     */
    protected function debug() {
        // ��¼��������ʱ��
        if ( $this->debug ) {
            G('queryEndTime');
            Log::record($this->queryStr." [ RunTime:".G('queryStartTime','queryEndTime',6)."s ]",Log::SQL);
        }
    }

    /**
     +----------------------------------------------------------
     * ����������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseLock($lock=false) {
        if(!$lock) return '';
        if('ORACLE' == $this->dbType) {
            return ' FOR UPDATE NOWAIT ';
        }
        return ' FOR UPDATE ';
    }

    /**
     +----------------------------------------------------------
     * set����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseSet($data) {
        foreach ($data as $key=>$val){
            $value   =  $this->parseValue($val);
            if(is_scalar($value)) // ���˷Ǳ�������
                $set[]    = $this->parseKey($key).'='.$value;
        }
        return ' SET '.implode(',',$set);
    }

    /**
     +----------------------------------------------------------
     * value����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $value
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseValue($value) {
        if(is_string($value)) {
            $value = '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value   =  $this->escapeString($value[1]);
        }elseif(is_null($value)){
            $value   =  'null';
        }
        return $value;
    }

    /**
     +----------------------------------------------------------
     * field����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $fields
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseField($fields) {
        if(is_array($fields)) {
            // �������鷽ʽ���ֶ�����֧��
            // ֧�� 'field1'=>'field2' �������ֶα�������
            $array   =  array();
            foreach ($fields as $key=>$field){
                if(!is_numeric($key))
                    $array[] =  $this->parseKey($key).' AS '.$this->parseKey($field);
                else
                    $array[] =  $this->parseKey($field);
            }
            $fieldsStr = implode(',', $array);
        }elseif(is_string($fields) && !empty($fields)) {
            $fieldsStr = $this->parseKey($fields);
        }else{
            $fieldsStr = '*';
        }
        return $fieldsStr;
    }

    /**
     +----------------------------------------------------------
     * table����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $table
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseTable($tables) {
        if(is_string($tables))
            $tables  =  explode(',',$tables);
        array_walk($tables, array(&$this, 'parseKey'));
        return implode(',',$tables);
    }

    /**
     +----------------------------------------------------------
     * where����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $where
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseWhere($where) {
        $whereStr = '';
        if(is_string($where)) {
            // ֱ��ʹ���ַ�������
            $whereStr = $where;
        }else{ // ʹ�������������ʽ
            if(isset($where['_logic'])) {
                // �����߼�������� ���� OR XOR AND NOT
                $operate    =   ' '.strtoupper($where['_logic']).' ';
                unset($where['_logic']);
            }else{
                // Ĭ�Ͻ��� AND ����
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val){
                $whereStr .= "( ";
                if(0===strpos($key,'_')) {
                    // ���������������ʽ
                    $whereStr   .= $this->parseThinkWhere($key,$val);
                }else{
                    $key = $this->parseKey($key);
                    if(is_array($val)) {
                        if(is_string($val[0])) {
                            if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT|NOTLIKE|LIKE)$/i',$val[0])) { // �Ƚ�����
                                $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->parseValue($val[1]);
                            }elseif('exp'==strtolower($val[0])){ // ʹ�ñ��ʽ
                                $whereStr .= ' ('.$key.' '.$val[1].') ';
                            }elseif(preg_match('/IN/i',$val[0])){ // IN ����
                                $zone   =   is_array($val[1])? implode(',',$this->parseValue($val[1])):$val[1];
                                $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                            }elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN����
                                $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                                $whereStr .=  ' ('.$key.' BETWEEN '.$data[0].' AND '.$data[1].' )';
                            }else{
                                throw_exception(L('_EXPRESS_ERROR_').':'.$val[0]);
                            }
                        }else {
                            $count = count($val);
                            if(in_array(strtoupper(trim($val[$count-1])),array('AND','OR','XOR'))) {
                                $rule = strtoupper(trim($val[$count-1]));
                                $count   =  $count -1;
                            }else{
                                $rule = 'AND';
                            }
                            for($i=0;$i<$count;$i++) {
                                $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                                if('exp'==strtolower($val[$i][0])) {
                                    $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                                }else{
                                    $op = is_array($val[$i])?$this->comparison[strtolower($val[$i][0])]:'=';
                                    $whereStr .= '('.$key.' '.$op.' '.$this->parseValue($data).') '.$rule.' ';
                                }
                            }
                            $whereStr = substr($whereStr,0,-4);
                        }
                    }else {
                        //���ַ��������ֶβ���ģ��ƥ��
                        if(C('LIKE_MATCH_FIELDS') && preg_match('/('.C('LIKE_MATCH_FIELDS').')/i',$key)) {
                            $val = '%'.$val.'%';
                            $whereStr .= $key." LIKE ".$this->parseValue($val);
                        }else {
                            $whereStr .= $key." = ".$this->parseValue($val);
                        }
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return empty($whereStr)?'':' WHERE '.$whereStr;
    }

    /**
     +----------------------------------------------------------
     * ������������
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     * @param mixed $val
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseThinkWhere($key,$val) {
        $whereStr   = '';
        switch($key) {
            case '_string':
                // �ַ���ģʽ��ѯ����
                $whereStr = $val;
                break;
            case '_complex':
                // ���ϲ�ѯ����
                $whereStr   = substr($this->parseWhere($val),6);
                break;
            case '_query':
                // �ַ���ģʽ��ѯ����
                parse_str($val,$where);
                if(isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op   =  ' AND ';
                }
                $array   =  array();
                foreach ($where as $field=>$data)
                    $array[] = $this->parseKey($field).' = '.$this->parseValue($data);
                $whereStr   = implode($op,$array);
                break;
        }
        return $whereStr;
    }

    /**
     +----------------------------------------------------------
     * limit����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $lmit
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseLimit($limit) {
        return !empty($limit)?   ' LIMIT '.$limit.' ':'';
    }

    /**
     +----------------------------------------------------------
     * join����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $join
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseJoin($join) {
        $joinStr = '';
        if(!empty($join)) {
            if(is_array($join)) {
                foreach ($join as $key=>$_join){
                    if(false !== stripos($_join,'JOIN'))
                        $joinStr .= ' '.$_join;
                    else
                        $joinStr .= ' LEFT JOIN ' .$_join;
                }
            }else{
                $joinStr .= ' LEFT JOIN ' .$join;
            }
        }
        return $joinStr;
    }

    /**
     +----------------------------------------------------------
     * order����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $order
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseOrder($order) {
        return !empty($order)?  ' ORDER BY '.$order:'';
    }

    /**
     +----------------------------------------------------------
     * group����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $group
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseGroup($group) {
        return !empty($group)? ' GROUP BY '.$group:'';
    }

    /**
     +----------------------------------------------------------
     * having����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $having
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseHaving($having) {
        return  !empty($having)?   ' HAVING '.$having:'';
    }

    /**
     +----------------------------------------------------------
     * distinct����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $distinct
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseDistinct($distinct) {
        return !empty($distinct)?   ' DISTINCT ' :'';
    }

    /**
     +----------------------------------------------------------
     * �����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ����
     * @param array $options �������ʽ
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function insert($data,$options=array()) {
        foreach ($data as $key=>$val){
            $value   =  $this->parseValue($val);
            if(is_scalar($value)) { // ���˷Ǳ�������
                $values[]   =  $value;
                $fields[]     =  $this->parseKey($key);
            }
        }
        $sql   =  'INSERT INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        $sql   .= $this->parseLock(isset($options['lock'])?$options['lock']:false);
        return $this->execute($sql);
    }

    /**
     +----------------------------------------------------------
     * ���¼�¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ����
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function update($data,$options) {
        $sql   = 'UPDATE '
            .$this->parseTable($options['table'])
            .$this->parseSet($data)
            .$this->parseWhere(isset($options['where'])?$options['where']:'')
            .$this->parseOrder(isset($options['order'])?$options['order']:'')
            .$this->parseLimit(isset($options['limit'])?$options['limit']:'')
            .$this->parseLock(isset($options['lock'])?$options['lock']:false);
        return $this->execute($sql);
    }

    /**
     +----------------------------------------------------------
     * ɾ����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function delete($options=array()) {
        $sql   = 'DELETE FROM '
            .$this->parseTable($options['table'])
            .$this->parseWhere(isset($options['where'])?$options['where']:'')
            .$this->parseOrder(isset($options['order'])?$options['order']:'')
            .$this->parseLimit(isset($options['limit'])?$options['limit']:'')
            .$this->parseLock(isset($options['lock'])?$options['lock']:false);
        return $this->execute($sql);
    }

    /**
     +----------------------------------------------------------
     * ���Ҽ�¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function select($options=array()) {
        if(isset($options['page'])) {
            // ����ҳ������limit
            list($page,$listRows) =  explode(',',$options['page']);
            $listRows = $listRows?$listRows:($options['limit']?$options['limit']:20);
            $offset  =  $listRows*((int)$page-1);
            $options['limit'] =  $offset.','.$listRows;
        }
        $sql   = str_replace(
            array('%TABLE%','%DISTINCT%','%FIELDS%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%'),
            array(
                $this->parseTable($options['table']),
                $this->parseDistinct(isset($options['distinct'])?$options['distinct']:false),
                $this->parseField(isset($options['field'])?$options['field']:'*'),
                $this->parseJoin(isset($options['join'])?$options['join']:''),
                $this->parseWhere(isset($options['where'])?$options['where']:''),
                $this->parseGroup(isset($options['group'])?$options['group']:''),
                $this->parseHaving(isset($options['having'])?$options['having']:''),
                $this->parseOrder(isset($options['order'])?$options['order']:''),
                $this->parseLimit(isset($options['limit'])?$options['limit']:'')
            ),$this->selectSql);
        $sql   .= $this->parseLock(isset($options['lock'])?$options['lock']:false);
        return $this->query($sql);
    }

    /**
     +----------------------------------------------------------
     * �ֶκͱ������`
     * ��ָ֤����ʹ�ùؼ��ֲ����� ���mysql
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $value
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    protected function parseKey(&$value) {
        $value   =  trim($value);
        if( false !== strpos($value,' ') || false !== strpos($value,',') || false !== strpos($value,'*') ||  false !== strpos($value,'(') || false !== strpos($value,'.') || false !== strpos($value,'`')) {
            //�������* ���� ʹ����sql���� ��������
        }else{
            $value = '`'.$value.'`';
        }
        return $value;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ���һ�β�ѯ��sql���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getLastSql() {
        return $this->queryStr;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ��������ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getLastInsID(){
        return $this->lastInsID;
    }
}