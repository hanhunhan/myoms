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
// $Id: DbIbase.class.php 2707 2012-02-04 04:22:48Z liu21st $

/**
 +------------------------------------------------------------------------------
 * Firebird���ݿ������� ���� 2007.12.28
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Db
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id: DbIbase.class.php 2707 2012-02-04 04:22:48Z liu21st $
 +------------------------------------------------------------------------------
 */
class DbIbase extends Db{

    protected $selectSql  =     'SELECT %LIMIT% %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%';
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
        if ( !extension_loaded('interbase') ) {
            throw_exception(L('_NOT_SUPPERT_').':Interbase or Firebird');
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
            $pconnect   = !empty($config['params']['persist'])? $config['params']['persist']:$this->pconnect;
            $conn = $pconnect ? 'ibase_pconnect':'ibase_connect';
            // �������˿ںŵ�socket�������
            $host = $config['hostname'].($config['hostport']?"/{$config['hostport']}":'');
            $this->linkID[$linkNum] = $conn($host.':'.$config['database'], $config['username'], $config['password'],C('DB_CHARSET'),0,3);
            if ( !$this->linkID[$linkNum]) {
                throw_exception(ibase_errmsg());
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
        ibase_free_result($this->queryID);
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
        $this->queryID = ibase_query($this->_linkID, $str);
        $this->debug();
        if ( false === $this->queryID ) {
            $this->error();
            return false;
        } else {
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
        $result =   ibase_query($this->_linkID, $str) ;
        $this->debug();
        if ( false === $result) {
            $this->error();
            return false;
        } else {
            $this->numRows = ibase_affected_rows($this->_linkID);
            $this->lastInsID =0;
            return $this->numRows;
        }
    }

    public function startTrans() {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        //����rollback ֧��
        if ($this->transTimes == 0) {
            ibase_trans( IBASE_DEFAULT, $this->_linkID);
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
            $result =  ibase_commit($this->_linkID);
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
            $result =ibase_rollback($this->_linkID);
            $this->transTimes = 0;
            if(!$result){
                throw_exception($this->error());
            }
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * BLOB�ֶν��ܺ��� Firebird����
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param $blob �����ܵ�BLOB
     +----------------------------------------------------------
     * @return ����������
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
     public function BlobDecode($blob) {
        $maxblobsize = 262144;
        $blob_data = ibase_blob_info($this->_linkID, $blob );
        $blobid = ibase_blob_open($this->_linkID, $blob );
        if( $blob_data[0] > $maxblobsize ) {
            $realblob = ibase_blob_get($blobid, $maxblobsize);
            while($string = ibase_blob_get($blobid, 8192)){
                $realblob .= $string;
            }
        } else {
            $realblob = ibase_blob_get($blobid, $blob_data[0]);
        }
        ibase_blob_close( $blobid );
        return( $realblob );
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
        while ( $row = ibase_fetch_assoc($this->queryID)) {
            $result[]   =   $row;
        }
        //���� 2007.12.30 �Զ�����BLOB�ֶ�
        //ȡBLOB�ֶ��嵥
        $bloblist = array();
        $fieldCount = ibase_num_fields($this->queryID);
        for ($i = 0; $i < $fieldCount; $i++) {
         $col_info = ibase_field_info($this->queryID, $i);
         if ($col_info['type']=='BLOB') {
           $bloblist[]=trim($col_info['name']);
         }
        }
       //�����BLOB�ֶ�,�ͽ��н��ܴ���
       if (!empty($bloblist)) {
         $i=0;
         foreach ($result as $row) {
           foreach($bloblist as $field) {
               if (!empty($row[$field])) $result[$i][$field]=$this->BlobDecode($row[$field]);
          }
          $i++;
        }
      }
     return $result;
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݱ���ֶ���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getFields($tableName) {
        $result   =  $this->query('SELECT RDB$FIELD_NAME AS FIELD, RDB$DEFAULT_VALUE AS DEFAULT1, RDB$NULL_FLAG AS NULL1 FROM RDB$RELATION_FIELDS WHERE RDB$RELATION_NAME=UPPER(\''.$tableName.'\') ORDER By RDB$FIELD_POSITION');
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $info[trim($val['FIELD'])] = array(
                    'name'    => trim($val['FIELD']),
                    'type'    => '',
                    'notnull' => (bool) ($val['NULL1'] ==1), // 1��ʾ��ΪNull
                    'default' => $val['DEFAULT1'],
                    'primary' => false,
                    'autoinc' => false,
                );
           }
      }
      //���� ȡ���ֶ�����
     $sql='select first 1 * from '. $tableName;
     $rs_temp = ibase_query ($this->_linkID, $sql);
     $fieldCount = ibase_num_fields($rs_temp);

     for ($i = 0; $i < $fieldCount; $i++)
     {
       $col_info = ibase_field_info($rs_temp, $i);
       $info[trim($col_info['name'])]['type']=$col_info['type'];
     }
     ibase_free_result ($rs_temp);

     //���� ȡ�������
     $sql='select b.rdb$field_name as FIELD_NAME from rdb$relation_constraints a join rdb$index_segments b
on a.rdb$index_name=b.rdb$index_name
where a.rdb$constraint_type=\'PRIMARY KEY\' and a.rdb$relation_name=UPPER(\''.$tableName.'\')';
     $rs_temp = ibase_query ($this->_linkID, $sql);
     while ($row=ibase_fetch_object($rs_temp)) {
      $info[trim($row->FIELD_NAME)]['primary']=True;
     }
     ibase_free_result ($rs_temp);

     return $info;
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݿ�ı���Ϣ
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getTables($dbName='') {
        $sql='SELECT DISTINCT RDB$RELATION_NAME FROM RDB$RELATION_FIELDS WHERE RDB$SYSTEM_FLAG=0';
        $result   =  $this->query($sql);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = trim(current($val));
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
            ibase_close($this->_linkID);
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
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function error() {
        $this->error = ibase_errmsg();
        if($this->debug && '' != $this->queryStr){
            $this->error .= "\n [ SQL��� ] : ".$this->queryStr;
        }
        return $this->error;
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
                 $limitStr = ' FIRST '.($limit[1]-$limit[0]).' SKIP '.$limit[0].' ';
            }else{
              $limitStr = ' FIRST '.$limit[0].' ';
            }
        }
		return $limitStr;
	}

}