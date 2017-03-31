<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	$connection = OpenConnection();
	include_once("vars.php");

	// php бщжЄЃЌднТд

	if($ID != ""){
		$sql="UPDATE {$tbl} SET title_left='".addslashes($title_left)."', title_right='".addslashes($title_right)."', did='$did' WHERE ID=$ID";
		exequery($connection,$sql);
	}
	else{
		$sql = "INSERT INTO {$tbl} (title_left, title_right, did) VALUES ('".addslashes($title_left)."', '".addslashes($title_right)."', '$did')";
		exequery($connection,$sql);
	}

	if($OP == 1) header("location:modify.php?OP=1&ID=".$ID.$url_next);
	else header("location:index.php?t=1".$url_next);
?>