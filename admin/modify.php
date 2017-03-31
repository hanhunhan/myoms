<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	$connection = OpenConnection();
	include_once("vars.php");

	$sql = "SELECT * FROM {$tbl} WHERE ID=$ID";
	$rows = exequery($connection,$sql);
	if($row = mysql_fetch_array($rows)){
		$fuid = $row[fuid];
		$tuid = $row[tuid];

		$sql = "SELECT USER_NAME FROM USER WHERE USER_ID='".$fuid."'";
		$rs = exequery($connection,$sql);
		$r = mysql_fetch_array($rs);
		$fusername = $r[USER_NAME];

		$tusername = "";
		$arr_names = explode(',',$tuid);
		foreach($arr_names as $key => $value){
			if($value == "") continue;

			$sql = "SELECT USER_NAME FROM USER WHERE USER_ID='$value'";
			$rs = exequery($connection,$sql);
			$r = mysql_fetch_array($rs);
			$tusername .= $r["USER_NAME"].',';
		}
	}
?>

<html>
<head>
<title>编辑<?=$p_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">

<script type="text/javascript">
	function chkfrm(frm){
		if(frm.title.value == ""){ 
			alert("请填写测评标题");
			frm.title.focus();
			return false;
		}

		if(frm.pid.value == ""){ 
			alert("请选择测评试卷");
			frm.pid.focus();
			return false;
		}

		if(frm.TO_NAME.value == ""){ 
			alert("请选择测评对象");
			frm.TO_NAME.focus();
			return false;
		}

		if(frm.COPY_TO_NAME.value == ""){ 
			alert("请选择测评人");
			frm.COPY_TO_NAME.focus();
			return false;
		}

		return true;
	}

	function trim(str){
		for(var i=0; i<str.length&&str.charAt(i)==" "; i++);
		for(var j=str.length; j>0&&str.charAt(j-1)==" "; j--);
		if(i>j) return  "";  
		return str.substring(i,j);
	}

	function td_calendar(fieldname){
		myleft=document.body.scrollLeft+event.clientX-event.offsetX-80;
		mytop=document.body.scrollTop+event.clientY-event.offsetY+140;
		window.showModalDialog("/inc/calendar.php?FIELDNAME="+fieldname,self,"edge:raised;scroll:0;status:0;help:0;resizable:1;dialogWidth:280px;dialogHeight:205px;dialogTop:"+mytop+"px;dialogLeft:"+myleft+"px");
	}

	function LoadWindow2(){
		URL="/module/user_select_test?ID=2";
		loc_x=document.body.scrollLeft+event.clientX-event.offsetX+100;
		loc_y=document.body.scrollTop+event.clientY-event.offsetY+100;
		window.showModalDialog(URL,self,"edge:raised;scroll:0;status:0;help:0;resizable:1;dialogWidth:480px;dialogHeight:400px;dialogTop:" +loc_y+"px;dialogLeft:"+loc_x+"px");
	}

function LoadWindow1(){
		URL="/module/user_select_single_test";
		loc_x=document.body.scrollLeft+event.clientX-event.offsetX-100;
		loc_y=document.body.scrollTop+event.clientY-event.offsetY+200;
		window.showModalDialog(URL,self,"edge:raised;scroll:0;status:0;help:0;resizable:1;dialogWidth:420px;dialogHeight:365px;dialogTop:" +loc_y+"px;dialogLeft:"+loc_x+"px");
	}

	function clear_user1(){
		document.form1.COPY_TO_NAME.value="";
		document.form1.COPY_TO_ID.value="";
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
      <td width="20%" nowrap class="TableData" style="padding:5px;"> 测评标题：</td>
      <td width="80%" class="TableData" style="padding:5px;"> 
	    <input type="text" name="title" size="30" maxlength="100" class="BigInput" value="<?=$row[title]?>">
	  </td>
    </tr>
    <tr>
      <td width="20%" nowrap class="TableData" style="padding:5px;"> 测评试卷：</td>
      <td width="80%" class="TableData" style="padding:5px;"> 
		<select name="pid">
			<option value="">选择测评试卷</option>
			<?
				$sql = "select * from zzqf_papers order by id desc";
				$rs = exequery($connection,$sql);
				while($r = mysql_fetch_array($rs)){
					$selected = ($row[pid] == $r[id]) ? " selected" : "";
					echo('<option value="'.$r[id].'"'.$selected.'>'.$r[title].'</option>');
				}
			?>
		</select>
	  </td>
    </tr>
    <tr>
      <td width="20%" nowrap class="TableData" style="padding:5px;"> 测评对象：</td>
      <td width="80%" class="TableData" style="padding:5px;"> 
		<input type="text" name="TO_NAME" value="<?=$fusername?>" size="10" class="BigInput" readonly>&nbsp;
		<input type="button" value="指 定" class="SmallButton" onClick="LoadWindow1()" title="指定经办人" name="button">
		<input type="hidden" name="TO_ID" value="<?=$fuid?>">
	  </td>
    </tr>
    <tr>
      <td width="20%" nowrap class="TableData" style="padding:5px;"> 测评人：</td>
      <td width="80%" class="TableData" style="padding:5px;"> 
		<input type="hidden" name="COPY_TO_ID" value="<?=$tuid?>">
		<textarea name="COPY_TO_NAME" rows="4" cols="45" class="BigStatic" wrap="yes" readonly><?=$tusername?></textarea>
		<input type="button" value="添 加" class="SmallButton" onClick="LoadWindow2()" name="button">
		<input type="button" value="清 空" class="SmallButton" onClick="clear_user1()" title="清空收件人" name="button">
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