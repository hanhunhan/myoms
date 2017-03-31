<?
	include_once("inc/conn.php");

	$arr_result = array();
	$arr_value = explode(";",$_POST[chk_value]);
	foreach($arr_value as $value){
		$arr_values = explode("_",$value);
		$arr_result[$arr_values[0]."_".$arr_values[1]] = $arr_values[2];
	}

	$arr_results = array();
	$sql = "select zzqf_user.title, zzqf_user_result.*, USER.user_name from zzqf_user_result left join zzqf_user on zzqf_user_result.tid=zzqf_user.id left join USER on zzqf_user_result.fuid=USER.user_id where zzqf_user_result.id='$tid'";
	$rows = exequery($connection,$sql);
	$row = mysql_fetch_array($rows);

	$sql = "select * from zzqf_papers where id='".$row[pid]."'";
	$rows = exequery($connection,$sql);
	$papers = mysql_fetch_array($rows);

	$sql = "select pe.* from zzqf_papers_exam pe where pe.pid = '".$row[pid]."' order by id";
	$rows = exequery($connection,$sql);
	while($vd = mysql_fetch_array($rows)){
		// $arr_results[$vd[did]][weights] = $vd[weights];

		$examlist = unserialize($vd["examlist"]);
		$did = implode(",",$examlist);

		$sql = "select * from zzqf_exam where id in (".$did.") order by id";
		$rs = exequery($connection,$sql);
		while($exam = mysql_fetch_array($rs)){
			$arr_results[$exam[did]][$exam[id]] = $arr_result[$exam[did]."_".$exam[id]];
		}
	}

	$sql="UPDATE zzqf_user_result SET RELATION='".$_POST[relation]."', STATUS='1', RESULT='".serialize($arr_results)."' WHERE ID='$tid'";
	exequery($connection,$sql);

	exit(json_encode(array(1)));
?>