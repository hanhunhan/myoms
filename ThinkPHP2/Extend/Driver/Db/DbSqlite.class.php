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
// $Id: DbSqlite.class.php 2729 2012-02-12 04:13:34Z liu21st $

/**
 +-------------------------------
 * Sqlite���ݿ�������
 +-------------------------------
 */
class DbSqlite extends Db {

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
        if ( !extension_loaded('sqlite') ) {
            throw_exception(L('_NOT_SUPPERT_').':sqlite');
        }
        if(!empty($config)) {
            if(!isset($config['mode'])) {
                $config['mode']	=	0666;
            }
            $this->config	=	$config;
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
            if(empty($config))	$config	=	$this->config;
            $pconnect   = !empty($config['params']['persist'])? $config['params']['persist']:$this->pconnect;
            $conn = $pconnect ? 'sqlite_popen':'sqlite_open';
            $this->linkID[$linkNum] = $conn($config['database'],$config['mode']);
            if ( !$this->linkID[$linkNum]) {
                throw_exception(sqlite_error_string());
            }
            // ������ӳɹ�
            $this->connected	=	true;
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
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( $this->queryID ) $this->free();
        N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->queryID = sqlite_query($this->_linkID,$str);
        $this->debug();
        if ( false === $this->queryID ) {
            $this->error();
            return false;
        } else {
            $this->numRows = sqlite_num_rows($this->queryID);
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
        $result	=	sqlite_exec($this->_linkID,$str);
        $this->debug();
        if ( false === $result ) {
            $this->error();
            return false;
        } else {
            $this->numRows = sqlite_changes($this->_linkID);
            $this->lastInsID = sqlite_last_insert_rowid($this->_linkID);
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
            sqlite_query($this->_linkID,'BEGIN TRANSACTION');
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
            $result = sqlite_query($this->_linkID,'COMMIT TRANSACTION');
            if(!$result){
                throw_exception($this->error());
            }
            $this->transTimes = 0;
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
            $result = sqlite_query($this->_linkID,'ROLLBACK TRANSACTION');
            if(!$result){
                throw_exception($this->error());
            }
            $this->transTimes = 0;
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
            for($i=0;$i<$this->numRows ;$i++ ){
                // �������鼯
                $result[$i] = sqlite_fetch_array($this->queryID,SQLITE_ASSOC);
            }
            sqlite_seek($this->queryID,0);
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
        $result =   $this->query('PRAGMA table_info( '.$tableName.' )');
        $info   =   array();
        if($result){
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
     * @return array
     +----------------------------------------------------------
     */
    public function getTables($dbName='') {
        $result =   $this->query("SELECT name FROM sqlite_master WHERE type='table' "
             . "UNION ALL SELECT name FROM sqlite_temp_master "
             . "WHERE type='table' ORDER BY name");
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
     */
    public function close() {
        if ($this->_linkID){
            sqlite_close($this->_linkID);
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
        $this->error = sqlite_error_string(sqlite_last_error($this->_linkID));
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
     * @param string $str  SQLָ��
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function escapeString($str) {
        return sqlite_escape_string($str);
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
        $limitStr    = '';
        if(!empty($limit)) {
            $limit  =   explode(',',$limit);
            if(count($limit)>1) {
                $limitStr .= ' LIMIT '.$limit[1].' OFFSET '.$limit[0].' ';
            }else{
                $limitStr .= ' LIMIT '.$limit[0].' ';
            }
        }
        return $limitStr;
    }

}