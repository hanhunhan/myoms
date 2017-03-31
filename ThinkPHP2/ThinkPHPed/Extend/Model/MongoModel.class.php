<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2010 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: MongoModel.class.php 2576 2012-01-12 15:09:01Z liu21st $


/**
 +------------------------------------------------------------------------------
 * TOPThink MongoModelģ����
 * ʵ����ODM��ActiveRecordsģʽ
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: MongoModel.class.php 2576 2012-01-12 15:09:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class MongoModel extends Model{
    // ��������
    const TYPE_OBJECT = 1; 
    const TYPE_INT = 2;
    const TYPE_STRING = 3;

    // ��������
    protected $pk  = '_id';
    // _id ���� 1 Object ����MongoId���� 2 Int ���� ֧���Զ����� 3 String �ַ���Hash
    protected $_idType  =  self::TYPE_OBJECT;
    // �����Ƿ��Զ����� ֧��Int������
    protected $_autoInc =  false;
    // MongoĬ�Ϲر��ֶμ�� ���Զ�̬׷���ֶ�
    protected $autoCheckFields   =   false;

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
        if(in_array(strtolower($method),array('table','where','order','limit','page'),true)) {
            // ���������ʵ��
            $this->options[strtolower($method)] =   $args[0];
            return $this;
        }elseif(strtolower(substr($method,0,5))=='getby') {
            // ����ĳ���ֶλ�ȡ��¼
            $field   =   parse_name(substr($method,5));
            $where[$field] =$args[0];
            return $this->where($where)->find();
        }elseif(strtolower(substr($method,0,10))=='getfieldby') {
            // ����ĳ���ֶλ�ȡ��¼��ĳ��ֵ
            $name   =   parse_name(substr($method,10));
            $where[$name] =$args[0];
            return $this->where($where)->getField($args[1]);
        }else{
            throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }

    /**
     +----------------------------------------------------------
     * ��ȡ�ֶ���Ϣ������ ������������Ϣֱ������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function flush() {
        // ���治�������ѯ���ݱ���Ϣ
        $fields =   $this->db->getFields();
        if(!$fields) { // ��ʱû�������޷���ȡ�ֶ���Ϣ �´β�ѯ
            return false;
        }
        $this->fields   =   array_keys($fields);
        $this->fields['_pk'] = $this->pk;
        $this->fields['_autoinc'] = $this->_autoInc;
        foreach ($fields as $key=>$val){
            // ��¼�ֶ�����
            $type[$key]    =   $val['type'];
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

    // д������ǰ�Ļص����� ���������͸���
    protected function _before_write(&$data) {
        $pk   =  $this->getPk();
        // �����������ʹ�����������
        if(isset($data[$pk]) && $this->_idType == self::TYPE_OBJECT) {
            $data[$pk] =  new MongoId($data[$pk]);
        }    
    }

    /**
     +----------------------------------------------------------
     * countͳ�� ���where�������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function count(){
        // �������ʽ
        $options =  $this->_parseOptions();
        return $this->db->count($options);
    }

    /**
     +----------------------------------------------------------
     * ��ȡ��һID �����Զ�������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $pk �ֶ��� Ĭ��Ϊ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function getMongoNextId($pk=''){
        if(empty($pk)) {
            $pk   =  $this->getPk();
        }
        return $this->db->mongo_next_id($pk);
    }

    // ��������ǰ�Ļص�����
    protected function _before_insert(&$data,$options) {
        // д�����ݵ����ݿ�
        if($this->_autoInc && $this->_idType== self::TYPE_INT) { // �����Զ�����
            $pk   =  $this->getPk();
            if(!isset($data[$pk])) {
                $data[$pk]   =  $this->db->mongo_next_id($pk);
            }
        }
    }

    public function clear(){
        return $this->db->clear();
    }

    // ��ѯ�ɹ���Ļص�����
    protected function _after_select(&$resultSet,$options) {
        array_walk($resultSet,array($this,'checkMongoId'));
    }

    /**
     +----------------------------------------------------------
     * ��ȡMongoId
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $result ��������
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function checkMongoId(&$result){
        if(is_object($result['_id'])) {
            $result['_id'] = $result['_id']->__toString();
        }
        return $result;
    }

    // ���ʽ���˻ص�����
    protected function _options_filter(&$options) {
        $id = $this->getPk();
        if(isset($options['where'][$id]) && $this->_idType== self::TYPE_OBJECT) {
            $options['where'][$id] = new MongoId($options['where'][$id]);
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
         if( is_numeric($options) || is_string($options)) {
            $id   =  $this->getPk();
            $where[$id] = $options;
            $options = array();
            $options['where'] = $where;
         }
        // �������ʽ
        $options =  $this->_parseOptions($options);
        $result = $this->db->find($options);
        if(false === $result) {
            return false;
        }
        if(empty($result)) {// ��ѯ���Ϊ��
            return null;
        }else{
            $this->checkMongoId($result);
        }
        $this->data = $result;
        $this->_after_find($this->data,$options);
        return $this->data;
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
        return $this->setField($field,array('inc',$step));
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
        return $this->setField($field,array('inc','-'.$step));
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
            $result = $this->db->find($options);
            if(!empty($result)) {
                return $result[$field];
            }
        }
        return null;
    }

    /**
     +----------------------------------------------------------
     * ִ��Mongoָ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $command  ָ��
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function command($command) {
        return $this->db->command($command);
    }

    /**
     +----------------------------------------------------------
     * ִ��MongoCode
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $code  MongoCode
     * @param array $args   ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function mongoCode($code,$args=array()) {
        return $this->db->execute($code,$args);
    }

    // ���ݿ��л���ص�����
    protected function _after_db() {
        // �л�Collection
        $this->db->switchCollection($this->getTableName(),$this->dbName?$this->dbName:C('db_name'));    
    }

    /**
     +----------------------------------------------------------
     * �õ����������ݱ��� Mongo��������dbName
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
        return $this->trueTableName;
    }
}