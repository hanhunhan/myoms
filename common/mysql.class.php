<?php
if(!session_id()) session_start();
/** 
*���´����������ݿ������ķ�װ
* 
* @author rex<rex.sp.li@aliyun.com> 
* @version 1.0
* @since 2015
*/


class Mysql{

//���ݿ����ӷ���ֵ
private $conn;

/**
* [���캯��������ֵ��$conn]
* @param [string] $hostname [������]
* @param [string] $username[�û���]
* @param [string] $password[����]
* @param [string] $dbname[���ݿ���]
* @param [string] $charset[�ַ���]
* @return [null]

*/

function __construct($hostname,$username,$password,$dbname,$charset='gbk'){
	$conn=@mysql_connect($hostname,$username,$password);
	if(!$conn){
		echo '����ʧ�ܣ�����ϵ����Ա';
		exit;
	}
	$this->conn = $conn;
	$res = mysql_select_db($dbname);
	if(!$res){
	echo '����ʧ�ܣ�����ϵ����Ա';
	exit;
	}
	mysql_set_charset($charset);
}
function __destruct(){
	mysql_close();
}
/**
* [getAll ��ȡ������Ϣ]
* @param [string] $sql [sql���]
* @return [array] [���ض�ά����]
*/
function getAll($sql){
	$result = mysql_query($sql,$this->conn);
	$data = array();
	if($result && mysql_num_rows($result)>0){
		while($row = mysql_fetch_assoc($result)){
		$data[] = $row;
		}
	}
	return $data;
}
/**
* [getOne ��ȡ��������]
* @param [string] $sql [sql���]
* @return [array] [����һά����]
*/
function getOne($sql){
	$result = mysql_query($sql,$this->conn);
	$data = array();
	if($result && mysql_num_rows($result)>0){
		$data = mysql_fetch_assoc($result);
	}
	return $data;
}

/**
* [getOne ��ȡ��������]
* @param [string] $table [����]
* @param [string] $data [���ֶ������������Ե���ֵ��һά����]
* @return [type] [����false���߲������ݵ�id]
*/

function insert($table,$data){
	$str = '';
	$str .="INSERT INTO `$table` ";
	$str .="(`".implode("`,`",array_keys($data))."`) "; 
	$str .=" VALUES ";
	$str .= "('".implode("','",$data)."')";
	$res = mysql_query($str,$this->conn);
	if($res && mysql_affected_rows()>0){
		return mysql_insert_id();
	}else{
		return false;
	}
}
/**
* [update �������ݿ�]
* @param [string] $table [����]
* @param [array] $data [���µ����ݣ����ֶ������������Ե���ֵ��һά����]
* @param [string] $where [���������ֶ�����=���ֶ����ԡ�]
* @return [type] [���³ɹ�����Ӱ�������������ʧ�ܷ���false]
*/
function update($table,$data,$where){
	$sql = 'UPDATE '.$table.' SET ';
	foreach($data as $key => $value){
	$sql .= "`{$key}`='{$value}',";
	}
	$sql = rtrim($sql,',');
	$sql .= " WHERE $where";
	$res = mysql_query($sql,$this->conn);
	if($res && mysql_affected_rows()){
		return mysql_affected_rows();
	}else{
		return false;
	}
}

/**
* [delete ɾ������]
* @param [string] $table [����]
* @param [string] $where [���������ֶ�����=���ֶ����ԡ�]
* @return [type] [�ɹ�����Ӱ���������ʧ�ܷ���false]
*/
function del($table,$where){
	$sql = "DELETE FROM `{$table}` WHERE {$where}";
	$res = mysql_query($sql,$this->conn);
	if($res && mysql_affected_rows()){
		return mysql_affected_rows();
	}else{
		return false;
	}
}
}


//���ô������
$hostname='localhost';
$username='root';
$password='idontcare';
$dbname='nj_project';
$charset = 'gbk';

//ʵ��������

$db = new Mysql($hostname,$username,$password,$dbname);
$DB_PREFIX = 'zzfw_2016_';
 