<?
	$p_title = "ฝแน๛";
	$tbl = "zzqf_user_result";

	$PAGE_SIZE = 100;

	if(isset($PAGE_START)) $url_next .= "&PAGE_START=".$PAGE_START;

	$if_admin = true;
	include_once("../../authvars.php");
?>