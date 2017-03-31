<?php
// +----------------------------------------------------------------------
// | ģ�������ݿ����sqlite3
// +----------------------------------------------------------------------
// | Author: luofei614<www.3g4k.com>
// +----------------------------------------------------------------------
// $Id: ImitSqlite.class.php 2766 2012-02-20 15:58:21Z luofei614@gmail.com $
class ImitSqlite extends SQLite3{
    function __construct()
    {
        $this->open(dirname(__FILE__).'/sae.db');
    }
    //������ݣ���������
    public function getData($sql){
        $this->last_sql = $sql;
        $result=$this->query($sql);
        if(!$result){
            return false;
        }
        $data=array();
        while($arr=$result->fetchArray(SQLITE3_ASSOC)){
            $data[]=$arr;
        }
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
    //����sql���
    public function runSql($sql) {
        return $this->exec($sql);
    }

}