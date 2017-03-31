<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	$connection = OpenConnection();
	include_once("vars.php");

	$sql = "SELECT * FROM {$tbl} WHERE ID=$ID";
	$rows = exequery($connection,$sql);
	$row = mysql_fetch_array($rows);
?>

<html>
<head>
<title>编辑<?=$p_title?></title>
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
    <td class="Big"><img src="/images/notify_new.gif" align="absmiddle"><b><span class="Big1"> 编辑<?=$p_title?></span></b>
    </td>
  </tr>
</table>

<hr width="100%" height="1" align="left" color="#FFFFFF" />

<table border="0" width="90%" cellpadding="2" cellspacing="1" align="center" bgcolor="#000000" class="small">
  <form enctype="multipart/form-data" action="update.php"  method="post" name="form1" id="trip" onsubmit="return chkfrm(this);">
    <tr>
      <td width="20%" nowrap class="TableData" style="padding:5px;"> 维度名称：</td>
      <td width="80%" class="TableData" style="padding:5px;"> 
	    <input type="text" name="title" size="30" maxlength="100" class="BigInput" value="<?=$row[title]?>">
	  </td>
    </tr>
    <tr align="center" class="TableControl">
      <td colspan="4" nowrap style="padding:5px;">
        <input type="hidden" name="ID" value="<?=$row[id]?>" />
        <input type="hidden" name="PAGE_START" value="<?=$PAGE_START?>" />
        <input type="hidden" name="OP" value="<?=$OP?>" />
        <input type="submit" value="确定" class="BigButton">&nbsp;&nbsp;
        <?if($OP != 1){?><input type="button" value="返回" class="BigButton" onClick="location='index.php?t=1<?=$url_next?>'"><?}?>
      </td>
    </tr>
  </form>
</table>

</body>
</html>