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
/*

$depr = '/';

$path   = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';

if(!empty($path)) {

$params = explode($depr,trim($path,$depr));

}

!empty($params)?$_GET['g']=array_shift($params):"";

!empty($params)?$_GET['m']=array_shift($params):"";

!empty($params)?$_GET['a']=array_shift($params):"";

if(count($params)>1) {

// ����ʣ����� ������GET��ʽ��ȡ

preg_replace('@(\w+),([^,\/]+)@e', '$_GET[\'\\1\']="\\2";', implode(',',$params));

}*/
	ini_set("display_errors",true);
	//define('MODE_NAME','cli');
	define('APP_DEBUG',false);
	define('NO_CACHE_RUNTIME',false);	
	define('APP_NAME','migration');
	define('APP_PATH','./');
	//define('CHANNELID','loan_channelid');//Ƶ��
	//define('POWER','loan_power');//����
	//define('CITYEN','loan_city_en');//Ƶ��
    //define('P_CRM','http://crm.house365.com:81/');
   // define('P_CRM_API',"http://crm.house365.com:81/index.php/Api/");
    //define('P_FGJ_API',"http://fgjit.house365.com/fgj/index.php?");
  
	require('../ThinkPHP/ThinkPHP.php');//*/
?>