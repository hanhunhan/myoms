<?php
include_once("../common/mysql.class.php");
include_once("../common/function.php");
 

include_once("../common/auth.php");
//$code= 'admin$����Ա';
//$_GET["authcode"] = get_authcode($code,'ENCODE');

$authcode = get_authcode($_GET["authcode"]);//var_dump($authcode);
//$authcode = $_GET["authcode"];
if(!$authcode){
	echo '��Ȩ��1��';
	exit;
	 
}

list($uid,$username) = explode("$",$authcode);

if(!$uid  ){
	echo '��Ȩ��2��';
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
    <title>����������֯��Χ�ʾ����</title>  
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
  <div class="panel-head"><strong>  <h1 style="text-align:center;">����������֯��Χ�ʾ����</h1>  </strong></div>
  <div class="body-content">
  <div  style="width:900px;margin-left: auto;margin-right: auto;font-size:16px;">
    <form method="post" class="form-x" action="question.php">
        <div class="form-group">
        
         

		  <h2  style="text-align:center;font-weight:bold;">��ӭ��</h2><br> 
		  <p>
		  ���ã�</p><br> 
		  <p style="text-indent:2em; ">
�ǳ���л���ܳ��ʱ����д������ʾ�</p><p style="text-indent:2em; "> ÿһλԱ���������������������������ǳ���Ҫ������ϣ�����������Ŀ���Թ�˾�Ļ��ͼ�ֵ���𵽴ٽ����ã�������������������������桢�͹۵ķ�������Թ�˾��֯��Χ����������Ī��İ�����</p><p style="text-indent:2em; ">  �����Ŷӳ�ŵ�����ϸ�֤���ε���������ԡ����ĸ�л���Ĳ��롣 

</p>
 <h2 style="text-align:center;font-weight:bold;">��д˵��</h2><br> 
 <p style="text-indent:2em; ">
 ����1-5�ְ�ť�£�ѡ�����Ͽɵ�ѡ����С�5����ʾ�ǳ�ͬ�⣬��4����ʾ�Ƚ�ͬ�⣬��3����ʾһ�㣬��2����ʾ�Ƚϲ�ͬ�⣬��1����ʾ�ǳ���ͬ�⡣
 </p>



        </div>
		<div style="text-align:center;">
		<input type="checkbox" name="checktop" id="checktop" />�����Ķ���֪�����˵����
		</div>
        
      
      
       
        <div class="form-group">
          <div class="label">
			<label></label>
          </div>
        <div class="field"  style="text-align:center;">
          <button class="button bg-main icon-check-square-o"  disabled="disabled"  id="submitsb" type="submit"> �����ʾ����</button>
        </div>
      
    </form>
	</div>
  </div>
</div>
</div>
</body></html>