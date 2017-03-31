<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");
	include_once("vars.php");
?>
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
</head>

<body class="bodycolor" topmargin="5">
<?
	$connection = OpenConnection();

	$sql = "DELETE FROM {$tbl} WHERE ID=$ID";
	exequery($connection,$sql);

	$sql = "DELETE FROM zzqf_user_result WHERE TID=$ID";
	exequery($connection,$sql);

	header("location: index.php?t=1".$url_next);
?>
</body>
</html>
