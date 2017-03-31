<?php

class SaeMysql extends SaeObject {

    static $link;
    static $charset;

    function __construct() {
        global $sae_config;
        self::$charset = $sae_config['db_charset'];
        $this->connect();
        parent::__construct();
    }

    //�������ݿ�
    protected function connect() {
        global $sae_config;
        if(empty($sae_config['db_name'])) die(Imit_L('_SAE_PLEASE_CONFIG_DB_'));
        self::$link = mysql_connect(SAE_MYSQL_HOST_M, SAE_MYSQL_USER, SAE_MYSQL_PASS) or die(Imit_L('_SAE_CONNECT_DB_ERR_'));
        mysql_select_db(SAE_MYSQL_DB, self::$link);
        mysql_query("set names " . self::$charset, self::$link);
        if (!mysql_select_db(SAE_MYSQL_DB, self::$link)) {
            //������ݿⲻ���ڣ��Զ�����
            mysql_query('create database ' . SAE_MYSQL_DB, self::$link);
            mysql_select_db(SAE_MYSQL_DB, self::$link) or Imit_L('_SAE_DATABASE_NOT_EXIST_');
        }
    }

    //����Ӱ������
    public function affectedRows() {
        return mysql_affected_rows(self::$link);
    }

    //�ر����ݿ�
    public function closeDb() {
        mysql_close(self::$link);
    }

    //escape
    public function escape($str) {
        return mysql_real_escape_string($str, self::$link);
    }

    //������ݣ���������
    public function getData($sql) {
        $this->last_sql = $sql;
        $result = mysql_query($sql, self::$link);
        if(!$result){
            return false;
        }
        $this->save_error();
        $data = array();
        while ($arr = mysql_fetch_array($result)) {
            $data[] = $arr;
        }
        mysql_free_result($result);
        return $data;
    }

    //���ص�һ������
    public function getLine($sql) {
        $data = $this->getData($sql);
        if ($data) {
            return @reset($data);
        } else {
            return false;
        }
    }

    //���ص�һ����¼�ĵ�һ���ֶ�ֵ
    public function getVar($sql) {
        $data = $this->getLine($sql);
        if ($data) {
            return $data[@reset(@array_keys($data))];
        } else {
            return false;
        }
    }

    //�������һ��id
    public function lastId() {
        return mysql_insert_id(self::$link);
    }

    //����sql���
    public function runSql($sql) {
        $ret = mysql_query($sql);
        $this->save_error();
        return $ret;
    }

    //������Ŀ��
    public function setAppname($appname) {
        
    }

    //�����ַ���
    public function setCharset($charset) {
        self::$charset = $charset;
        mysql_query("set names " . self::$charset, self::$link);
    }

    //���ö˿�
    public function setPort($port) {
        
    }

    protected function save_error() {
        $this->errmsg = mysql_error(self::$link);
        $this->errno = mysql_errno(self::$link);
    }

}
