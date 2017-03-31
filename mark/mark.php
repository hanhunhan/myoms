<?php
	include_once("../inc/auth.php");
	//include_once("inc/utility_all.php");

	$sql = "select zzqf_user.title, zzqf_user_result.*, USER.user_name from zzqf_user_result left join zzqf_user on zzqf_user_result.tid=zzqf_user.id left join USER on zzqf_user_result.fuid=USER.user_id where zzqf_user_result.id='23'";
	$rows = exequery($connection,$sql);
	$row = mysql_fetch_array($rows);

	$sql = "select * from zzqf_papers where id='".$row[pid]."'";
	$rows = exequery($connection,$sql);
	$papers = mysql_fetch_array($rows);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?=$papers[title]?> - 360测评</title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />

<script type="text/javascript" src="/images/jquery-1.7.1.min.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		$('input[name^="exam"]').click(function(){
			$("#"+this.name).css({ "color": "black" });
		});

		$("#submit").click(function(){
			var chk_name=[];
			$('input[name^="exam"]:checked').each(function(){
				chk_name.push($(this).attr('name'));
			});

			var cnt_exam = $('div[id^="exam"]');
			if(chk_name.length < cnt_exam.length){
				alert("题目尚未做完，请继续完成");

				for(var i=1; i<=cnt_exam.length; i++){
					if($('input[name="exam_'+i+'"]:checked').size()<1){
						$("#exam_"+i).css({ "color": "red" });
					}
				}
				return false;
			}

			if( $('input[id$="000"]:checked').size()==cnt_exam.length || 
				$('input[id$="111"]:checked').size()==cnt_exam.length || 
				$('input[id$="222"]:checked').size()==cnt_exam.length ||
				$('input[id$="333"]:checked').size()==cnt_exam.length ||
				$('input[id$="444"]:checked').size()==cnt_exam.length ||
				$('input[id$="555"]:checked').size()==cnt_exam.length
			){
				alert("无效，请公正评价");
				return false;
			}

			/*
			if($('input[name="relation"]:checked').size()<1){
				alert("请选择您与被测人的关系");
				$("#relation").focus();
				return false;
			}
			*/

			var chk_value;
			for(var k=1; k<=cnt_exam.length; k++){
				var tmp=[];
				$('input[name="exam_'+k+'"]:checked').each(function(){
					tmp.push($(this).attr('value'));
				});
				var tmp1 = chk_value;
				chk_value = chk_value ? tmp1+";"+tmp : tmp;
			}

			var relation=[];

			/*
			$('input[name^="relation"]:checked').each(function(){
				relation.push($(this).attr('value'));
			});
			*/

			var tid = $("#tid").val();
			db = 'tid='+tid+'&relation='+relation+'&chk_value='+chk_value;
			$.ajax({
				data:encodeURI(db),
				type:'post',
				dataType:'json',
				url:'postajax.php',
				success:function(msg){
					if(msg=='1'){
						alert('提交成功');
					}
					else{
						alert('提交失败');
					}
				}
			});
			return true;
		});
	});
</script>

<style type="text/css">
	td {line-height:150%;}
	.content{ width:99%; margin:0 auto;}
	.descp { font-size:14px; text-align:center; margin:10px auto; text-align:left; padding-left:100px; padding-right:100px; text-indent:2em; letter-spacing:2px; line-height:20px; color:#333333;}
	.title3{ margin-top:10px; margin-left:10px; margin-bottom:10px;font-size:20px;}
	.option{ margin-left:30px; margin-bottom:8px;}
</style>
</head>
<body class="bodycolor">

<table border="0" width="100%" cellspacing="0" cellpadding="3" class="small">
  <tr>
    <td class="Big"><img src="/images/notify_new.gif" align="absmiddle"><span class="big3"> <?=$row[title]?> - 评测对象：<?=$row[user_name]?></span><br /><div class="descp"><?=nl2br($papers["descp"])?></div></td>
  </tr>
</table>

<table width="95%" border="0" cellspacing="0" cellpadding="0" height="3">
  <tr>
	<td background="/images/dian1.gif" width="100%"></td>
  </tr>
</table>
<br />

<div class="content">
  <div style="width:95%; display:inline;">
	<div class="title3 big" style="float:left; width:28%;">&nbsp;</div>
	<div class="title3 big" style="float:left; width:40%; text-align:center">目前的状况</div>
	<div class="title3 big" style="float:left; width:28%;">&nbsp;</div>
  </div>

<?php
	$i = 1;
	$sql = "select pe.* from zzqf_papers_exam pe where pe.pid = '".$row[pid]."' order by id";
	$rows = exequery($connection,$sql);
	while($vd = mysql_fetch_array($rows)){
		$examlist = unserialize($vd["examlist"]);
		$did = implode(",",$examlist);

		$sql = "select * from zzqf_exam where id in (".$did.") order by id";
		$rs = exequery($connection,$sql);
		while($exam = mysql_fetch_array($rs)){
			echo('
			  <div style="width:95%; display:inline; margin-bottom:20px;" id="exam_'.$i.'"> 
				<div class="title3 big" style="float:left; width:28%; text-align:right;">'.$exam["title_left"].'</div>
				<div class="title3 big" style="float:left; width:40%; text-align:center;">'
			);

			$k = 0;
			for($j = "A"; $j <= "F"; $j++){
				echo '<input type="radio" name="exam_'.$i.'" id="exam'.$k.$k.$k.'" value="'.$exam[did].'_'.$exam[id].'_'.$k.'"/> '.$j.' &nbsp;';
				$k++;
			}

			echo('
				</div>
				<div class="title3 big" style="float:left; width:28%;">'.$exam["title_right"].'</div>
			  </div>
			');

			$i++;
		}
	}
?>

  <!-- <div class="title3 big"><?=$i?>、选择您与被测人 <u><?=$row[user_name]?></u> 的关系</div>
  <div class="option"><input type="radio" name="relation" id="relation" value="1"/> 您自己</div>
  <div class="option"><input type="radio" name="relation" value="2"/> 您的上级</div>
  <div class="option"><input type="radio" name="relation" value="3"/> 您的同事/客户</div>
  <div class="option"><input type="radio" name="relation" value="4"/> 您的下属</div> -->

  <input type="hidden" name="tid" id="tid" value="<?=$id?>" />
  <div align="center"><input type="button" name="submit" id="submit" value=" 提 交 " class="BigButton"/></div>
</div>




</body>
</html>
