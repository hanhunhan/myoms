<?php
$host = 'localhost';
$database = 'zt';
$username = 'root';
$password = '111111';
 
$connection = mysql_connect($host, $username, $password);//连接到数据库
mysql_query("set names 'gbk'");//编码转化
if (!$connection) {
  die("could not connect to the database.\n" . mysql_error());//诊断连接错误
}
$selectedDb = mysql_select_db($database);//选择数据库
if (!$selectedDb) {
  die("could not to the database\n" . mysql_error());
}

function exequery($connection,$sql){
	return mysql_query($sql);

}

$LOGIN_USER_ID = 'wangxiaoyan1';
 

?>