<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	$connection = OpenConnection();
	include_once("vars.php");

	$p_title = "结果";

	$sql = "SELECT zzqf_papers.title ptitle, {$tbl}.*, USER.user_name FROM {$tbl} LEFT JOIN zzqf_papers ON {$tbl}.PID=zzqf_papers.ID  LEFT JOIN USER ON {$tbl}.FUID=USER.USER_ID WHERE {$tbl}.ID='$ID' ORDER BY {$tbl}.ID DESC";
	$rows = exequery($connection,$sql);
	$row = mysql_fetch_array($rows);

	$sql = "select count(*) counts from zzqf_user_result where zzqf_user_result.tid='$ID' and zzqf_user_result.status=1";
	$rs = exequery($connection,$sql);
	$r = mysql_fetch_array($rs);
	$counts = $r[counts];
?>

<html>
<head>
<title>查看<?=$p_title?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312">

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

<table border="0" width="90%" cellpadding="2" cellspacing="1" align="center" bgcolor="#000000" class="small" id="tbl_export">
  <tr align="center" class="TableControl">
	<td colspan="<?=($counts + 3)?>" align="left" style="padding:5px;">
	  <strong><?=$row[title]?> - <?=$row[ptitle]?></strong>
	  测评对象：<strong><?=$row[user_name]?></strong>
	</td>
  </tr>
  <tr>
	<td width="1%" nowrap class="TableData" style="padding:5px;"> &nbsp;</td>
    <?
		$arr_users = array();
		$sql = "select USER.user_id, USER.user_name from zzqf_user_result left join USER on zzqf_user_result.tuid=USER.user_id where zzqf_user_result.tid='$ID' and zzqf_user_result.status=1";
		$rs = exequery($connection,$sql);
		while($r = mysql_fetch_array($rs)){
			$arr_users[] = $r[user_id];
	?>
	<td width="1%" class="TableData" style="padding:5px;"> <?=$r[user_name]?></td>
	<?
		}
    ?>
	<td width="1%" class="TableData" style="padding:5px;"> 指标得分</td>
	<td width="1%" class="TableData" style="padding:5px;"> 指标差异</td>
  </tr>
  <?
	$sql = "select * from zzqf_papers where id='".$row[pid]."'";
	$rows = exequery($connection,$sql);
	$papers = mysql_fetch_array($rows);

	$arr_results = array();
	$sql = "select * from zzqf_user_result where tid='$ID' and status=1";
	$rs = exequery($connection,$sql);
	while($r = mysql_fetch_array($rs)){
		$arr_result = unserialize($r["result"]);
		$arr_results[$r[tuid]] = $arr_result;
	}

	$arr_exams = array();
	$arr_totalscore = array();
	$arr_dscore = array();
	$arr_totaldscore = array();

	$sql = "select zzqf_papers_exam.*, zzqf_dimension.title dtitle from zzqf_papers_exam left join zzqf_dimension on zzqf_papers_exam.did=zzqf_dimension.id where zzqf_papers_exam.pid = '".$row[pid]."' order by id";
	$rs = exequery($connection,$sql);
	while($r = mysql_fetch_array($rs)){
		$examlist = unserialize($r["examlist"]);
  ?>
  <tr>
	<td width="1%" class="TableData" style="padding:5px;"> <?=$r[dtitle]?></td>
  <?
		foreach($arr_users as $uid){
			$j = 0;
			$score = 0;
			foreach($examlist as $did){
				$j++;
				$score += $arr_results[$uid][$r[did]][$did];
			}

			$score = round($score / $j,2);
			$arr_totalscore[$uid] += $score;

			$arr_dscore[$r[did]] += $score;
  ?>
	<td width="1%" class="TableData" style="padding:5px;"> <?=$score?></td>
  <?
		}

		$dscore = round($arr_dscore[$r[did]] / sizeof($arr_users),2);

		$arr_totaldscore[0] += $dscore;
		$arr_totaldscore[1] += 5 - $dscore;
  ?>
	<td width="1%" class="TableData" style="padding:5px;"> <?=$dscore?></td>
	<td width="1%" class="TableData" style="padding:5px;"> <?=(5 - $dscore)?></td>
  </tr>
  <?
	}
  ?>
  <tr align="center" class="TableControl">
	<td width="1%" style="padding:5px;"> &nbsp;</td>
	<?
		foreach($arr_users as $uid){
	?>
	<td width="1%" style="padding:5px;" align="left"> <?=$arr_totalscore[$uid]?></td>
	<?
		}
	?>
	<td width="1%" style="padding:5px;" align="left"> <?=$arr_totaldscore[0]?></td>
	<td width="1%" style="padding:5px;" align="left"> <?=$arr_totaldscore[1]?></td>
  </tr>
</table>

<script type="text/javascript">
	function js_export(){
		var tbl_content = document.getElementById("tbl_export").innerHTML;
		tbl_content = "<TABLE>" + tbl_content + "</TABLE>"

		document.frm_export.export_content.value = tbl_content;
		document.frm_export.submit();
	}

</script>

<div style="margin:10px auto;" align="center"><input type="button" value=" 导 出 " onclick="js_export();"> <input type="button" value=" 返 回 " onclick="history.go(-1)"></div>

<form name="frm_export" id="frm_export" action="/inc/export_excel.php" method="post" target="ifr_export" style="margin:0;padding:0;"><input type="hidden" name="export_content" value=""></form>

<iframe name="ifr_export" width="0" height="0"></iframe>

</body>
</html>