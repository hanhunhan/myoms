<?php
ini_set("display_errors",true);

define('APP_DEBUG',true);
define('NO_CACHE_RUNTIME',True);
define('APP_NAME','loan');
define('APP_PATH','./');
define('CHANNELID','loan_channelid');//频道
define('POWER','loan_power');//条口
define('CITYEN','loan_city_en');//频道
//CRM接口地址
define('P_CRM','http://crm.house365.com:81/');
//CRM API接口地址
define('P_CRM_API',"http://crm.house365.com:81/index.php/Api/");
//房管家接口地址
define('P_FGJ_API',"http://fgjit.house365.com/fgj/index.php?");
//OA接口地址
define('OA_API',"http://oa.house365.com/api/");


//引入框架
require('../ThinkPHP/ThinkPHP.php');
?>