<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	$connection = OpenConnection();
	include_once("vars.php");

	$sql = "SELECT * FROM {$tbl} WHERE ID=$ID";
	$rows = exequery($connection,$sql);
	$row = mysql_fetch_array($rows);

	$arr_exams = array();
	$sql = "SELECT * FROM zzqf_papers_exam WHERE PID=$ID";
	$rs = exequery($connection,$sql);
	while($r = mysql_fetch_array($rs)){
		$arr_exam = array();

		$arr_exam[examlist] = unserialize($r[examlist]);

		$arr_exams[$r[did]] = $arr_exam;
	}
?>

<html>
<head>
<title>编辑<?=$p_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">

<script type="text/javascript">
	function chkfrm(frm){
		if(frm.title.value == ""){ 
			alert("请填写试卷标题");
			frm.title.focus();
			return false;
		}

		if($('input[name^="dimension"]:checked').size() < 1){
			alert("请至少选择一个维度");
			return false;
		}

		$('input[name^="dimension"]:checked').each(function(){
			var s = this.id.replace("dimension","");
			if($('input[id="exam'+s+'"]:checked').size() < 1){
				alert("请至少选择一道试题");
				$("#exam"+s).focus();
				r = 1;
				return false;
			}
			else{
				r = 0;
			}
		});
		
		if(r){
			return false;
		}
		else{
			return true;
		}
	}

	function trim(str){
		for(var i=0; i<str.length&&str.charAt(i)==" "; i++);
		for(var j=str.length; j>0&&str.charAt(j-1)==" "; j--);
		if(i>j) return  "";  
		return str.substring(i,j);
	}
</script>

<script type="text/javascript" src="/images/jquery-1.7.1.min.js"></script>

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
      <td width="20%" nowrap class="TableData" style="padding:5px;"> 试卷标题：</td>
      <td width="80%" class="TableData" style="padding:5px;"> 
	    <input type="text" name="title" size="30" maxlength="100" class="BigInput" value="<?=$row[title]?>">
	  </td>
    </tr>
    <tr>
      <td width="20%" nowrap class="TableData" style="padding:5px;"> 试卷描述：</td>
      <td width="80%" class="TableData" style="padding:5px;"> 
		<div style="float:left;"><textarea name="descp" rows="6" cols="60" class="BigStatic" wrap="yes"><?=$row[descp]?></textarea></div>
	  </td>
    </tr>
	<?
		$sql = "select * from zzqf_dimension order by id";
		$rs = exequery($connection,$sql);
		while($r = mysql_fetch_array($rs)){
	?>
    <tr>
      <td width="20%" nowrap class="TableData" style="padding:5px;"> 试卷内容：</td>
      <td width="80%" class="TableData" style="padding:5px;"> 
		<input type="checkbox" name="dimension[]" id="dimension<?=$r[id]?>" value="<?=$r[id]?>"<?=(isset($arr_exams[$r[id]]))?' checked':''?> /> <?=$r[title]?><br /><hr />
		<?
			$sql = "select * from zzqf_exam where did='".$r[id]."' order by id";
			$rs_exam = exequery($connection,$sql);
			while($exam = mysql_fetch_array($rs_exam)){
				echo(' <input type="checkbox" name="exam'.$r[id].'[]" id="exam'.$r[id].'" value='.$exam["id"].''.((in_array($exam["id"],$arr_exams[$r[id]][examlist]))?' checked':'').' /> '.$exam["title_left"]." - ".$exam["title_right"].'<br />');
			}
		?>
	  </td>
    </tr>
	<?
		}
	?>
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