<?php
include_once("../common/mysql.class.php");
include_once("../common/function.php");
 
	$sql = "SELECT * FROM ".$DB_PREFIX."new_user_result where uid='".$_SESSION['zzfwuid']."' ";
	$count = $db->getOne($sql); 
	if($count){
		 

	 
 
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
  
  <div class="body-content">
  <div  style="width:900px;margin-left: auto;margin-right: auto;font-size:16px;">
    <form method="post" class="form-x" action="question.php">
        <div class="form-group">
        
         

		 
		  <p style="text-indent:2em;text-align:center; ">
��л������֯��Χ���еĻ�����ϡ�<br>
���ǽ���������ı�������ͽ��飬���������ĸ��ƣ�<br>
������ѧԺ��л���Ĵ���֧�֣�</p>
  



        </div>
		 
        
      
      
       
        
        
      
    </form>
	</div>
  </div>
</div>
</div>
</body></html>
<?php
	exit;
	}
?>