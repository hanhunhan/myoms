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
// $Id: DbSqlsrv.class.php 2707 2012-02-04 04:22:48Z liu21st $

/**
 +------------------------------------------------------------------------------
 * Sqlsrv���ݿ������� 
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Db
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: DbSqlsrv.class.php 2707 2012-02-04 04:22:48Z liu21st $
 +------------------------------------------------------------------------------
 */
class DbSqlsrv extends Db{
    protected $selectSql  =     'SELECT T1.* FROM (SELECT ROW_NUMBER() OVER (%ORDER%) AS ROW_NUMBER, thinkphp.* FROM (SELECT %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%) AS thinkphp) AS T1 WHERE %LIMIT%';
    /**
     +----------------------------------------------------------
     * �ܹ����� ��ȡ���ݿ�������Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $config ���ݿ���������
     +----------------------------------------------------------
     */
    public function __construct($config='') {
        if ( !function_exists('sqlsrv_connect') ) {
            throw_exception(L('_NOT_SUPPERT_').':sqlsrv');
        }
        if(!empty($config)) {
            $this->config	=	$config;
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
            if(empty($config))	$config  =  $this->config;
            $host = $config['hostname'].($config['hostport']?",{$config['hostport']}":'');
            $connectInfo  =  array('Database'=>$config['dababase'],'UID'=>$config['username'],'PWD'=>$config['password']);
            $this->linkID[$linkNum] = sqlsrv_connect( $host, $connectInfo);
            if ( !$this->linkID[$linkNum] )  throw_exception($this->error());
            // ������ӳɹ�
            $this->connected =  true;
            //ע�����ݿⰲȫ��Ϣ
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
        sqlsrv_free_stmt($this->queryID);
        $this->queryID = null;
    }

    /**
     +----------------------------------------------------------
     * ִ�в�ѯ  �������ݼ�
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
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( $this->queryID ) $this->free();
        N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->queryID = sqlsrv_query($this->_linkID,$str);
        $this->debug();
        if ( false === $this->queryID ) {
            $this->error();
            return false;
        } else {
            $this->numRows = sqlsrv_num_rows($this->queryID);
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
        if ( $this->queryID ) $this->free();
        N('db_write',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->queryID=	sqlsrv_query($this->_linkID,$str);
        $this->debug();
        if ( false === $this->queryID ) {
            $this->error();
            return false;
        } else {
            $this->numRows = sqlsrv_rows_affected($this->queryID);
            $this->lastInsID = $this->mssql_insert_id();
            return $this->numRows;
        }
    }

    /**
     +----------------------------------------------------------
     * ���ڻ�ȡ�������ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     */
    public function mssql_insert_id() {
        $query  =   "SELECT @@IDENTITY as last_insert_id";
        $result =   sqlsrv_query($this->_linkID,$query);
        list($last_insert_id)   =   sqlsrv_fetch_array($result);
        sqlsrv_free_stmt($result);
        return $last_insert_id;
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
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        //����rollback ֧��
        if ($this->transTimes == 0) {
            sqlsrv_begin_transaction($this->_linkID);
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
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = sqlsrv_commit($this->_linkID);
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
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = sqlsrv_rollback($this->_linkID);
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
            while($row = sqlsrv_fetch_array($this->queryID,SQLSRV_FETCH_ASSOC))
                $result[]   =   $row;
        }
        return $result;
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
    public function getFields($tableName) {
        $result =   $this->query("SELECT   column_name,   data_type,   column_default,   is_nullable
        FROM    information_schema.tables AS t
        JOIN    information_schema.columns AS c
        ON  t.table_catalog = c.table_catalog
        AND t.table_schema  = c.table_schema
        AND t.table_name    = c.table_name
        WHERE   t.table_name = '$tableName'");
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[$val['column_name']] = array(
                    'name'    => $val['column_name'],
                    'type'    => $val['data_type'],
                    'notnull' => (bool) ($val['is_nullable'] === ''), // not null is empty, null is yes
                    'default' => $val['column_default'],
                    'primary' => false,
                    'autoinc' => false,
                );
            }
        }
        return $info;
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
    public function getTables($dbName='') {
        $result   =  $this->query("SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_TYPE = 'BASE TABLE'
            ");
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
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
        return !empty($order)?  ' ORDER BY '.$order:' ORDER BY rand()';
    }

    /**
     +----------------------------------------------------------
     * limit
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function parseLimit($limit) {
		if(empty($limit)) $limit=1;
        $limit	=	explode(',',$limit);
        if(count($limit)>1)
            $limitStr	=	'(T1.ROW_NUMBER BETWEEN '.$limit[0].' + 1 AND '.$limit[0].' + '.$limit[1].')';
		else
            $limitStr = '(T1.ROW_NUMBER BETWEEN 1 AND '.$limit[0].")";
        return $limitStr;
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
            sqlsrv_close($this->_linkID);
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
        $this->error = sqlsrv_errors();
        if($this->debug && '' != $this->queryStr){
            $this->error .= "\n [ SQL��� ] : ".$this->queryStr;
        }
        return $this->error;
    }

}