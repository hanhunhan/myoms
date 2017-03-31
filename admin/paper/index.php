<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");
	include_once("vars.php");
?>
<html>
<head>
<title><?=$p_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">

<script type="text/javascript" src="/images/qswhGB2312.js"></script>

<script type="text/javascript">
	function deleteit(ID){
		msg='确认要删除该项<?=$p_title?>么？';
		if(window.confirm(msg)){
			URL="delete.php?ID="+ID+"<?=$url_next?>";
			window.location=URL;
		}
	}

	function set_page(PAGE_START){
		if(PAGE_START==0){
			try{
				PAGE_START=(document.frm.PAGE_NUM.value-1)*<?=$PAGE_SIZE?>+1;
			}
			catch (e){
				PAGE_START=1;
			}
		}

		var url="index.php?PAGE_START="+PAGE_START;

		url = url.replace("+","%2B");
		location = url;
	}

	function trim(str){
		for(var i=0; i<str.length&&str.charAt(i)==" "; i++);
		for(var j=str.length; j>0&&str.charAt(j-1)==" "; j--);
		if(i>j) return  "";  
		return str.substring(i,j);
	}
</script>
</head>

<body class="bodycolor" topmargin="5">

<table border="0" width="100%" cellspacing="0" cellpadding="3" class="small">
  <tr>
    <td class="Big"><img src="/images/notify_new.gif" align="absmiddle"><span class="big3"> 相关管理</span><br /></td>
  </tr>
</table>

<div align="center">
  <input type="button" value="测评管理" class="BigButton" onClick="location='../';" title="测评管理">
  <input type="button" value="维度管理" class="BigButton" onClick="location='../dimension/';" title="维度管理">
  <input type="button" value="试题管理" class="BigButton" onClick="location='../exam/';" title="试题管理">
  <input type="button" value="试卷管理" class="BigButton" onClick="location='../paper/';" title="试卷管理">
  <!-- <input type="button" value="结果管理" class="BigButton" onClick="location='../result/';" title="结果管理"> -->
</div>

<br />

<table width="99%" border="0" cellspacing="0" cellpadding="0" height="3">
  <tr>
    <td background="/images/dian1.gif" width="100%"></td>
  </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="3" class="small">
  <tr>
    <td class="Big"><img src="/images/notify_new.gif" align="absmiddle"><span class="big3"> 发布新的<?=$p_title?></span><br /></td>
  </tr>
</table>

<div align="center">
  <input type="button" value="新建<?=$p_title?>" class="BigButton" onClick="location='modify.php?t=1<?=$url_next?>';" title="新建<?=$p_title?>">
</div>

<br />

<table width="99%" border="0" cellspacing="0" cellpadding="0" height="3">
  <tr>
    <td background="/images/dian1.gif" width="100%"></td>
  </tr>
</table>
<?
	$where="";

	$sql = "SELECT COUNT(*) FROM {$tbl} WHERE 1".$where;
	$rs = exequery($connection,$sql);

	$news_count = 0;
	if($r = mysql_fetch_array($rs)) $news_count = $r[0];

	if($news_count == 0){
?>
<table border="0" width="100%" cellspacing="0" cellpadding="3" class="small">
  <tr>
    <td class="Big"><img src="/images/notify_open.gif" align="absmiddle"><span class="big3"> <?=$p_title?></span><br /></td>
  </tr>
</table>
<br>
<?
		Message("","无已发布的".$p_title);
		exit;
	}

	$TOTAL_COUNT = $news_count;

	$PAGE_TOTAL = $TOTAL_COUNT/$PAGE_SIZE;
	$PAGE_TOTAL = ceil($PAGE_TOTAL);

	if($TOTAL_COUNT <= $PAGE_SIZE) $LAST_PAGE_START = 1;
	else if($TOTAL_COUNT % $PAGE_SIZE == 0) $LAST_PAGE_START = $TOTAL_COUNT - $PAGE_SIZE + 1;
	else $LAST_PAGE_START = $TOTAL_COUNT - $TOTAL_COUNT % $PAGE_SIZE + 1;

	if($PAGE_START == "") $PAGE_START = 1;

	if($PAGE_START > $TOTAL_COUNT) $PAGE_START = $LAST_PAGE_START;

	if($PAGE_START < 1) $PAGE_START = 1;

	$PAGE_END = $PAGE_START + $PAGE_SIZE - 1;

	if($PAGE_END > $TOTAL_COUNT) $PAGE_END = $TOTAL_COUNT;

	$PAGE_NUM = ($PAGE_START - 1) / $PAGE_SIZE + 1;
?>
<form name="frm" onsubmit="return false;" style="margin:0;padding:0;">
<table border="0" width="99%" cellspacing="0" cellpadding="3" class="small">
  <tr>
    <td class="Big"><img src="/images/notify_open.gif" align="absmiddle"><span class="big3"> <?=$p_title?></span><br /></td>
    <td valign="bottom" align="right" class="big3">
	  共<span class="big4">&nbsp;<?=$TOTAL_COUNT?></span>&nbsp;条<?=$p_title?>
	</td>
  </tr>
</table>
<br />

<table border="0" cellspacing="1" width="99%" class="small" bgcolor="#000000" cellpadding="3">
  <tr class="TableHeader">
	<td align="center" width="80">编号</td>
	<td align="center" width="300">试卷标题</td>
	<td align="center">试卷描述</td>
	<td align="center" width="120">操作</td>
  </tr>

  <?
	$sql = "SELECT {$tbl}.* FROM {$tbl} where 1".$where." ORDER BY ID DESC";
	$rs = exequery($connection,$sql);

	$news_count = 0;
	while($r = mysql_fetch_array($rs)){
		$news_count++;

		if($news_count < $PAGE_START) continue;
		else if($news_count > $PAGE_END) break;

		if($news_count % 2 == 1) $TableLine = "TableLine1";
		else $TableLine = "TableLine2";

		$ID = $r[id];

		$str_action = "";
		$str_action .= '<a href="modify.php?ID='.$ID.$url_next.'"> 修改</a>';
		$str_action .= '<a href="javascript:deleteit(\''.$ID.'\');"> 删除</a>';
?>
  <tr class="<?=$TableLine?>">
	<td nowrap align="center" style="padding-left:5px;"><?=$r[id]?></td>
	<td align="left" style="padding-left:5px;"><?=$r[title]?></td>
	<td align="left" style="padding-left:5px;"><?=$r[descp]?></td>
	<td nowrap align="center" style="padding-left:5px;"><?=$str_action?></td>
  </tr>
<?
	}
?>
</table>

<table border="0" cellspacing="1" width="99%" class="small" bgcolor="#000000" cellpadding="3" style="margin-top:5px;">
  <tr class="TableData">
	<td align="right" colspan="100">
	  共<span class="big4">&nbsp;<?=$TOTAL_COUNT?></span>&nbsp;条<?=$p_title?>
	  <input type="button" value="首页" class="SmallButton"<?if($PAGE_START == 1) echo(" disabled");?> onclick="set_page(1);"> &nbsp;
	  <input type="button" value="上一页" class="SmallButton"<?if($PAGE_START == 1) echo(" disabled");?> onclick="set_page(<?=($PAGE_START-$PAGE_SIZE)?>);"> &nbsp;
	  <input type="button" value="下一页" class="SmallButton"<?if($PAGE_END >= $TOTAL_COUNT) echo(" disabled");?> onclick="set_page(<?=($PAGE_END+1)?>);"> &nbsp;
	  <input type="button" value="末页" class="SmallButton"<?if($PAGE_END >= $TOTAL_COUNT) echo(" disabled");?> onclick="set_page(<?=$LAST_PAGE_START?>);"> &nbsp; 页数
	  <input type="text" name="PAGE_NUM" value="<?=$PAGE_NUM?>" class="SmallInput" size="2"> <input type="button"  value="转到" class="SmallButton" onclick="set_page(0);" title="转到指定的页面">&nbsp;
	</td>
  </tr>
</table>
</form>

</body>
</html>
