<?php
	
	//echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	//echo "<br/>";
	/*
	$ip_arr = array(
		'218.94.115.131',//�Ͼ�
	);
	$ip = explode(",",$_SERVER["HTTP_X_FORWARDED_FOR"]);
	if (!in_array($ip[0],$ip_arr)){
		header("Location:http://www.house365.com/");
		die();
	}
*/


////////////////////////////////////////////////////
	ini_set("display_errors",true);
	define('APP_DEBUG', true);
	define('NO_CACHE_RUNTIME',True);	
	define('APP_NAME','loan');
	define('APP_PATH','./');
	define('CHANNELID','loan_channelid');//Ƶ��
	define('POWER','loan_power');//����
	define('CITYEN','loan_city_en');//Ƶ��
	/*�·�*/
	require('../ThinkPHP/ThinkPHP.php');//*/
	
?>