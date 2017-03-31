<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ZhangXuehun <zhangxuehun@sohu.com>
// +----------------------------------------------------------------------
// $Id: DbOracle.class.php 2729 2012-02-12 04:13:34Z liu21st $

/**
+------------------------------
* Oracle���ݿ�������
+------------------------------
*/
class DbOracle extends Db{

    private $mode = OCI_COMMIT_ON_SUCCESS;
    private $table  =  '';
    protected $selectSql  =     'SELECT * FROM (SELECT thinkphp.*, rownum AS numrow FROM (SELECT  %DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%) thinkphp ) %LIMIT%';

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
        // putenv("NLS_LANG=AMERICAN_AMERICA.UTF8");//
		putenv("NLS_LANG=SIMPLIFIED CHINESE_CHINA.ZHS16GBK");
        if ( !extension_loaded('oci8') ) {
            throw_exception(L('_NOT_SUPPERT_').'oracle');
        }
        if(!empty($config)) {
            $this->config        =        $config;
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
            if(empty($config))  $config = $this->config;
            $pconnect   = !empty($config['params']['persist'])? $config['params']['persist']:$this->pconnect;
            $conn = $pconnect ? 'oci_pconnect':'oci_new_connect';
            $this->linkID[$linkNum] = $conn($config['username'], $config['password'],$config['database']);//modify by wyfeng at 2008.12.19

            if (!$this->linkID[$linkNum]){
                $error = $this->error(false);
                throw_exception($error["message"], '', $error["code"]);
            }
            // ������ӳɹ�
            $this->connected = true;
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
        oci_free_statement($this->queryID);
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
        //��������ģʽ
        ###$this->mode = OCI_COMMIT_ON_SUCCESS;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( $this->queryID ) $this->free();
        N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->queryID = oci_parse($this->_linkID,$str);
        $this->debug();
        if (false === oci_execute($this->queryID, $this->mode)) {
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
        // �ж���������
        $flag = false;
        if(preg_match("/^\s*(INSERT\s+INTO)\s+(\w+)\s+/i", $this->queryStr, $match)) {
            $this->table = C("DB_SEQUENCE_PREFIX") .str_ireplace(C("DB_PREFIX"), "", $match[2]);
            $flag = (boolean)$this->query("SELECT * FROM user_sequences WHERE sequence_name='" . strtoupper($this->table) . "'");
        }//modify by wyfeng at 2009.08.28
		  //echo $str;
        //��������ģʽ
       ### $this->mode = OCI_COMMIT_ON_SUCCESS;
        //�ͷ�ǰ�εĲ�ѯ���
        if ( $this->queryID ) $this->free();
        N('db_write',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $stmt = oci_parse($this->_linkID,$str);
        $this->debug();
        if (false === oci_execute($stmt, $this->mode)) {
            $this->error();
            return false;
        } else { 
            $this->numRows = oci_num_rows($stmt);
            $this->lastInsID = $flag?$this->insertLastId():0;//modify by wyfeng at 2009.08.28
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
            $this->mode = OCI_DEFAULT;
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
    public function commit(){
        if ($this->transTimes > 0) {
            $result = oci_commit($this->_linkID);
            if(!$result){
                throw_exception($this->error());
				return false;// huanghonghe
            }
			$this->mode = OCI_COMMIT_ON_SUCCESS;//huanghonghe
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
     public function rollback(){
        if ($this->transTimes > 0) {
            $result = oci_rollback($this->_linkID);
            if(!$result){
                throw_exception($this->error());
				return false;// huanghonghe
            }
			$this->mode = OCI_COMMIT_ON_SUCCESS;//huanghonghe
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
        $this->numRows = oci_fetch_all($this->queryID, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
		//add by wyfeng at 2008-12-23 ǿ�ƽ��ֶ���ת��ΪСд�������Model�ຯ����count��
        if(C("DB_CASE_LOWER")) {
            foreach($result as $k=>$v) {
                $result[$k] = array_change_key_case($result[$k], CASE_LOWER);
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
        $result = $this->query("select a.column_name,data_type,decode(nullable,'Y',0,1) notnull,data_default,decode(a.column_name,b.column_name,1,0) pk "
                  ."from user_tab_columns a,(select column_name from user_constraints c,user_cons_columns col "
          ."where c.constraint_name=col.constraint_name and c.constraint_type='P'and c.table_name='".strtoupper($tableName)
          ."') b where table_name='".strtoupper($tableName)."' and a.column_name=b.column_name(+)");
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {  
                $info[$val['COLUMN_NAME']] = array(
                    'name'    => $val['COLUMN_NAME'],
                    'type'    => strtolower($val['DATA_TYPE']),
                    'notnull' => $val['NOTNULL'],
                    'default' => $val['DATA_DEFAULT'],
                    'primary' => $val['PK'],
                    'autoinc' => $val['PK'],
                );
            }
        } 
        return $info;
    }

    /**
     +----------------------------------------------------------
     * ȡ�����ݿ�ı���Ϣ����ʱʵ��ȡ���û�����Ϣ��
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function getTables($dbName='') {
        $result = $this->query("select table_name from user_tables");
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
        if($this->_linkID){
            oci_close($this->_linkID);
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
     public function error($result = true) {
        if($result){
           $this->error = oci_error($this->queryID);
        }elseif(!$this->_linkID){
            $this->error = oci_error();
        }else{
            $this->error = oci_error($this->_linkID);
        }
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
     * @param mix $str  SQLָ��
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function escapeString($str) {
        return str_ireplace("'", "''", $str);
    }

    /**
     +----------------------------------------------------------
     * ��ȡ������id ,�������ڲ�������+�������������ID�ķ�ʽ
     * ��config.php��ָ��
     'DB_TRIGGER_PREFIX'	=>	'tr_',
     'DB_SEQUENCE_PREFIX' =>	'ts_',
     * eg:�� tb_user
     ���tb_user������Ϊ��
     -- Create sequence
     create sequence TS_USER
     minvalue 1
     maxvalue 999999999999999999999999999
     start with 1
     increment by 1
     nocache;
     ���tb_user,ts_user�Ĵ�����Ϊ��
     create or replace trigger TR_USER
     before insert on "TB_USER"
     for each row
     begin
     select "TS_USER".nextval into :NEW.ID from dual;
     end;
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return integer
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    public function insertLastId() {
        if(empty($this->table)) {
            return 0;
        }
        $sequenceName = $this->table;
        $vo = $this->query("SELECT {$sequenceName}.currval currval FROM dual");
        return $vo?$vo[0]["CURRVAL"]:0;
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
            $limit	=	explode(',',$limit);
            if(count($limit)>1)
                $limitStr = "(numrow>" . $limit[0] . ") AND (numrow<=" . ($limit[0]+$limit[1]) . ")";
            else
                $limitStr = "(numrow>0 AND numrow<=".$limit[0].")";
        }
        return $limitStr?' WHERE '.$limitStr:'';
    }
}