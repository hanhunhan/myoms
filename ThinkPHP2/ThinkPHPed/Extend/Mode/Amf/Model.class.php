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
// $Id: Model.class.php 2656 2012-01-23 08:53:55Z liu21st $

/**
 +------------------------------------------------------------------------------
 * ThinkPHP AMFģʽModelģ����
 * ֻ֧��CURD��������� �Լ����ò�ѯ ȥ���ص��ӿ�
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
    // ������Ϣ
    protected $data =   array();
    // ��ѯ���ʽ����
    protected $options  =   array();
    // ���������Ϣ
    protected $error = '';

    /**
     +----------------------------------------------------------
     * �ܹ�����
     * ȡ��DB���ʵ������
     +----------------------------------------------------------
     * @param string $name ģ������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($name='') {
        // ģ�ͳ�ʼ��
        $this->_initialize();
        // ��ȡģ������
        if(!empty($name)) {
            $this->name   =  $name;
        }elseif(empty($this->name)){
            $this->name =   $this->getModelName();
        }
        // ���ݿ��ʼ������
        import("Db");
        // ��ȡ���ݿ��������
        $this->db = Db::getInstance(empty($this->connection)?'':$this->connection);
        // ���ñ�ǰ׺
        $this->tablePrefix = $this->tablePrefix?$this->tablePrefix:C('DB_PREFIX');
        // �ֶμ��
        if(!empty($this->name))    $this->_checkTableInfo();
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
                $this->fields = F('_fields/'.$this->name);
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
        if(C('DB_FIELDS_CACHE'))
            // ���û������ݱ���Ϣ
            F('_fields/'.$this->name,$this->fields);
    }

    // �ص����� ��ʼ��ģ��
    protected function _initialize() {}
    /**
     +----------------------------------------------------------
     * ����__call����ʵ��һЩ�����Model���� ��ħ��������
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
        if(in_array(strtolower($method),array('field','table','where','order','limit','page','having','group','lock','distinct'),true)) {
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
            $options['where'] =  $field.'=\''.$args[0].'\'';
            return $this->find($options);
        }else{
            throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }

    /**
     +----------------------------------------------------------
     * �������ݶ����ֵ ��ħ��������
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
     * ��ȡ���ݶ����ֵ ��ħ��������
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
        // д�����ݵ����ݿ�
        $result = $this->db->insert($data,$options);
        $insertId   =   $this->getLastInsID();
        if($insertId) {
            return $insertId;
        }
        //�ɹ��󷵻ز���ID
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
        // �������ʽ
        $options =  $this->_parseOptions($options);
        if(!isset($options['where']) ) {
            // ��������������� ���Զ���Ϊ��������
            if(isset($data[$this->getPk()])) {
                $pk   =  $this->getPk();
                $options['where']  =  $pk.'=\''.$data[$pk].'\'';
                $pkValue = $data[$pk];
                unset($data[$pk]);
            }else{
                // ���û���κθ���������ִ��
                $this->error = L('_OPERATION_WRONG_');
                return false;
            }
        }
        return $this->db->update($data,$options);
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
            $where  =  $pk.'=\''.$options.'\'';
            $pkValue = $options;
            $options =  array();
            $options['where'] =  $where;
        }
        // �������ʽ
        $options =  $this->_parseOptions($options);
        return $this->db->delete($options);
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
        // �������ʽ
        $options =  $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if(empty($resultSet)) { // ��ѯ���Ϊ��
            return false;
        }
        return $resultSet;
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
             $where = $this->getPk().'=\''.$options.'\'';
             $options = array();
             $options['where'] = $where;
         }
         // ���ǲ���һ����¼
        $options['limit'] = 1;
        // �������ʽ
        $options =  $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if(empty($resultSet)) {// ��ѯ���Ϊ��
            return false;
        }
        $this->data = $resultSet[0];
        return $this->data;
     }

    /**
     +----------------------------------------------------------
     * �������ʽ
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @param array $options ���ʽ����
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    private function _parseOptions($options) {
        if(is_array($options))
            $options =  array_merge($this->options,$options);
        // ��ѯ�������sql���ʽ��װ ����Ӱ���´β�ѯ
        $this->options  =   array();
        if(!isset($options['table']))
            // �Զ���ȡ����
            $options['table'] =$this->getTableName();
        return $options;
    }

    /**
     +----------------------------------------------------------
     * �������ݶ��� �������浽���ݿ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ��������
     * @param string $type ״̬
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
     public function create($data='',$type='') {
        // ���û�д�ֵĬ��ȡPOST����
        if(empty($data)) {
            $data    =   $_POST;
        }elseif(is_object($data)){
            $data   =   get_object_vars($data);
        }elseif(!is_array($data)){
            $this->error = L('_DATA_TYPE_INVALID_');
            return false;
        }
        // �������ݶ���
        $vo   =  array();
        foreach ($this->fields as $key=>$name){
            if(substr($key,0,1)=='_') continue;
            $val = isset($data[$name])?$data[$name]:null;
            //��֤��ֵ��Ч
            if(!is_null($val)){
                $vo[$name] = (MAGIC_QUOTES_GPC && is_string($val))?   stripslashes($val)  :  $val;
                if(C('DB_FIELDTYPE_CHECK')) {
                    // �ֶ����ͼ��
                    $fieldType = strtolower($this->fields['_type'][$name]);
                    if(false !== strpos($fieldType,'int')) {
                        $vo[$name]   =  intval($vo[$name]);
                    }elseif(false !== strpos($fieldType,'float') || false !== strpos($fieldType,'double')){
                        $vo[$name]   =  floatval($vo[$name]);
                    }
                }
            }
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
     * @param string $sql  SQLָ��
     +----------------------------------------------------------
     * @return array
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
    public function execute($sql='') {
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
     * �õ���ǰ�����ݶ�������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getModelName() {
        if(empty($this->name)) {
            $this->name =   substr(get_class($this),0,-5);
        }
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
            if(!empty($this->dbName)) {
                $tableName    =  $this->dbName.'.'.$tableName;
            }
            $this->trueTableName    =   strtolower($tableName);
        }
        return $this->trueTableName;
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
};