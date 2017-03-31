<?php
/**
 * 跨平台CRM的接口函数
 *
 */
class interfaceApi{
    
	static public $propertyApi = "http://api.house365.com/xf/newhouse/get_prj.php?r=j";
	static public $houselistApi = "http://api.house365.com/xf/newhouse/get_search_prj.php?limit=50&type=365tf";
	//获取关联楼盘
	static public function getHouselist($search,$city){

		$str = file_get_contents(self::$houselistApi."&q={$search}&city={$city}");
		
		return $str;
	}
	//获取关联项目属性
	static public function getHouseProperty($houseid,$city){
		$str = file_get_contents(self::$propertyApi."&prj_listid={$houseid}&city={$city}");
		
		return $str;
	}
    
   
}