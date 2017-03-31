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
 * ThinkPHP Model?????
 * ?????ORM??ActiveRecords??
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Core
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: Model.class.php 2702 2012-02-02 12:35:01Z liu21st $
 +------------------------------------------------------------------------------
 */
class Model {
    // ??????
    const MODEL_INSERT      =   1;      //  ???????????
    const MODEL_UPDATE    =   2;      //  ???????????
    const MODEL_BOTH      =   3;      //  ??????????????
    const MUST_VALIDATE         =   1;// ???????
    const EXISTS_VAILIDATE      =   0;// ???????????????
    const VALUE_VAILIDATE       =   2;// ??????????????
    // ?????????????
    private $_extModel =  null;
    // ???????????????
    protected $db = null;
    // ????????
    protected $pk  = 'id';
    // ???????
    protected $tablePrefix  =   '';
    // ???????
    protected $name = '';
    // ?????????
    protected $dbName  = '';
    // ?????????????????????
    protected $tableName = '';
    // ??????????????????????
    protected $trueTableName ='';
    // ??????????
    protected $error = '';
    // ??????
    protected $fields = array();
    // ???????
    protected $data =   array();
    // ???????????
    protected $options  =   array();
    protected $_validate       = array();  // ??????????
    protected $_auto           = array();  // ?????????
    protected $_map           = array();  // ????????
    // ????????????????????
    protected $autoCheckFields   =   true;
    // ????????????
    protected $patchValidate   =  false;

    /**
     +----------------------------------------------------------
     * ???????
     * ???DB?????????? ??¦Ì??
     +----------------------------------------------------------
     * @param string $name ???????
     * @param string $tablePrefix ????
     * @param mixed $connection ????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function __construct($name='',$tablePrefix='',$connection='') {
        // ???????
        $this->_initialize();
        // ??????????
        if(!empty($name)) {
            if(strpos($name,'.')) { // ??? ???????.??????? ????
                list($this->dbName,$this->name) = explode('.',$name);
            }else{
                $this->name   =  $name;
            }
        }elseif(empty($this->name)){
            $this->name =   $this->getModelName();
        }
        // ???????
        if(is_null($tablePrefix)) {// ???Null????????
            $this->tablePrefix = '';
        }elseif('' != $tablePrefix) {
            $this->tablePrefix = $tablePrefix;
        }else{
            $this->tablePrefix = $this->tablePrefix?$this->tablePrefix:C('DB_PREFIX');
        }

        // ?????????????
        // ???????????????
        // ???????§Ø?????????????????
        $this->db(0,empty($this->connection)?$connection:$this->connection);
    }

    /**
     +----------------------------------------------------------
     * ??????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function _checkTableInfo() {
        // ???????Model?? ??????????????
        // ?????????§Þ??
        if(empty($this->fields)) {
            // ?????????????§Ø???????????
            if(C('DB_FIELDS_CACHE')) {
                $db   =  $this->dbName?$this->dbName:C('DB_NAME');
                $this->fields = F('_fields/'.$db.'.'.$this->name);
                if(!$this->fields)   $this->flush();
            }else{
                // ??¦Æ?????????????
                $this->flush();
            }
        }
    }

    /**
     +----------------------------------------------------------
     * ???????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function flush() {
        // ???ÖÎ????????????????
        $this->db->setModel($this->name);
        $fields =   $this->db->getFields($this->getTableName());
        if(!$fields) { // ????????????
            return false;
        }
        $this->fields   =   array_keys($fields);
        $this->fields['_autoinc'] = false;
        foreach ($fields as $key=>$val){
            // ??????????
            $type[$key]    =   $val['type'];
            if($val['primary']) {
                $this->fields['_pk'] = $key;
                if($val['autoinc']) $this->fields['_autoinc']   =   true;
            }
        }
        // ?????????????
        if(C('DB_FIELDTYPE_CHECK'))   $this->fields['_type'] =  $type;

        // 2008-3-7 ??????žD?????
        if(C('DB_FIELDS_CACHE')){
            // ???????????????
            $db   =  $this->dbName?$this->dbName:C('DB_NAME');
            F('_fields/'.$db.'.'.$this->name,$this->fields);
        }
    }

    /**
     +----------------------------------------------------------
     * ????§Ý???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $type ???????????
     * @param mixed $vars ???????????????????
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function switchModel($type,$vars=array()) {
        $class = ucwords(strtolower($type)).'Model';
        if(!class_exists($class))
            throw_exception($class.L('_MODEL_NOT_EXIST_'));
        // ???????????
        $this->_extModel   = new $class($this->name);
        if(!empty($vars)) {
            // ????????????????????
            foreach ($vars as $var)
                $this->_extModel->setProperty($var,$this->$var);
        }
        return $this->_extModel;
    }

    /**
     +----------------------------------------------------------
     * ?????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ????
     * @param mixed $value ?
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function __set($name,$value) {
        // ???????????????
        $this->data[$name]  =   $value;
    }

    /**
     +----------------------------------------------------------
     * ????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __get($name) {
        return isset($this->data[$name])?$this->data[$name]:null;
    }

    /**
     +----------------------------------------------------------
     * ????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function __isset($name) {
        return isset($this->data[$name]);
    }

    /**
     +----------------------------------------------------------
     * ?????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
     +----------------------------------------------------------
     * ????__call????????§»?????Model????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $method ????????
     * @param array $args ???¨°???
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function __call($method,$args) {
        if(in_array(strtolower($method),array('table','where','order','limit','page','alias','having','group','lock','distinct'),true)) {
            // ????????????
            $this->options[strtolower($method)] =   $args[0];
            return $this;
        }elseif(in_array(strtolower($method),array('count','sum','min','max','avg'),true)){
            // ??????????
            $field =  isset($args[0])?$args[0]:'*';
            return $this->getField(strtoupper($method).'('.$field.') AS tp_'.$method);
        }elseif(strtolower(substr($method,0,5))=='getby') {
            // ?????????¦Ë?????
            $field   =   parse_name(substr($method,5));
            $where[$field] =  $args[0];
            return $this->where($where)->find();
        }elseif(strtolower(substr($method,0,10))=='getfieldby') {
            // ?????????¦Ë???????????
            $name   =   parse_name(substr($method,10));
            $where[$name] =$args[0];
            return $this->where($where)->getField($args[1]);
        }else{
            throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
            return;
        }
    }
    // ??????? ????????
    protected function _initialize() {}

    /**
     +----------------------------------------------------------
     * ????›Ô????????????§Õ???
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data ???????????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
     protected function _facade($data) {
        // ???????????
        if(!empty($this->fields)) {
            foreach ($data as $key=>$val){
                if(!in_array($key,$this->fields,true)){
                    unset($data[$key]);
                }elseif(C('DB_FIELDTYPE_CHECK') && is_scalar($val)) {
                    // ?????????
                    $this->_parseType($data,$key);
                }
            }
        }
        $this->_before_write($data);
        return $data;
     }

    // §Õ??????????????? ?????????????
    protected function _before_write(&$data) {}

    /**
     +----------------------------------------------------------
     * ????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ????
     * @param array $options ????
     * @param boolean $replace ???replace
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function add($data='',$options=array(),$replace=false) {
        if(empty($data)) {
            // ??§Õ???????????????????????
            if(!empty($this->data)) {
                $data    =   $this->data;
                // ????????
                $this->data = array();
            }else{
                $this->error = L('_DATA_TYPE_INVALID_');
                return false;
            }
        }
        // ????????
        $options =  $this->_parseOptions($options);
        // ???????
        $data = $this->_facade($data);
        if(false === $this->_before_insert($data,$options)) {
            return false;
        }
        // §Õ????????????
        $result = $this->db->insert($data,$options,$replace);
        if(false !== $result ) {
            $insertId   =   $this->getLastInsID();
            if($insertId) {
                // ???????????????ID
                $data[$this->getPk()]  = $insertId;
                $this->_after_insert($data,$options);
                return $insertId;
            }
        }
        return $result;
    }
    // ?????????????????
    protected function _before_insert(&$data,$options) {}
    // ???????????????
    protected function _after_insert($data,$options) {}

    public function addAll($dataList,$options=array(),$replace=false){
        if(empty($dataList)) {
            $this->error = L('_DATA_TYPE_INVALID_');
            return false;
        }
        // ????????
        $options =  $this->_parseOptions($options);
        // ???????
        foreach ($dataList as $key=>$data){
            $dataList[$key] = $this->_facade($data);
        }
        // §Õ????????????
        $result = $this->db->insertAll($dataList,$options,$replace);
        if(false !== $result ) {
            $insertId   =   $this->getLastInsID();
            if($insertId) {
                return $insertId;
            }
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ???Select????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $fields ????????????????
     * @param string $table ?????????????
     * @param array $options ????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function selectAdd($fields='',$table='',$options=array()) {
        // ????????
        $options =  $this->_parseOptions($options);
        // §Õ????????????
        if(false === $result = $this->db->selectInsert($fields?$fields:$options['field'],$table?$table:$this->getTableName(),$options)){
            // ??????????????
            $this->error = L('_OPERATION_WRONG_');
            return false;
        }else {
            // ??????
            return $result;
        }
    }

    /**
     +----------------------------------------------------------
     * ????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ????
     * @param array $options ????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function save($data='',$options=array()) {
        if(empty($data)) {
            // ??§Õ???????????????????????
            if(!empty($this->data)) {
                $data    =   $this->data;
                // ????????
                $this->data = array();
            }else{
                $this->error = L('_DATA_TYPE_INVALID_');
                return false;
            }
        }
        // ???????
        $data = $this->_facade($data);
        // ????????
        $options =  $this->_parseOptions($options);
        if(false === $this->_before_update($data,$options)) {
            return false;
        }
        if(!isset($options['where']) ) {
            // ??????????????? ????????????????
            if(isset($data[$this->getPk()])) {
                $pk   =  $this->getPk();
                $where[$pk]   =  $data[$pk];
                $options['where']  =  $where;
                $pkValue = $data[$pk];
                unset($data[$pk]);
            }else{
                // ???????¦Ê¦È????????????
                $this->error = L('_OPERATION_WRONG_');
                return false;
            }
        }
        $result = $this->db->update($data,$options);
        if(false !== $result) {
            if(isset($pkValue)) $data[$pk]   =  $pkValue;
            $this->_after_update($data,$options);
        }
        return $result;
    }
    // ?????????????????
    protected function _before_update(&$data,$options) {}
    // ???3???????????
    protected function _after_update($data,$options) {}

    /**
     +----------------------------------------------------------
     * ???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $options ????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function delete($options=array()) {
        if(empty($options) && empty($this->options)) {
            // ????????????? ????????????????????????
            if(!empty($this->data) && isset($this->data[$this->getPk()]))
                return $this->delete($this->data[$this->getPk()]);
            else
                return false;
        }
        if(is_numeric($options)  || is_string($options)) {
            // ??????????????
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
        // ????????
        $options =  $this->_parseOptions($options);
        $result=    $this->db->delete($options);
        if(false !== $result) {
            $data = array();
            if(isset($pkValue)) $data[$pk]   =  $pkValue;
            $this->_after_delete($data,$options);
        }
        // ??????????????
        return $result;
    }
    // ???????????????
    protected function _after_delete($data,$options) {}

    /**
     +----------------------------------------------------------
     * ????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ????????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function select($options=array()) {
        if(is_string($options) || is_numeric($options)) {
            // ???????????
            $pk   =  $this->getPk();
            if(strpos($options,',')) {
                $where[$pk] =  array('IN',$options);
            }else{
                $where[$pk]   =  $options;
            }
            $options =  array();
            $options['where'] =  $where;
        }elseif(false === $options){ // ???????? ??????????SQL
            $options =  array();
            // ????????
            $options =  $this->_parseOptions($options);
            return  '( '.$this->db->buildSelectSql($options).' )';
        }
        // ????????
        $options =  $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) { // ?????????
            return null;
        }
        $this->_after_select($resultSet,$options);
        return $resultSet;
    }
    // ???????????????
    protected function _after_select(&$resultSet,$options) {}

    /**
     +----------------------------------------------------------
     * ??????SQL ??????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ????????
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function buildSql($options=array()) {
        // ????????
        $options =  $this->_parseOptions($options);
        return  '( '.$this->db->buildSelectSql($options).' )';
    }

    /**
     +----------------------------------------------------------
     * ????????
     +----------------------------------------------------------
     * @access proteced
     +----------------------------------------------------------
     * @param array $options ????????
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function _parseOptions($options=array()) {
        if(is_array($options))
            $options =  array_merge($this->options,$options);
        // ??????????sql??????? ????????¡ä¦Â??
        $this->options  =   array();
        if(!isset($options['table']))
            // ??????????
            $options['table'] =$this->getTableName();
        if(!empty($options['alias'])) {
            $options['table']   .= ' '.$options['alias'];
        }
        // ????????????????
        $options['model'] =  $this->name;
        // ??????????
        if(C('DB_FIELDTYPE_CHECK')) {
            if(isset($options['where']) && is_array($options['where'])) {
                // ?????????????????????????
                foreach ($options['where'] as $key=>$val){
                    if(in_array($key,$this->fields,true) && is_scalar($val)){
                        $this->_parseType($options['where'],$key);
                    }
                }
            }
        }
        // ????????
        $this->_options_filter($options);
        return $options;
    }
    // ??????????????
    protected function _options_filter(&$options) {}

    /**
     +----------------------------------------------------------
     * ??????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $data ????
     * @param string $key ?????
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    protected function _parseType(&$data,$key) {
        $fieldType = strtolower($this->fields['_type'][$key]);
        if(false === strpos($fieldType,'bigint') && false !== strpos($fieldType,'int')) {
            $data[$key]   =  intval($data[$key]);
        }elseif(false !== strpos($fieldType,'float') || false !== strpos($fieldType,'double')){
            $data[$key]   =  floatval($data[$key]);
        }elseif(false !== strpos($fieldType,'bool')){
            $data[$key]   =  (bool)$data[$key];
        }
    }

    /**
     +----------------------------------------------------------
     * ???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $options ????????
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
        // ?????????????
        $options['limit'] = 1;
        // ????????
        $options =  $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) {// ?????????
            return null;
        }
        $this->data = $resultSet[0];
        $this->_after_find($this->data,$options);
        return $this->data;
    }
    // ??????????????
    protected function _after_find(&$result,$options) {}

    /**
     +----------------------------------------------------------
     * ??????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $data ???????
     * @param integer $type ???? 0 §Õ?? 1 ???
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseFieldsMap($data,$type=1) {
        // ?????????
        if(!empty($this->_map)) {
            foreach ($this->_map as $key=>$val){
                if($type==1) { // ???
                    if(isset($data[$val])) {
                        $data[$key] =   $data[$val];
                        unset($data[$val]);
                    }
                }else{
                    if(isset($data[$key])) {
                        $data[$val] =   $data[$key];
                        unset($data[$key]);
                    }
                }
            }
        }
        return $data;
    }

    /**
     +----------------------------------------------------------
     * ???¨¹???????????
     * ?????????????¦Ê????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string|array $field  ?????
     * @param string $value  ????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function setField($field,$value='') {
        if(is_array($field)) {
            $data = $field;
        }else{
            $data[$field]   =  $value;
        }
        return $this->save($data);
    }

    /**
     +----------------------------------------------------------
     * ????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $field  ?????
     * @param integer $step  ?????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function setInc($field,$step=1) {
        return $this->setField($field,array('exp',$field.'+'.$step));
    }

    /**
     +----------------------------------------------------------
     * ????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $field  ?????
     * @param integer $step  ?????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function setDec($field,$step=1) {
        return $this->setField($field,array('exp',$field.'-'.$step));
    }

    /**
     +----------------------------------------------------------
     * ??????????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $field  ?????
     * @param string $spea  ???????????? NULL????????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function getField($field,$sepa=null) {
        $options['field']    =  $field;
        $options =  $this->_parseOptions($options);
        if(strpos($field,',')) { // ?????
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
                    if($move) { // ?????????
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
        }else{   // ??????????
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
     * ??????????? ???????›Ô?????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ????????
     * @param string $type ??
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
     public function create($data='',$type='') {
        // ?????§Õ??????POST????
        if(empty($data)) {
            $data    =   $_POST;
        }elseif(is_object($data)){
            $data   =   get_object_vars($data);
        }
        // ???????
        if(empty($data) || !is_array($data)) {
            $this->error = L('_DATA_TYPE_INVALID_');
            return false;
        }

        // ?????????
        $data = $this->parseFieldsMap($data,0);

        // ??
        $type = $type?$type:(!empty($data[$this->getPk()])?self::MODEL_UPDATE:self::MODEL_INSERT);

        // ??????????
        if(!$this->autoValidation($data,$type)) return false;

        // ??????????
        if(C('TOKEN_ON') && !$this->autoCheckToken($data)) {
            $this->error = L('_TOKEN_ERROR_');
            return false;
        }

        // ?????????????????
        if($this->autoCheckFields) { // ??????¦Ì?? ??????????????
            $vo   =  array();
            foreach ($this->fields as $key=>$name){
                if(substr($key,0,1)=='_') continue;
                $val = isset($data[$name])?$data[$name]:null;
                //????????§¹
                if(!is_null($val)){
                    $vo[$name] = (MAGIC_QUOTES_GPC && is_string($val))?   stripslashes($val)  :  $val;
                }
            }
        }else{
            $vo   =  $data;
        }

        // ??????????????????????
        $this->autoOperation($vo,$type);
        // ?????????????
        $this->data =   $vo;
        // ????????????????????????
        return $vo;
     }

    // ?????????????
    // TODO  ajax??????????????????
    public function autoCheckToken($data) {
        if(C('TOKEN_ON')){
            $name   = C('TOKEN_NAME');
            if(!isset($data[$name]) || !isset($_SESSION[$name])) { // ??????????§¹
                return false;
            }

            // ???????
            list($key,$value)  =  explode('_',$data[$name]);
            if($_SESSION[$name][$key] == $value) { // ????????
                unset($_SESSION[$name][$key]); // ??????????session
                return true;
            }
            // ????TOKEN????
            if(C('TOKEN_RESET')) unset($_SESSION[$name][$key]);
            return false;
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ??????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $value  ??????????
     * @param string $rule ???????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function regex($value,$rule) {
        $validate = array(
            'require'=> '/.+/',
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'url' => '/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/',
            'currency' => '/^\d+(\.\d+)?$/',
            'number' => '/^\d+$/',
            'zip' => '/^[1-9]\d{5}$/',
            'integer' => '/^[-\+]?\d+$/',
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'english' => '/^[A-Za-z]+$/',
        );
        // ????????????????????
        if(isset($validate[strtolower($rule)]))
            $rule   =   $validate[strtolower($rule)];
        return preg_match($rule,$value)===1;
    }

    /**
     +----------------------------------------------------------
     * ??????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $data ????????
     * @param string $type ????????
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    private function autoOperation(&$data,$type) {
        // ??????
        if(!empty($this->_auto)) {
            foreach ($this->_auto as $auto){
                // ????????????
                // array('field','???????','???????','???????',[???????])
                if(empty($auto[2])) $auto[2] = self::MODEL_INSERT; // ???????????????????
                if( $type == $auto[2] || $auto[2] == self::MODEL_BOTH) {
                    switch($auto[3]) {
                        case 'function':    //  ??¨²?????????? ??¦Å?????????
                        case 'callback': // ?????????
                            $args = isset($auto[4])?(array)$auto[4]:array();
                            if(isset($data[$auto[0]])) {
                                array_unshift($args,$data[$auto[0]]);
                            }
                            if('function'==$auto[3]) {
                                $data[$auto[0]]  = call_user_func_array($auto[1], $args);
                            }else{
                                $data[$auto[0]]  =  call_user_func_array(array(&$this,$auto[1]), $args);
                            }
                            break;
                        case 'field':    // ????????¦Å?????????
                            $data[$auto[0]] = $data[$auto[1]];
                            break;
                        case 'string':
                        default: // ??????????????
                            $data[$auto[0]] = $auto[1];
                    }
                    if(false === $data[$auto[0]] )   unset($data[$auto[0]]);
                }
            }
        }
        return $data;
    }

    /**
     +----------------------------------------------------------
     * ?????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data ????????
     * @param string $type ????????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    protected function autoValidation($data,$type) {
        // ???????
        if(!empty($this->_validate)) { // ???????????????????????????????
            if($this->patchValidate) { // ??????????????
                $this->error = array();
            }
            foreach($this->_validate as $key=>$val) {
                // ????????????
                // array(field,rule,message,condition,type,when,params)
                // ?§Ø?????????????
                if(empty($val[5]) || $val[5]== self::MODEL_BOTH || $val[5]== $type ) {
                    if(0==strpos($val[2],'{%') && strpos($val[2],'}'))
                        // ???????????????? ??? {%???????} ???
                        $val[2]  =  L(substr($val[2],2,-1));
                    $val[3]  =  isset($val[3])?$val[3]:self::EXISTS_VAILIDATE;
                    $val[4]  =  isset($val[4])?$val[4]:'regex';
                    // ?§Ø????????
                    switch($val[3]) {
                        case self::MUST_VALIDATE:   // ??????? ???????????????????
                            if(false === $this->_validationField($data,$val)) 
                                return false;
                            break;
                        case self::VALUE_VAILIDATE:    // ??????????????
                            if('' != trim($data[$val[0]]))
                                if(false === $this->_validationField($data,$val)) 
                                    return false;
                            break;
                        default:    // ????????????¦Î????
                            if(isset($data[$val[0]]))
                                if(false === $this->_validationField($data,$val)) 
                                    return false;
                    }
                }
            }
            // ????????????????????
            if(!empty($this->error)) return false;
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * ????????? ??????????
     * ?????????????????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data ????????
     * @param array $val ???????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    protected function _validationField($data,$val) {
        if(false === $this->_validationFieldItem($data,$val)){
            if($this->patchValidate) {
                $this->error[$val[0]]  =  $val[2];
            }else{
                $this->error    =   $val[2];
                return false;
            }
        }
        return ;
    }

    /**
     +----------------------------------------------------------
     * ?????????????????
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param array $data ????????
     * @param array $val ???????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    protected function _validationFieldItem($data,$val) {
        switch($val[4]) {
            case 'function':// ??¨²??????????
            case 'callback':// ???¡Â??????????
                $args = isset($val[6])?(array)$val[6]:array();
                array_unshift($args,$data[$val[0]]);
                if('function'==$val[4]) {
                    return call_user_func_array($val[1], $args);
                }else{
                    return call_user_func_array(array(&$this, $val[1]), $args);
                }
            case 'confirm': // ????????????????
                return $data[$val[0]] == $data[$val[1]];
            case 'unique': // ??????????¦·?
                if(is_string($val[0]) && strpos($val[0],','))
                    $val[0]  =  explode(',',$val[0]);
                $map = array();
                if(is_array($val[0])) {
                    // ???????????
                    foreach ($val[0] as $field)
                        $map[$field]   =  $data[$field];
                }else{
                    $map[$val[0]] = $data[$val[0]];
                }
                if(!empty($data[$this->getPk()])) { // ?????????????¦·?
                    $map[$this->getPk()] = array('neq',$data[$this->getPk()]);
                }
                if($this->field($this->getPk())->where($map)->find())   return false;
                return true;
            default:  // ??¸ë?????
                return $this->check($data[$val[0]],$val[1],$val[4]);
        }
    }

    /**
     +----------------------------------------------------------
     * ??????? ??? in between equal length regex expire ip_allow ip_deny
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $value ???????
     * @param mixed $rule ???????
     * @param string $type ?????? ???????????
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     */
    public function check($value,$rule,$type='regex'){
        switch(strtolower($type)) {
            case 'in': // ????????????????¦¶??? ???????????????????
                $range   = is_array($rule)?$rule:explode(',',$rule);
                return in_array($value ,$range);
            case 'between': // ?????????????¦¶
                list($min,$max)   =  explode(',',$rule);
                return $value>=$min && $value<=$max;
            case 'equal': // ?????????????
                return $value == $rule;
            case 'length': // ???????
                $length  =  mb_strlen($value,'utf-8'); // ??????????
                if(strpos($rule,',')) { // ????????
                    list($min,$max)   =  explode(',',$rule);
                    return $length >= $min && $length <= $max;
                }else{// ???????
                    return $length == $rule;
                }
            case 'expire':
                list($start,$end)   =  explode(',',$rule);
                if(!is_numeric($start)) $start   =  strtotime($start);
                if(!is_numeric($end)) $end   =  strtotime($end);
                return $_SERVER['REQUEST_TIME'] >= $start && $_SERVER['REQUEST_TIME'] <= $end;
            case 'ip_allow': // IP ??????????
                return in_array(get_client_ip(),explode(',',$rule));
            case 'ip_deny': // IP ??????????
                return !in_array(get_client_ip(),explode(',',$rule));
            case 'regex':
            default:    // ????????????? ?????????????§Ø???????????
                // ??¸ë?????
                return $this->regex($value,$rule);
        }
    }

    /**
     +----------------------------------------------------------
     * SQL???
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $sql  SQL???
     * @param boolean $parse  ??????????SQL
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     */
    public function query($sql,$parse=false) {
        $sql  =   $this->parseSql($sql,$parse);
        return $this->db->query($sql);
    }

    /**
     +----------------------------------------------------------
     * ???SQL???
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $sql  SQL???
     * @param boolean $parse  ??????????SQL
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function execute($sql,$parse=false) {
        $sql  =   $this->parseSql($sql,$parse);
        return $this->db->execute($sql);
    }

    /**
     +----------------------------------------------------------
     * ????SQL???
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $sql  SQL???
     * @param boolean $parse  ??????????SQL
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    protected function parseSql($sql,$parse) {
        // ????????
        if($parse) {
            $options =  $this->_parseOptions();
            $sql  =   $this->db->parseSql($sql,$options);
        }else{
            if(strpos($sql,'__TABLE__'))
                $sql    =   str_replace('__TABLE__',$this->getTableName(),$sql);
        }
        $this->db->setModel($this->name);
        return $sql;
    }

    /**
     +----------------------------------------------------------
     * ?§Ý???????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param integer $linkNum  ???????
     * @param mixed $config  ????????????
     * @param array $params  ??????
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function db($linkNum,$config='',$params=array()){
        static $_db = array();
        if(!isset($_db[$linkNum])) {
            // ????????¦Ì????
            if(!empty($config) && false === strpos($config,'/')) { // ????????¨°???
                $config  =  C($config);
            }
            $_db[$linkNum]            =    Db::getInstance($config);
        }elseif(NULL === $config){
            $_db[$linkNum]->close(); // ????????????
            unset($_db[$linkNum]);
            return ;
        }
        if(!empty($params)) {
            if(is_string($params))    parse_str($params,$params);
            foreach ($params as $name=>$value){
                $this->setProperty($name,$value);
            }
        }
        // ?§Ý??????????
        $this->db   =    $_db[$linkNum];
        $this->_after_db();
        // ??¦Ì??
        if(!empty($this->name) && $this->autoCheckFields)    $this->_checkTableInfo();
        return $this;
    }
    // ??????§Ý?????????
    protected function _after_db() {}

    /**
     +----------------------------------------------------------
     * ???????????????????
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
     * ????????????????
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
     * ???????
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
     * ??????
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
     * ??????
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
     * ??????????????
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
     * ????????????????
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
     * ???????????ID
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
     * ?????????§Ö?sql???
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function getLastSql() {
        return $this->db->getLastSql($this->name);
    }
    // ????getLastSql?????? ????_sql ????
    public function _sql(){
        return $this->getLastSql();
    }

    /**
     +----------------------------------------------------------
     * ???????????
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
     * ??????????????
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
     * ????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ????
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
     * ???SQL??? join
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $join
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function join($join) {
        if(is_array($join)) {
            $this->options['join'] =  $join;
        }elseif(!empty($join)) {
            $this->options['join'][]  =   $join;
        }
        return $this;
    }

    /**
     +----------------------------------------------------------
     * ???SQL??? union
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $union
     * @param boolean $all
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function union($union,$all=false) {
        if(empty($union)) return $this;
        if($all) {
            $this->options['union']['_all']  =   true;
        }
        if(is_object($union)) {
            $union   =  get_object_vars($union);
        }
        // ???union????
        if(is_string($union) ) {
            $options =  $union;
        }elseif(is_array($union)){
            if(isset($union[0])) {
                $this->options['union']  =  array_merge($this->options['union'],$union);
                return $this;
            }else{
                $options =  $union;
            }
        }else{
            throw_exception(L('_DATA_TYPE_INVALID_'));
        }
        $this->options['union'][]  =   $options;
        return $this;
    }

    /**
     +----------------------------------------------------------
     * ???????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $key
     * @param integer $expire
     * @param string $type
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function cache($key=true,$expire='',$type=''){
        $this->options['cache']  =  array('key'=>$key,'expire'=>$expire,'type'=>$type);
        return $this;
    }

    /**
     +----------------------------------------------------------
     * ????????? ?????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $field
     * @param boolean $except ??????
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function field($field,$except=false){
        if($except) {// ??????
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
     * ?????????????
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $name ????
     * @param mixed $value ?
     +----------------------------------------------------------
     * @return Model
     +----------------------------------------------------------
     */
    public function setProperty($name,$value) {
        if(property_exists($this,$name))
            $this->$name = $value;
        return $this;
    }

}