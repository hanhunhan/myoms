<?php
if(!session_id()) session_start();
//ini_set('display_errors','on');
//error_reporting(E_ALL);
include_once("../common/mysql.class.php");
include_once("../common/function.php");
include_once("check.php");
$uid = $_SESSION['zzfwuid'];
if($_REQUEST['subquestion']){
	$sql = "SELECT * FROM ".$DB_PREFIX."new_user_result where uid='".$uid."' ";
	$count = $db->getOne($sql); 
	if($count){
		Jalert('ʧ��,�ظ��ύ','question.php');

	}else{
		$sql = "SELECT * FROM ".$DB_PREFIX."new_user where USER_ID='".$uid."' ";
		$user = $db->getOne($sql); 

		$data['uid'] = $uid ;
		$data['dept'] = $user['DEPT_ID'];
		//$data['dept2'] = 1;
		for($i=1;$i<16;$i++){
			$total +=$_REQUEST['score'.$i];
		}
		$data['score'] = $total;
		$data['score1'] = $_REQUEST['score1'];
		$data['score2'] = $_REQUEST['score2'];
		$data['score3'] = $_REQUEST['score3'];
		$data['score4'] = $_REQUEST['score4'];
		$data['score5'] = $_REQUEST['score5'];
		$data['score6'] = $_REQUEST['score6'];
		$data['score7'] = $_REQUEST['score7'];
		$data['score8'] = $_REQUEST['score8'];
		$data['score9'] = $_REQUEST['score9'];
		$data['score10'] = $_REQUEST['score10'];
		$data['score11'] = $_REQUEST['score11'];
		$data['score12'] = $_REQUEST['score12'];
		$data['score13'] = $_REQUEST['score13'];
		//$data['score14'] = $_REQUEST['score14'];
		//$data['score15'] = $_REQUEST['score15'];
		//$data['other'] = $_REQUEST['other'];
		$data['other'] = mb_substr($_REQUEST['other'],0,100,'gbk'); 
		$res = $db->insert($DB_PREFIX.'new_user_result',$data); 
		if($res){
			Jalert('����ɹ�!','question.php');
		}else{
			Jalert('ʧ��','question.php');
			 
		}
	}

}

$sql = "SELECT * FROM ".$DB_PREFIX."new_exam where status=1 ";
$exam = $db->getAll($sql); 
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title></title>  
    <link rel="stylesheet" href="../css/pintuer.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="../js/jquery.js"></script>
    <script src="../js/pintuer.js"></script>  
	<style>
	table th{background:#0090D7;font-weight:normal;line-height:30px;font-size:14px;color:#FFF;}
table tr:nth-child(odd){background:#F4F4F4;}
table td:nth-child(even){color:#000;}
 
table tr:hover{background:#73B1E0;color:#FFF;}
table td,table th{border:1px solid #EEE;}
	</style>
	
</head>
<body>
<form method="post" action="" id="formLogin">
  <div class="panel admin-panel">
    <div class="panel-head"><strong class="icon-reorder"> ����������֯��Χ�ʾ����</strong></div>
    <div class="padding border-bottom">
      <ul class="search">
        <li>
           ����1-5�ְ�ť�£�ѡ�����Ͽɵ�ѡ����С�5����ʾ�ǳ�ͬ�⣬��4����ʾ�Ƚ�ͬ�⣬��3����ʾһ�㣬��2����ʾ�Ƚϲ�ͬ�⣬��1����ʾ�ǳ���ͬ�⡣
��ӭ������֯��Χ�ĸ����������Ľ��鼰�����
<br>
��ÿ�����һ�£������Ͼ������������õ�������ʵ���ۡ�
        </li>
      </ul>
    </div>
    <table class="table  text-center">
      <tr>
        <th width="20" >ID</th>
        <th width="500">����</th>       
        <th width="10">1��</th>
        <th width="10">2��</th>
        <th width="10">3��</th>
        <th width="10">4��</th>
         <th width="10">5��</th>
          
      </tr>   
	  <?php
	  foreach($exam as $key=>$val){
	  
	  ?>
        <tr>
          <td> <?=$val['id']?></td>
          <td  align="center"  ><?=$val['question']?></td>
          <td ><input type="radio" name="score<?=$val['id']?>" value="1"  /></td>
          <td ><input type="radio" name="score<?=$val['id']?>" value="2"    /></td>  
           <td  ><input type="radio" name="score<?=$val['id']?>" value="3"   /></td>         
          <td ><input type="radio" name="score<?=$val['id']?>" value="4"     /></td>
          <td ><input type="radio" name="score<?=$val['id']?>" value="5"    /></td>
         
        </tr>
		<?php
		
	  }
		?>
		 
		  
		<tr>
		  <td>14</td>
          <td  align="center">��������</td>
          <td colspan="5"  align="left" ><textarea    type="textarea" name="other" value="" maxlength="100"  style="width:350px; height:50px;" ></textarea>  </td>
         
        </tr>
        ��
       
    </table>
	<div class="form-group">
        <div class="label">
          <label></label>
        </div>
        <div class="field"  style="text-align:center;">
          <button class="button bg-main icon-check-square-o" name='subquestion' id="subquestion" value='1' type="submit"> �ύ</button>
        </div>
      </div>
  </div>
</form>
<script type="text/javascript">

 

$(function(){
	$("#subquestion").click(function(){
		for(var i=1;i<14;i++){
			if( $("input[name=score"+i+"]:checked").val() ==null ){
				alert('�������'+i+'���');
				return false;
			}
		}
		return true;
	});  

	 


}); 

 
</script>
</body></html>