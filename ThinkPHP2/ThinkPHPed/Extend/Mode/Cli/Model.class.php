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
// $Id: Model.class.php 2702 2012-02-02 12:35:01Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP CLIģʽModelģ����
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Model.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class Model {

    // ��ǰ���ݿ��������
    protected $db = null;
    // ��������
    protected $pk  = 'id';
    // ���ݱ�ǰ׺
    protected $tablePrefix  =   '';
    // ģ������
    protected $name = '';
    // ���ݿ�����
    protected $dbName  = '';
    // ���ݱ�������������ǰ׺��
    protected $tableName = '';
    // ʵ�����ݱ�����������ǰ׺��
    protected $trueTableName ='';
    // ���������Ϣ
    protected $error = '';
    // �ֶ���Ϣ
    protected $fields = array();
    // ������Ϣ
    protected $data =   array();
    // ��ѯ���ʽ����
    protected $options  =   array();
    protected $_validate       = array();  // �Զ���֤����
    protected $_auto           = array();  // �Զ���ɶ���
    // �Ƿ��Զ�������ݱ��ֶ���Ϣ
    protected $autoCheckFields   =   true;
    // �Ƿ���������֤
    protected $patchValidate   =  false;

    /**
     +----------------------------------------------------------
     * �ܹ�����
     * ȡ��DB���ʵ������ �ֶμ��
     +----------------------------------------------------------
     * @param string $name ģ������
     * @param string $tablePrefix ��ǰ׺
     * @param mixed $connection ���ݿ�������Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($name='',$tablePrefix='',$connection='') {
        // ģ�ͳ�ʼ��
        $this->_initialize();
        // ��ȡģ������
        if(!empty($name)) {
            if(strpos($name,'.')) { // ֧�� ���ݿ���.ģ������ ����
                list($this->dbName,$this->name) = explode('.',$name);
            }else{
                $this->name   =  $name;
            }
        }elseif(empty($this->name)){
            $this->name =   $this->getModelName();
        }
        if(!empty($tablePrefix)) {
            $this->tablePrefix =  $tablePrefix;
        }
        // ���ñ�ǰ׺
        $this->tablePrefix = $this->tablePrefix?$this->tablePrefix:C('DB_PREFIX');
        // ���ݿ��ʼ������
        // ��ȡ���ݿ��������
        // ��ǰģ���ж��������ݿ�������Ϣ
        $this->db(0,empty($this->connection)?$connection:$this->connection);
        // �ֶμ��
        if(!empty($this->name) && $this->autoCheckFields)    $this->_checkTableInfo();
    }

    /**
     +----------------------------------------------------------
     * �Զ�������ݱ���Ϣ
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function _checkTableInfo() {
        // �������Model�� �Զ���¼���ݱ���Ϣ
        // ֻ�ڵ�һ��ִ�м�¼
        if(empty($this->fields)) {
            // ������ݱ��ֶ�û�ж������Զ���ȡ
            if(C('DB_FIELDS_CACHE')) {
                $db   =  $this->dbName?$this->dbName:C('DB_NAME');
                $this->fields = F('_fields/'.$db.'.'.$this->name);
                if(!$this->fields)   $this->flush();
            }else{
                // ÿ�ζ����ȡ���ݱ���Ϣ
                $this->flush();
            }
        }
    }

    /**
     +----------------------------------------------------------
     * ��ȡ�ֶ���Ϣ������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function flush() {
        // ���治�������ѯ���ݱ���Ϣ
        $fields =   $this->db->getFields($this->getTableName());
        if(!$fields) { // �޷���ȡ�ֶ���Ϣ
            return false;
        }
        $this->fields   =   array_keys($fields);
        $this->fields['_autoinc'] = false;
        foreach ($fields as $key=>$val){
            // ��¼�ֶ�����
            $type[$key]    =   $val['type'];
            if($val['primary']) {
                $this->fields['_pk'] = $key;
                if($val['autoinc']) $this->fields['_autoinc']   =   true;
            }
        }
        // ��¼�ֶ�������Ϣ
        if(C('DB_FIELDTYPE_CHECK'))   $this->fields['_type'] =  $type;

        // 2008-3-7 ���ӻ��濪�ؿ���
        if(C('DB_FIELDS_CACHE')){
            // ���û������ݱ���Ϣ
            $db   =  $this->dbName?$this->dbName:C('DB_NAME');
            F('_fields/'.$db.'.'.$this->name,$this->fields);
        }
    }

    /**
     +----------------------------------------------------------
     * �������ݶ����ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ����
     * @param mixed $value ֵ
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function __set($name,$value) {
        // �������ݶ�������
        $this->data[$name]  =   $value;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ���ݶ����ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return isset($this->data[$name])?$this->data[$name]:null;
    }

    /**
     +----------------------------------------------------------
     * ������ݶ����ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ����
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
     +----------------------------------------------------------
     * �������ݶ����ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ����
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
     +----------------------------------------------------------
     * ����__call����ʵ��һЩ�����Model����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ��������
     * @param array $args ���ò���
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
        if(in_array(strtolower($method),array('table','where','order','limit','page','alias','having','group','lock','distinct'),true)) {
            // ���������ʵ��
            $this->options[strtolower($method)] =   $args[0];
            return $this;
        }elseif(in_array(strtolower($method),array('count','sum','min','max','avg'),true)){
            // ͳ�Ʋ�ѯ��ʵ��
            $field =  isset($args[0])?$args[0]:'*';
            return $this->getField(strtoupper($method).'('.$field.') AS tp_'.$method);
        }elseif(strtolower(substr($method,0,5))=='getby') {
            // ����ĳ���ֶλ�ȡ��¼
            $field   =   parse_name(substr($method,5));
            $where[$field] =  $args[0];
            return $this->where($where)->find();
        }else{
            throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
    // �ص����� ��ʼ��ģ��
    protected function _initialize() {}

    /**
     +----------------------------------------------------------
     * �Ա��浽���ݿ�����ݽ��д���
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data Ҫ����������
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
     protected function _facade($data) {
        // ���������ֶ�
        if(!empty($this->fields)) {
            foreach ($data as $key=>$val){
                if(!in_array($key,$this->fields,true)){
                    unset($data[$key]);
                }elseif(C('DB_FIELDTYPE_CHECK') && is_scalar($val)) {
                    // �ֶ����ͼ��
                    $this->_parseType($data,$key);
                }
            }
        }
        return $data;
     }

    /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ����
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function add($data='',$options=array()) {
        if(empty($data)) {
            // û�д������ݣ���ȡ��ǰ���ݶ����ֵ
            if(!empty($this->data)) {
                $data    =   $this->data;
            }else{
                $this->error = L('_DATA_TYPE_INVALID_');
                return false;
            }
        }
        // �������ʽ
        $options =  $this->_parseOptions($options);
        // ���ݴ���
        $data = $this->_facade($data);
        // д�����ݵ����ݿ�
        $result = $this->db->insert($data,$options);
        if(false !== $result ) {
            $insertId   =   $this->getLastInsID();
            if($insertId) {
                // �����������ز���ID
                return $insertId;
            }
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ����
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function save($data='',$options=array()) {
        if(empty($data)) {
            // û�д������ݣ���ȡ��ǰ���ݶ����ֵ
            if(!empty($this->data)) {
                $data    =   $this->data;
            }else{
                $this->error = L('_DATA_TYPE_INVALID_');
                return false;
            }
        }
        // ���ݴ���
        $data = $this->_facade($data);
        // �������ʽ
        $options =  $this->_parseOptions($options);
        if(!isset($options['where']) ) {
            // ��������������� ���Զ���Ϊ��������
            if(isset($data[$this->getPk()])) {
                $pk   =  $this->getPk();
                $where[$pk]   =  $data[$pk];
                $options['where']  =  $where;
                unset($data[$pk]);
            }else{
                // ���û���κθ���������ִ��
                $this->error = L('_OPERATION_WRONG_');
                return false;
            }
        }
        $result = $this->db->update($data,$options);
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ɾ������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $options ���ʽ
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function delete($options=array()) {
        if(empty($options) && empty($this->options)) {
            // ���ɾ������Ϊ�� ��ɾ����ǰ���ݶ�������Ӧ�ļ�¼
            if(!empty($this->data) && isset($this->data[$this->getPk()]))
                return $this->delete($this->data[$this->getPk()]);
            else
                return false;
        }
        if(is_numeric($options)  || is_string($options)) {
            // ��������ɾ����¼
            $pk   =  $this->getPk();
            if(strpos($options,',')) {
                $where[$pk]   =  array('IN', $options);
            }else{
                $where[$pk]   =  $options;
                $pkValue = $options;
            }
            $options =  array();
            $options['where'] =  $where;
        }
        // �������ʽ
        $options =  $this->_parseOptions($options);
        $result=    $this->db->delete($options);
        // ����ɾ����¼����
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ��ѯ���ݼ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ���ʽ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function select($options=array()) {
        if(is_string($options) || is_numeric($options)) {
            // ����������ѯ
            $pk   =  $this->getPk();
            if(strpos($options,',')) {
                $where[$pk] =  array('IN',$options);
            }else{
                $where[$pk]   =  $options;
            }
            $options =  array();
            $options['where'] =  $where;
        }
        // �������ʽ
        $options =  $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) { // ��ѯ���Ϊ��
            return null;
        }
        return $resultSet;
    }

    /**
     +----------------------------------------------------------
     * �������ʽ
     +----------------------------------------------------------
     * @access proteced
     +----------------------------------------------------------
     * @param array $options ���ʽ����
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function _parseOptions($options=array()) {
        if(is_array($options))
            $options =  array_merge($this->options,$options);
        // ��ѯ�������sql���ʽ��װ ����Ӱ���´β�ѯ
        $this->options  =   array();
        if(!isset($options['table']))
            // �Զ���ȡ����
            $options['table'] =$this->getTableName();
        if(!empty($options['alias'])) {
            $options['table']   .= ' '.$options['alias'];
        }
        // �ֶ�������֤
        if(C('DB_FIELDTYPE_CHECK')) {
            if(isset($options['where']) && is_array($options['where'])) {
                // �������ѯ���������ֶ����ͼ��
                foreach ($options['where'] as $key=>$val){
                    if(in_array($key,$this->fields,true) && is_scalar($val)){
                        $this->_parseType($options['where'],$key);
                    }
                }
            }
        }
        return $options;
    }

    /**
     +----------------------------------------------------------
     * �������ͼ��
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data ����
     * @param string $key �ֶ���
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function _parseType(&$data,$key) {
        $fieldType = strtolower($this->fields['_type'][$key]);
        if(false !== strpos($fieldType,'int')) {
            $data[$key]   =  intval($data[$key]);
        }elseif(false !== strpos($fieldType,'float') || false !== strpos($fieldType,'double')){
            $data[$key]   =  floatval($data[$key]);
        }elseif(false !== strpos($fieldType,'bool')){
            $data[$key]   =  (bool)$data[$key];
        }
    }

    /**
     +----------------------------------------------------------
     * ��ѯ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $options ���ʽ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function find($options=array()) {
        if(is_numeric($options) || is_string($options)) {
            $where[$this->getPk()] =$options;
            $options = array();
            $options['where'] = $where;
        }
        // ���ǲ���һ����¼
        $options['limit'] = 1;
        // �������ʽ
        $options =  $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) {// ��ѯ���Ϊ��
            return null;
        }
        $this->data = $resultSet[0];
        return $this->data;
    }

    /**
     +----------------------------------------------------------
     * ���ü�¼��ĳ���ֶ�ֵ
     * ֧��ʹ�����ݿ��ֶκͷ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string|array $field  �ֶ���
     * @param string|array $value  �ֶ�ֵ
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function setField($field,$value) {
        if(is_array($field)) {
            $data = $field;
        }else{
            $data[$field]   =  $value;
        }
        return $this->save($data);
    }

    /**
     +----------------------------------------------------------
     * �ֶ�ֵ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $field  �ֶ���
     * @param integer $step  ����ֵ
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function setInc($field,$step=1) {
        return $this->setField($field,array('exp',$field.'+'.$step));
    }

    /**
     +----------------------------------------------------------
     * �ֶ�ֵ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $field  �ֶ���
     * @param integer $step  ����ֵ
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function setDec($field,$step=1) {
        return $this->setField($field,array('exp',$field.'-'.$step));
    }

    /**
     +----------------------------------------------------------
     * ��ȡһ����¼��ĳ���ֶ�ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $field  �ֶ���
     * @param string $spea  �ֶ����ݼ������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function getField($field,$sepa=null) {
        $options['field']    =  $field;
        $options =  $this->_parseOptions($options);
        if(strpos($field,',')) { // ���ֶ�
            $resultSet = $this->db->select($options);
            if(!empty($resultSet)) {
                $_field = explode(',', $field);
                $field  = array_keys($resultSet[0]);
                $move   =  $_field[0]==$_field[1]?false:true;
                $key =  array_shift($field);
                $key2 = array_shift($field);
                $cols   =   array();
                $count  =   count($_field);
                foreach ($resultSet as $result){
                    $name   =  $result[$key];
                    if($move) { // ɾ����ֵ��¼
                        unset($result[$key]);
                    }
                    if(2==$count) {
                        $cols[$name]   =  $result[$key2];
                    }else{
                        $cols[$name]   =  is_null($sepa)?$result:implode($sepa,$result);
                    }
                }
                return $cols;
            }
        }else{   // ����һ����¼
            $options['limit'] = 1;
            $result = $this->db->select($options);
            if(!empty($result)) {
                return reset($result[0]);
            }
        }
        return null;
    }

    /**
     +----------------------------------------------------------
     * �������ݶ��� �������浽���ݿ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ��������
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
     public function create($data='') {
        // ���û�д�ֵĬ��ȡPOST����
        if(empty($data)) {
            $data    =   $_POST;
        }elseif(is_object($data)){
            $data   =   get_object_vars($data);
        }
        // ��֤����
        if(empty($data) || !is_array($data)) {
            $this->error = L('_DATA_TYPE_INVALID_');
            return false;
        }

        // ��֤����������ݶ���
        if($this->autoCheckFields) { // �����ֶμ�� ����˷Ƿ��ֶ�����
            $vo   =  array();
            foreach ($this->fields as $key=>$name){
                if(substr($key,0,1)=='_') continue;
                $val = isset($data[$name])?$data[$name]:null;
                //��֤��ֵ��Ч
                if(!is_null($val)){
                    $vo[$name] = (MAGIC_QUOTES_GPC && is_string($val))?   stripslashes($val)  :  $val;
                }
            }
        }else{
            $vo   =  $data;
        }

        // ��ֵ��ǰ���ݶ���
        $this->data =   $vo;
        // ���ش����������Թ���������
        return $vo;
     }

    /**
     +----------------------------------------------------------
     * SQL��ѯ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $sql  SQLָ��
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function query($sql) {
        if(!empty($sql)) {
            if(strpos($sql,'__TABLE__'))
                $sql    =   str_replace('__TABLE__',$this->getTableName(),$sql);
            return $this->db->query($sql);
        }else{
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * ִ��SQL���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $sql  SQLָ��
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function execute($sql) {
        if(!empty($sql)) {
            if(strpos($sql,'__TABLE__'))
                $sql    =   str_replace('__TABLE__',$this->getTableName(),$sql);
            return $this->db->execute($sql);
        }else {
            return false;
        }
    }

    /**
     +----------------------------------------------------------
     * �л���ǰ�����ݿ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param integer $linkNum  �������
     * @param mixed $config  ���ݿ�������Ϣ
     * @param array $params  ģ�Ͳ���
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function db($linkNum,$config='',$params=array()){
        static $_db = array();
        if(!isset($_db[$linkNum])) {
            // ����һ���µ�ʵ��
            if(!empty($config) && false === strpos($config,'/')) { // ֧�ֶ�ȡ���ò���
                $config  =  C($config);
            }
            $_db[$linkNum]            =    Db::getInstance($config);
        }elseif(NULL === $config){
            $_db[$linkNum]->close(); // �ر����ݿ�����
            unset($_db[$linkNum]);
            return ;
        }
        if(!empty($params)) {
            if(is_string($params))    parse_str($params,$params);
            foreach ($params as $name=>$value){
                $this->setProperty($name,$value);
            }
        }
        // �л����ݿ�����
        $this->db   =    $_db[$linkNum];
        return $this;
    }

    /**
     +----------------------------------------------------------
     * �õ���ǰ�����ݶ�������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getModelName() {
        if(empty($this->name))
            $this->name =   substr(get_class($this),0,-5);
        return $this->name;
    }

    /**
     +----------------------------------------------------------
     * �õ����������ݱ���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getTableName() {
        if(empty($this->trueTableName)) {
            $tableName  = !empty($this->tablePrefix) ? $this->tablePrefix : '';
            if(!empty($this->tableName)) {
                $tableName .= $this->tableName;
            }else{
                $tableName .= parse_name($this->name);
            }
            $this->trueTableName    =   strtolower($tableName);
        }
        return (!empty($this->dbName)?$this->dbName.'.':'').$this->trueTableName;
    }

    /**
     +----------------------------------------------------------
     * ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function startTrans() {
        $this->commit();
        $this->db->startTrans();
        return ;
    }

    /**
     +----------------------------------------------------------
     * �ύ����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     +----------------------------------------------------------
     * ����ع�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function rollback() {
        return $this->db->rollback();
    }

    /**
     +----------------------------------------------------------
     * ����ģ�͵Ĵ�����Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getError(){
        return $this->error;
    }

    /**
     +----------------------------------------------------------
     * �������ݿ�Ĵ�����Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getDbError() {
        return $this->db->getError();
    }

    /**
     +----------------------------------------------------------
     * �����������ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getLastInsID() {
        return $this->db->getLastInsID();
    }

    /**
     +----------------------------------------------------------
     * �������ִ�е�sql���
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getLastSql() {
        return $this->db->getLastSql();
    }
    // ����getLastSql�Ƚϳ��� ����_sql ����
    public function _sql(){
        return $this->getLastSql();
    }

    /**
     +----------------------------------------------------------
     * ��ȡ��������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getPk() {
        return isset($this->fields['_pk'])?$this->fields['_pk']:$this->pk;
    }

    /**
     +----------------------------------------------------------
     * ��ȡ���ݱ��ֶ���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function getDbFields(){
        if($this->fields) {
            $fields   =  $this->fields;
            unset($fields['_autoinc'],$fields['_pk'],$fields['_type']);
            return $fields;
        }
        return false;
    }

    /**
     +----------------------------------------------------------
     * ָ����ѯ�ֶ� ֧���ֶ��ų�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $field
     * @param boolean $except �Ƿ��ų�
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function field($field,$except=false){
        if($except) {// �ֶ��ų�
            if(is_string($field)) {
                $field =  explode(',',$field);
            }
            $fields   =  $this->getDbFields();
            $field =  $fields?array_diff($fields,$field):$field;
        }
        $this->options['field']   =   $field;
        return $this;
    }

    /**
     +----------------------------------------------------------
     * �������ݶ���ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ����
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function data($data){
        if(is_object($data)){
            $data   =   get_object_vars($data);
        }elseif(is_string($data)){
            parse_str($data,$data);
        }elseif(!is_array($data)){
            throw_exception(L('_DATA_TYPE_INVALID_'));
        }
        $this->data = $data;
        return $this;
    }

    /**
     +----------------------------------------------------------
     * ��ѯSQL��װ join
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $join
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function join($join) {
        if(is_array($join))
            $this->options['join'] =  $join;
        else
            $this->options['join'][]  =   $join;
        return $this;
    }

    /**
     +----------------------------------------------------------
     * ��ѯSQL��װ union
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $union
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function union($union) {
        if(empty($union)) return $this;
        // ת��union���ʽ
        if($union instanceof Model) {
            $options   =  $union->getProperty('options');
            if(!isset($options['table'])){
                // �Զ���ȡ����
                $options['table'] =$union->getTableName();
            }
            if(!isset($options['field'])) {
                $options['field'] =$this->options['field'];
            }
        }elseif(is_object($union)) {
            $options   =  get_object_vars($union);
        }elseif(!is_array($union)){
            throw_exception(L('_DATA_TYPE_INVALID_'));
        }
        $this->options['union'][]  =   $options;
        return $this;
    }

    /**
     +----------------------------------------------------------
     * ����ģ�͵�����ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ����
     * @param mixed $value ֵ
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function setProperty($name,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
        return $this;
    }

    /**
     +----------------------------------------------------------
     * ��ȡģ�͵�����ֵ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function getProperty($name){
        if(property_exists($this,$name))
            return $this->$name;
        else
            return NULL;
    }
}