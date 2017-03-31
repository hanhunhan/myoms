<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	die("暂未开发");

	$connection = OpenConnection();
	include_once("vars.php");

	$sql = "SELECT zzqf_user.title, zzqf_papers.title ptitle, {$tbl}.*, USER.user_name, USER.dept_id, user2.user_name user_name2 FROM {$tbl} LEFT JOIN zzqf_user ON {$tbl}.TID=zzqf_user.ID LEFT JOIN zzqf_papers ON {$tbl}.PID=zzqf_papers.ID  LEFT JOIN USER ON {$tbl}.FUID=USER.USER_ID LEFT JOIN USER user2 ON {$tbl}.TUID=user2.USER_ID WHERE {$tbl}.ID='$ID' ORDER BY {$tbl}.ID DESC";
	$rows = exequery($connection,$sql);
	$row = mysql_fetch_array($rows);
?>

<html>
<head>
<title>查看<?=$p_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">

<script type="text/javascript">
	function chkfrm(frm){
		if(frm.title.value == ""){ 
			alert("请填写维度名称");
			frm.title.focus();
			return false;
		}

		return true;
	}
</script>

<script type="text/javascript" src="/images/prototype.js"></script>
<script type="text/javascript" src="/images/qswhGB2312.js"></script>
<script type="text/javascript" src="/images/autocomplete.js"></script>
<link rel="stylesheet" type="text/css" href="/images/autocomplete.css" />

</head>

<body class="bodycolor" topmargin="5">

<table border="0" width="95%" cellspacing="0" cellpadding="3" class="small">
  <tr>
    <td class="Big"><img src="/images/notify_new.gif" align="absmiddle"><b><span class="Big1"> 查看<?=$p_title?></span></b>
    </td>
  </tr>
</table>

<hr width="100%" height="1" align="left" color="#FFFFFF" />

<table border="0" width="90%" cellpadding="2" cellspacing="1" align="center" bgcolor="#000000" class="small">
  <tr align="center" class="TableControl">
	<td colspan="4" nowrap style="padding:5px;">
	  <strong><?=$row[title]?> - <?=$row[ptitle]?></strong>
	  测评对象：<strong><?=$row[user_name]?></strong>
	  测评人：<strong><?=$row[user_name2]?></strong>
	</td>
  </tr>
  <tr align="center" class="TableControl">
	<td colspan="4" nowrap style="padding:5px;">
	</td>
  </tr>
</table>

</body>
</html>