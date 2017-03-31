<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	$connection = OpenConnection();
	include_once("vars.php");

	// php бщжЄЃЌднТд

	if($ID != ""){
		$sql="UPDATE {$tbl} SET title='".addslashes($title)."', descp='".addslashes($descp)."' WHERE ID=$ID";
		exequery($connection,$sql);

		$pid = $ID;
	}
	else{
		$sql = "INSERT INTO {$tbl} (title, descp) VALUES ('".addslashes($title)."', '".addslashes($descp)."')";
		exequery($connection,$sql);

		$pid = mysql_insert_id();
	}

	$sql = "DELETE FROM zzqf_papers_exam WHERE PID='".$pid."'";
	exequery($connection,$sql);

	foreach($dimension as $k=>$v){
		$examlist = serialize($_POST['exam'.$v]);

		$sql = "INSERT INTO zzqf_papers_exam (pid, did, examlist) VALUES ('".$pid."', '".$v."', '".$examlist."')";
		exequery($connection,$sql);
	}

	if($OP == 1) header("location:modify.php?OP=1&ID=".$ID.$url_next);
	else header("location:index.php?t=1".$url_next);
?>