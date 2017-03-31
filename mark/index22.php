<?php
include_once("../common/mysql.class.php");
include_once("../common/function.php");
 

include_once("../common/auth.php");
//$code= 'admin$管理员';
//$_GET["authcode"] = get_authcode($code,'ENCODE');

$authcode = get_authcode($_GET["authcode"]);//var_dump($authcode);
//$authcode = $_GET["authcode"];
if(!$authcode){
	echo '无权限1！';
	exit;
	 
}

list($uid,$username) = explode("$",$authcode);

if(!$uid  ){
	echo '无权限2！';
	exit;
	 
}

$_SESSION['zzfwuid'] =$uid;
include_once("check.php");
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>三六五网组织氛围问卷调查</title>  
    <link rel="stylesheet" href="../css/pintuer.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="../js/jquery.js"></script>
    <script src="../js/pintuer.js"></script>  
	<script language="javascript" type="text/javascript" src="../js/My97DatePicker/WdatePicker.js"></script>
</head>
<script>
$(function(){
	$("#checktop").change(function(){
		if($("#checktop").prop("checked") ){
			$("#submitsb").attr("disabled",false);
		}else{
			$("#submitsb").attr("disabled","disabled");
		}
	
	
	})


});
</script>
<body>
<div style="">
<div class="panel admin-panel" style="width:960px;margin-left: auto;margin-right: auto;margin-top:20px; ">
  <div class="panel-head"><strong>  <h1 style="text-align:center;">三六五网组织氛围问卷调查</h1>  </strong></div>
  <div class="body-content">
  <div  style="width:900px;margin-left: auto;margin-right: auto;font-size:16px;">
    <form method="post" class="form-x" action="question.php">
        <div class="form-group">
        
         

		  <h2  style="text-align:center;font-weight:bold;">欢迎语</h2><br> 
		  <p>
		  您好！</p><br> 
		  <p style="text-indent:2em; ">
非常感谢您能抽出时间填写下面的问卷。</p><p style="text-indent:2em; "> 每一位员工的声音对于提升工作环境都非常重要，我们希望借助这个项目，对公司文化和价值观起到促进作用，请您对相关问题评价作出认真、客观的反馈，这对公司组织氛围提升而言是莫大的帮助。</p><p style="text-indent:2em; ">  调研团队承诺，将严格保证本次调查的匿名性。衷心感谢您的参与。 

</p>
 <h2 style="text-align:center;font-weight:bold;">填写说明</h2><br> 
 <p style="text-indent:2em; ">
 请在1-5分按钮下，选中你认可的选项，其中“5”表示非常同意，“4”表示比较同意，“3”表示一般，“2”表示比较不同意，“1”表示非常不同意。
 </p>



        </div>
		<div style="text-align:center;">
		<input type="checkbox" name="checktop" id="checktop" />我已阅读并知晓相关说明。
		</div>
        
      
      
       
        <div class="form-group">
          <div class="label">
			<label></label>
          </div>
        <div class="field"  style="text-align:center;">
          <button class="button bg-main icon-check-square-o"  disabled="disabled"  id="submitsb" type="submit"> 进入问卷调查</button>
        </div>
      
    </form>
	</div>
  </div>
</div>
</div>
</body></html>