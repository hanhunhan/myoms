<?
	$p_title = "����";
	$tbl = "zzqf_exam";

	$PAGE_SIZE = 100;

	if(isset($PAGE_START)) $url_next .= "&PAGE_START=".$PAGE_START;

	$if_admin = true;
	include_once("../../authvars.php");
?>