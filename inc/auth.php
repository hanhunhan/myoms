<?php
$host = 'localhost';
$database = 'zt';
$username = 'root';
$password = '111111';
 
$connection = mysql_connect($host, $username, $password);//���ӵ����ݿ�
mysql_query("set names 'gbk'");//����ת��
if (!$connection) {
  die("could not connect to the database.\n" . mysql_error());//������Ӵ���
}
$selectedDb = mysql_select_db($database);//ѡ�����ݿ�
if (!$selectedDb) {
  die("could not to the database\n" . mysql_error());
}

function exequery($connection,$sql){
	return mysql_query($sql);

}

$LOGIN_USER_ID = 'wangxiaoyan1';
 

?>