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
 * ThinkPHP ���ģʽModelģ����
 * ֻ֧��ԭ��SQL���� ֧�ֶ����ݿ����Ӻ��л�
 +------------------------------------------------------------------------------
 */
class Model {
    // ��ǰ���ݿ��������
    protected $db = null;
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
        // ��ȡ���ݿ��������
        // ��ǰģ���ж��������ݿ�������Ϣ
        $this->db(0,empty($this->connection)?$connection:$this->connection);
        // ���ñ�ǰ׺
        $this->tablePrefix = $this->tablePrefix?$this->tablePrefix:C('DB_PREFIX');
    }

    // �ص����� ��ʼ��ģ��
    protected function _initialize() {}

    /**
     +----------------------------------------------------------
     * SQL��ѯ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $sql  SQLָ��
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function query($sql) {
        if(is_array($sql)) {
            return $this->patchQuery($sql);
        }
        if(!empty($sql)) {
            if(strpos($sql,'__TABLE__')) {
                $sql    =   str_replace('__TABLE__',$this->getTableName(),$sql);
            }
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
            if(strpos($sql,'__TABLE__')) {
                $sql    =   str_replace('__TABLE__',$this->getTableName(),$sql);
            }
            $result =   $this->db->execute($sql);
            return $result;
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

};
?>