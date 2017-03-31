<?php
ini_set('session.cookie_lifetime',3600*3);
ini_set('session.gc_maxlifetime',3600*3);
	
	//echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	//echo "<br/>";
	/*
	$ip_arr = array(
		'218.94.115.131',//南京
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
	define('NO_CACHE_RUNTIME',false);	
	define('APP_NAME','loan');
	define('APP_PATH','./');
	define('CHANNELID','loan_channelid');//频道
	define('POWER','loan_power');//条口
	define('CITYEN','loan_city_en');//频道
    define('P_CRM','http://crm.house365.com:81/');
    define('P_CRM_API',"http://crm.house365.com:81/index.php/Api/");
    define('P_FGJ_API',"http://fgjit.house365.com/fgj/index.php?");

    //define('CONTRACT_API',"http://221.231.141.180:81/365tongji/admin/api/");
	//define('CONTRACTAPI',"http://221.231.141.180:81/365tongji/admin/api/is_ct_back.php");
	//define('INCOMEAPI',"http://221.231.141.180:81/365tongji/admin/api/get_ct_info.php");
	//define('CONTRACT_API',"http://172.17.1.8:81/365tongji/admin/api/");
	//define('CONTRACTAPI',"http://172.17.1.8:81/365tongji/admin/api/is_ct_back.php");
	//define('INCOMEAPI',"http://172.17.1.8:81/365tongji/admin/api/get_ct_info.php");
	define('CONTRACT_API',"http://221.231.141.180:81/365tongji_beta/admin/api/");
	define('CONTRACTAPI',"http://221.231.141.180:81/365tongji_beta/admin/api/is_ct_back.php");
	define('INCOMEAPI',"http://221.231.141.180:81/365tongji_beta/admin/api/get_ct_info.php");
    define('CONTRACT_LIST', "http://221.231.141.180:81/365tongji/admin/api/get_keywords_cts.php");  // 获取合同列表接口

	define('ZKAPI1',"http://zk.erbu.house365.com/index.php?s=/Api/Api/postPurchase");
	define('ZKAPI2',"http://zk.erbu.house365.com/index.php?s=/Api/Api/postExpense");

	//OA接口地址
	define('OA_API',"http://oa.house365.com/api/");

	//全链条精准导购系统
	define('QLTAPI',"http://192.168.105.28:9696/house365-hgs-web/rest/interface?serviceCode=Hgs");
  
	require('../ThinkPHP/ThinkPHP.php');//*/
?>