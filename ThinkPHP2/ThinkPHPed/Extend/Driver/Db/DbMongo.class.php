<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: DbMongo.class.php 2570 2012-01-10 13:30:39Z liu21st $

/**
 +------------------------------------------------------------------------------
 * Mongo���ݿ������� ��Ҫ���MongoModelʹ��
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Db
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: DbMongo.class.php 2570 2012-01-10 13:30:39Z liu21st $
 +------------------------------------------------------------------------------
 */
class DbMongo extends Db{

    protected $_mongo = null; // MongoDb Object
    protected $_collection    = null; // MongoCollection Object
    protected $_dbName = ''; // dbName
    protected $_collectionName = ''; // collectionName
    protected $_cursor   =  null; // MongoCursor Object
    protected $comparison      = array('neq'=>'ne','ne'=>'ne','gt'=>'gt','egt'=>'gte','gte'=>'gte','lt'=>'lt','elt'=>'lte','lte'=>'lte','in'=>'in','not in'=>'nin','nin'=>'nin');

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
        if ( !class_exists('mongo') ) {
            throw_exception(L('_NOT_SUPPERT_').':mongo');
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
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            $host = 'mongodb://'.($config['username']?"{$config['username']}":'').($config['password']?":{$config['password']}@":'').$config['hostname'].($config['hostport']?":{$config['hostport']}":'');
            try{
                $this->linkID[$linkNum] = new mongo( $host,$config['params']);
            }catch (MongoConnectionException $e){
                throw_exception($e->getmessage());
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
     * �л���ǰ������Db��Collection
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $collection  collection
     * @param string $db  db
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function switchCollection($collection,$db=''){
        // ��ǰû������ �����Ƚ������ݿ�����
        if ( !$this->_linkID ) $this->initConnect(false);
        try{
            if(!empty($db)) { // ����Db���л����ݿ�
                // ��ǰMongoDb����
                $this->_dbName  =  $db;
                $this->_mongo = $this->_linkID->selectDb($db);
            }
            // ��ǰMongoCollection����
            if($this->debug) {
                $this->queryStr   =  $this->_dbName.'.getCollection('.$collection.')';
            }
            if($this->_collectionName != $collection) {
                N('db_read',1);
                // ��¼��ʼִ��ʱ��
                G('queryStartTime');
                $this->_collection =  $this->_mongo->selectCollection($collection);
                $this->debug();
                $this->_collectionName  = $collection; // ��¼��ǰCollection����
            }
        }catch (MongoException $e){
            throw_exception($e->getMessage());
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
        $this->_cursor = null;
    }

    /**
     +----------------------------------------------------------
     * ִ������
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $command  ָ��
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function command($command=array()) {
        N('db_write',1);
        $this->queryStr = 'command:'.json_encode($command);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $result   = $this->_mongo->command($command);
        $this->debug();
        if(!$result['ok']) {
            throw_exception($result['errmsg']);
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * ִ�����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $code  sqlָ��
     * @param array $args  ����
     +----------------------------------------------------------
     * @return mixed
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function execute($code,$args=array()) {
        N('db_write',1);
        $this->queryStr = 'execute:'.$code;
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $result   = $this->_mongo->execute($code,$args);
        $this->debug();
        if($result['ok']) {
            return $result['retval'];
        }else{
            throw_exception($result['errmsg']);
        }
    }

    /**
     +----------------------------------------------------------
     * �ر����ݿ�
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function close() {
        if($this->_linkID) {
            $this->_linkID->close();
            $this->_linkID = null;
            $this->_mongo = null;
            $this->_collection =  null;
            $this->_cursor = null;
        }
    }

    /**
     +----------------------------------------------------------
     * ���ݿ������Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function error() {
        $this->error = $this->_mongo->lastError();
        return $this->error;
    }

    /**
     +----------------------------------------------------------
     * �����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param mixed $data ����
     * @param array $options �������ʽ
     * @param boolean $replace �Ƿ�replace
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function insert($data,$options=array(),$replace=false) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        N('db_write',1);
        if($this->debug) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.insert(';
            $this->queryStr   .= $data?json_encode($data):'{}';
            $this->queryStr   .= ')';
        }
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $result =  $replace?   $this->_collection->save($data,true):  $this->_collection->insert($data,true);
            $this->debug();
            if($result) {
               $_id    = $data['_id'];
                if(is_object($_id)) {
                    $_id = $_id->__toString();
                }
               $this->lastInsID    = $_id;
            }
            return $result;
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
    }

    /**
     +----------------------------------------------------------
     * ���������¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $dataList ����
     * @param array $options �������ʽ
     +----------------------------------------------------------
     * @return bool
     +----------------------------------------------------------
     */
    public function insertAll($dataList,$options=array()) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        N('db_write',1);
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
           $result =  $this->_collection->batchInsert($dataList);
           $this->debug();
           return $result;
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
    }

    /**
     +----------------------------------------------------------
     * ������һ����¼ID ����������MongoId����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $pk ������
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function mongo_next_id($pk) {
        N('db_read',1);
        if($this->debug) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.find({},{'.$pk.':1}).sort({'.$pk.':-1}).limit(1)';
        }
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $result   =  $this->_collection->find(array(),array($pk=>1))->sort(array($pk=>-1))->limit(1);
            $this->debug();
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
        $data = $result->getNext();
        return isset($data[$pk])?$data[$pk]+1:1;
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
     * @return bool
     +----------------------------------------------------------
     */
    public function update($data,$options) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        N('db_write',1);
        $query   = $this->parseWhere($options['where']);
        $set  =  $this->parseSet($data);
        if($this->debug) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.update(';
            $this->queryStr   .= $query?json_encode($query):'{}';
            $this->queryStr   .=  ','.json_encode($set).')';
        }
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $result   = $this->_collection->update($query,$set,array("multiple" => true));
            $this->debug();
            return $result;
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
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
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        $query   = $this->parseWhere($options['where']);
        N('db_write',1);
        if($this->debug) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.remove('.json_encode($query).')';
        }
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $result   = $this->_collection->remove($query);
            $this->debug();
            return $result;
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
    }

    /**
     +----------------------------------------------------------
     * ��ռ�¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return false | integer
     +----------------------------------------------------------
     */
    public function clear($options=array()){
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        N('db_write',1);
        if($this->debug) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.remove({})';
        }
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $result   =  $this->_collection->drop();
            $this->debug();
            return $result;
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
    }

    /**
     +----------------------------------------------------------
     * ���Ҽ�¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return iterator
     +----------------------------------------------------------
     */
    public function select($options=array()) {
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        $cache  =  isset($options['cache'])?$options['cache']:false;
        if($cache) { // ��ѯ������
            $key =  is_string($cache['key'])?$cache['key']:md5(serialize($options));
            $value   =  S($key,'','',$cache['type']);
            if(false !== $value) {
                return $value;
            }
        }
        N('db_query',1);
        $query  =  $this->parseWhere($options['where']);
        $field =  $this->parseField($options['field']);
        try{
            if($this->debug) {
                $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.find(';
                $this->queryStr  .=  $query? json_encode($query):'{}';
                $this->queryStr  .=  $field? ','.json_encode($field):'';
                $this->queryStr  .=  ')';
            }
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $_cursor   = $this->_collection->find($query,$field);
            if($options['order']) {
                $order   =  $this->parseOrder($options['order']);
                if($this->debug) {
                    $this->queryStr .= '.sort('.json_encode($order).')';
                }
                $_cursor =  $_cursor->sort($order);
            }
            if(isset($options['page'])) { // ����ҳ������limit
                if(strpos($options['page'],',')) {
                    list($page,$length) =  explode(',',$options['page']);
                }else{
                    $page    = $options['page'];
                }
                $page    = $page?$page:1;
                $length = isset($length)?$length:(is_numeric($options['limit'])?$options['limit']:20);
                $offset  =  $length*((int)$page-1);
                $options['limit'] =  $offset.','.$length;
            }
            if(isset($options['limit'])) {
                list($offset,$length) =  $this->parseLimit($options['limit']);
                if(!empty($offset)) {
                    if($this->debug) {
                        $this->queryStr .= '.skip('.intval($offset).')';
                    }
                    $_cursor =  $_cursor->skip(intval($offset));
                }
                if($this->debug) {
                    $this->queryStr .= '.limit('.intval($length).')';
                }
                $_cursor =  $_cursor->limit(intval($length));
            }
            $this->debug();
            $this->_cursor =  $_cursor;
            $resultSet  =  iterator_to_array($_cursor);
            if($cache && $resultSet ) { // ��ѯ����д��
                S($key,$resultSet,$cache['expire'],$cache['type']);
            }
            return $resultSet;
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
    }

    /**
     +----------------------------------------------------------
     * ����ĳ����¼
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function find($options=array()){
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        $cache  =  isset($options['cache'])?$options['cache']:false;
        if($cache) { // ��ѯ������
            $key =  is_string($cache['key'])?$cache['key']:md5(serialize($options));
            $value   =  S($key,'','',$cache['type']);
            if(false !== $value) {
                return $value;
            }
        }
        N('db_query',1);
        $query  =  $this->parseWhere($options['where']);
        $fields    = $this->parseField($options['field']);
        if($this->debug) {
            $this->queryStr = $this->_dbName.'.'.$this->_collectionName.'.fineOne(';
            $this->queryStr .= $query?json_encode($query):'{}';
            $this->queryStr .= $fields?','.json_encode($fields):'';
            $this->queryStr .= ')';
        }
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $result   = $this->_collection->findOne($query,$fields);
            $this->debug();
            if($cache && $result ) { // ��ѯ����д��
                S($key,$result,$cache['expire'],$cache['type']);
            }
            return $result;
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
    }

    /**
     +----------------------------------------------------------
     * ͳ�Ƽ�¼��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $options ���ʽ
     +----------------------------------------------------------
     * @return iterator
     +----------------------------------------------------------
     */
    public function count($options=array()){
        if(isset($options['table'])) {
            $this->switchCollection($options['table']);
        }
        N('db_query',1);
        $query  =  $this->parseWhere($options['where']);
        if($this->debug) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName;
            $this->queryStr   .= $query?'.find('.json_encode($query).')':'';
            $this->queryStr   .= '.count()';
        }
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $count   = $this->_collection->count($query);
            $this->debug();
            return $count;
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
    }

    public function group($keys,$initial,$reduce,$options=array()){
        $this->_collection->group($keys,$initial,$reduce,$options);
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݱ���ֶ���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function getFields($collection=''){
        if(!empty($collection) && $collection != $this->_collectionName) {
            $this->switchCollection($collection);
        }
        N('db_query',1);
        if($this->debug) {
            $this->queryStr   =  $this->_dbName.'.'.$this->_collectionName.'.findOne()';
        }
        try{
            // ��¼��ʼִ��ʱ��
            G('queryStartTime');
            $result   =  $this->_collection->findOne();
            $this->debug();
        } catch (MongoCursorException $e) {
            throw_exception($e->getMessage());
        }
        if($result) { // ��������������ֶ�
            $info =  array();
            foreach ($result as $key=>$val){
                $info[$key] =  array(
                    'name'=>$key,
                    'type'=>getType($val),
                    );
            }
            return $info;
        }
        // ��ʱû������ ����false
        return false;
    }

    /**
     +----------------------------------------------------------
     * ȡ�õ�ǰ���ݿ��collection��Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function getTables(){
        if($this->debug) {
            $this->queryStr   =  $this->_dbName.'.getCollenctionNames()';
        }
        N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $list   = $this->_mongo->listCollections();
        $this->debug();
        $info =  array();
        foreach ($list as $collection){
            $info[]   =  $collection->getName();
        }
        return $info;
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
        $result   =  array();
        foreach ($data as $key=>$val){
            if(is_array($val)) {
                switch($val[0]) {
                    case 'inc':
                        $result['$inc'][$key]  =  (int)$val[1];
                        break;
                    case 'set':
                    case 'unset':
                    case 'push':
                    case 'pushall':
                    case 'addtoset':
                    case 'pop':
                    case 'pull':
                    case 'pullall':
                        $result['$'.$val[0]][$key] = $val[1];
                        break;
                    default:
                        $result['$set'][$key] =  $val;
                }
            }else{
                $result['$set'][$key]    = $val;
            }
        }
        return $result;
    }

    /**
     +----------------------------------------------------------
     * order����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $order
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function parseOrder($order) {
        if(is_string($order)) {
            $array   =  explode(',',$order);
            $order   =  array();
            foreach ($array as $key=>$val){
                $arr  =  explode(' ',trim($val));
                if(isset($arr[1])) {
                    $arr[1]  =  $arr[1]=='asc'?1:-1;
                }else{
                    $arr[1]  =  1;
                }
                $order[$arr[0]]    = $arr[1];
            }
        }
        return $order;
    }

    /**
     +----------------------------------------------------------
     * limit����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $limit
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function parseLimit($limit) {
        if(strpos($limit,',')) {
            $array  =  explode(',',$limit);
        }else{
            $array   =  array(0,$limit);
        }
        return $array;
    }

    /**
     +----------------------------------------------------------
     * field����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $fields
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseField($fields){
        if(empty($fields)) {
            $fields    = array();
        }
        if(is_string($fields)) {
            $fields    = explode(',',$fields);
        }
        return $fields;
    }

    /**
     +----------------------------------------------------------
     * where����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param mixed $where
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    public function parseWhere($where){
        $query   = array();
        foreach ($where as $key=>$val){
            if('_id' != $key && 0===strpos($key,'_')) {
                // ���������������ʽ
                $query   = $this->parseThinkWhere($key,$val);
            }else{
                // ��ѯ�ֶεİ�ȫ����
                if(!preg_match('/^[A-Z_\|\&\-.a-z0-9]+$/',trim($key))){
                    throw_exception(L('_ERROR_QUERY_').':'.$key);
                }
                $key = trim($key);
                if(strpos($key,'|')) {
                    $array   =  explode('|',$key);
                    $str   = array();
                    foreach ($array as $k){
                        $str[]   = $this->parseWhereItem($k,$val);
                    }
                    $query['$or'] =    $str;
                }elseif(strpos($key,'&')){
                    $array   =  explode('&',$key);
                    $str   = array();
                    foreach ($array as $k){
                        $str[]   = $this->parseWhereItem($k,$val);
                    }
                    $query   = array_merge($query,$str);
                }else{
                    $str   = $this->parseWhereItem($key,$val);
                    $query   = array_merge($query,$str);
                }
            }
        }
        return $query;
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
        $query   = array();
        switch($key) {
            case '_query': // �ַ���ģʽ��ѯ����
                parse_str($val,$query);
                if(isset($query['_logic']) && strtolower($query['_logic']) == 'or' ) {
                    unset($query['_logic']);
                    $query['$or']   =  $query;
                }
                break;
            case '_string':// MongoCode��ѯ
                $query['$where']  = new MongoCode($val);
                break;
        }
        return $query;
    }

    /**
     +----------------------------------------------------------
     * where�ӵ�Ԫ����
     +----------------------------------------------------------
     * @access protected
     +----------------------------------------------------------
     * @param string $key
     * @param mixed $val
     +----------------------------------------------------------
     * @return array
     +----------------------------------------------------------
     */
    protected function parseWhereItem($key,$val) {
        $query   = array();
        if(is_array($val)) {
            if(is_string($val[0])) {
                $con  =  strtolower($val[0]);
                if(in_array($con,array('neq','ne','gt','egt','gte','lt','lte','elt'))) { // �Ƚ�����
                    $k = '$'.$this->comparison[$con];
                    $query[$key]  =  array($k=>$val[1]);
                }elseif('like'== $con){ // ģ����ѯ ��������ʽ
                    $query[$key]  =  new MongoRegex("/".$val[1]."/");  
                }elseif('mod'==$con){ // mod ��ѯ
                    $query[$key]   =  array('$mod'=>$val[1]);
                }elseif('regex'==$con){ // �����ѯ
                    $query[$key]  =  new MongoRegex($val[1]);
                }elseif(in_array($con,array('in','nin','not in'))){ // IN NIN ����
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $k = '$'.$this->comparison[$con];
                    $query[$key]  =  array($k=>$data);
                }elseif('all'==$con){ // ��������ָ������
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$all'=>$data);
                }elseif('between'==$con){ // BETWEEN����
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$gte'=>$data[0],'$lte'=>$data[1]);
                }elseif('not between'==$con){
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $query[$key]  =  array('$lt'=>$data[0],'$gt'=>$data[1]);
                }elseif('exp'==$con){ // ���ʽ��ѯ
                    $query['$where']  = new MongoCode($val[1]);
                }elseif('exists'==$con){ // �ֶ��Ƿ����
                    $query[$key]  =array('$exists'=>(bool)$val[1]);
                }elseif('size'==$con){ // �������Դ�С
                    $query[$key]  =array('$size'=>intval($val[1]));
                }elseif('type'==$con){ // �����ֶ����� 1 ������ 2 �ַ��� 3 �������MongoDBRef 5 MongoBinData 7 MongoId 8 ������ 9 MongoDate 10 NULL 15 MongoCode 16 32λ���� 17 MongoTimestamp 18 MongoInt64 ���������Ļ��ж�Ԫ�ص�����
                    $query[$key]  =array('$type'=>intval($val[1]));
                }else{
                    $query[$key]  =  $val;
                }
                return $query;
            }
        }
        $query[$key]  =  $val;
        return $query;
    }
}