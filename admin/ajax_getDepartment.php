<?php
include_once("../common/mysql.class.php");
include_once("../common/function.php");
include_once("../common/check.php");
$id = $_GET['id'] ? $_GET['id']:1;
$row = getDepartment($db,$id,$DB_PREFIX);
echo json_encode($row);

 
function getDepartment($db,$pid,$DB_PREFIX){
	$sql = "SELECT * FROM ".$DB_PREFIX."new_department where DEPT_PARENT = $pid and status<>-1 ";
	$row = $db->getAll($sql); 
	$temp  = array();
	foreach($row as $val){
		$one = array();
		$one['id'] =$val['DEPT_ID'];
		$one['text'] =g2u($val['DEPT_NAME']);
		$one['state'] ='closed';
		$children =  getDepartment($db,$val['DEPT_ID'],$DB_PREFIX);
		//if($children)$one['children'] = $children;
		$temp [] = $one; 
	}
	return $temp;
	

}

?>