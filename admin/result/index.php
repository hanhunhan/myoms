<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");
	include_once("vars.php");

	die("��δ����");
?>
<html>
<head>
<title><?=$p_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">

<script type="text/javascript" src="/images/qswhGB2312.js"></script>

<script type="text/javascript">
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
</script>
</head>

<body class="bodycolor" topmargin="5">

<table border="0" width="100%" cellspacing="0" cellpadding="3" class="small">
  <tr>
    <td class="Big"><img src="/images/notify_new.gif" align="absmiddle"><span class="big3"> ��ع���</span><br /></td>
  </tr>
</table>

<div align="center">
  <input type="button" value="��������" class="BigButton" onClick="location='../';" title="��������">
  <input type="button" value="ά�ȹ���" class="BigButton" onClick="location='../dimension/';" title="ά�ȹ���">
  <input type="button" value="�������" class="BigButton" onClick="location='../exam/';" title="�������">
  <input type="button" value="�Ծ����" class="BigButton" onClick="location='../paper/';" title="�Ծ����">
  <input type="button" value="�������" class="BigButton" onClick="location='../result/';" title="�������">
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
		Message("","���ѷ�����".$p_title);
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
	  ��<span class="big4">&nbsp;<?=$TOTAL_COUNT?></span>&nbsp;��<?=$p_title?>
	</td>
  </tr>
</table>
<br />

<table border="0" cellspacing="1" width="99%" class="small" bgcolor="#000000" cellpadding="3">
  <tr class="TableHeader">
	<td align="center" width="80">���</td>
	<td align="center">��������</td>
	<td align="center" width="300">�����Ծ�</td>
	<td align="center" width="80">��������</td>
	<td align="center" width="80">������</td>
	<td align="center" width="120">����/״̬</td>
  </tr>

  <?
	$sql = "SELECT zzqf_user.title, zzqf_papers.title ptitle, {$tbl}.*, USER.user_name, USER.dept_id, user2.user_name user_name2 FROM {$tbl} LEFT JOIN zzqf_user ON {$tbl}.TID=zzqf_user.ID LEFT JOIN zzqf_papers ON {$tbl}.PID=zzqf_papers.ID  LEFT JOIN USER ON {$tbl}.FUID=USER.USER_ID LEFT JOIN USER user2 ON {$tbl}.TUID=user2.USER_ID WHERE 1".$where." ORDER BY {$tbl}.ID DESC";
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
		$str_action .= $r[status] ? '<a href="result.php?ID='.$ID.$url_next.'"> �Ѳ���</a>' : "δ����";
?>
  <tr class="<?=$TableLine?>">
	<td nowrap align="center" style="padding-left:5px;"><?=$r[id]?></td>
	<td align="left" style="padding-left:5px;"><?=$r[title]?></td>
	<td align="left" style="padding-left:5px;"><?=$r[ptitle]?></td>
	<td align="center" style="padding-left:5px;"><?=$r[user_name]?></td>
	<td align="center" style="padding-left:5px;"><?=$r[user_name2]?></td>
	<td nowrap align="center" style="padding-left:5px;"><?=$str_action?></td>
  </tr>

<?
	}
?>
</table>

<table border="0" cellspacing="1" width="99%" class="small" bgcolor="#000000" cellpadding="3" style="margin-top:5px;">
  <tr class="TableData">
	<td align="right" colspan="100">
	  ��<span class="big4">&nbsp;<?=$TOTAL_COUNT?></span>&nbsp;��<?=$p_title?>
	  <input type="button" value="��ҳ" class="SmallButton"<?if($PAGE_START == 1) echo(" disabled");?> onclick="set_page(1);"> &nbsp;
	  <input type="button" value="��һҳ" class="SmallButton"<?if($PAGE_START == 1) echo(" disabled");?> onclick="set_page(<?=($PAGE_START-$PAGE_SIZE)?>);"> &nbsp;
	  <input type="button" value="��һҳ" class="SmallButton"<?if($PAGE_END >= $TOTAL_COUNT) echo(" disabled");?> onclick="set_page(<?=($PAGE_END+1)?>);"> &nbsp;
	  <input type="button" value="ĩҳ" class="SmallButton"<?if($PAGE_END >= $TOTAL_COUNT) echo(" disabled");?> onclick="set_page(<?=$LAST_PAGE_START?>);"> &nbsp; ҳ��
	  <input type="text" name="PAGE_NUM" value="<?=$PAGE_NUM?>" class="SmallInput" size="2"> <input type="button"  value="ת��" class="SmallButton" onclick="set_page(0);" title="ת��ָ����ҳ��">&nbsp;
	</td>
  </tr>
</table>
</form>

</body>
</html>
