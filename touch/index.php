<?php
ini_set("display_errors",true);

define('APP_DEBUG',true);
define('NO_CACHE_RUNTIME',True);
define('APP_NAME','loan');
define('APP_PATH','./');
define('CHANNELID','loan_channelid');//Ƶ��
define('POWER','loan_power');//����
define('CITYEN','loan_city_en');//Ƶ��
//CRM�ӿڵ�ַ
define('P_CRM','http://crm.house365.com:81/');
//CRM API�ӿڵ�ַ
define('P_CRM_API',"http://crm.house365.com:81/index.php/Api/");
//���ܼҽӿڵ�ַ
define('P_FGJ_API',"http://fgjit.house365.com/fgj/index.php?");
//OA�ӿڵ�ַ
define('OA_API',"http://oa.house365.com/api/");


//������
require('../ThinkPHP/ThinkPHP.php');
?>