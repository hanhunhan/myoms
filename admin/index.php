<?php
include_once("../common/mysql.class.php");
include_once("../common/function.php");
include_once("../common/check.php");
 

?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>��̨��������</title>  
    <link rel="stylesheet" href="../css/pintuer.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="../js/jquery.js"></script>   
</head>
<body style="background-color:#f2f9fd;">
<div class="header bg-main">
  <div class="logo margin-big-left fadein-top">
    <h1><img src="images/y.jpg" class="radius-circle rotate-hover" height="50" alt="" />��̨��������</h1>
  </div>
  <div class="head-l"><a class="button button-little bg-green" href="##" target="_blank"><span class="icon-home"></span> ǰ̨��ҳ</a> &nbsp;&nbsp;<a href="##" class="button button-little bg-blue"><span class="icon-wrench"></span> �������</a> &nbsp;&nbsp;<a class="button button-little bg-red" href="login.php"><span class="icon-power-off"></span> �˳���¼</a> </div>
</div>
<div class="leftnav">
  <div class="leftnav-title"><strong><span class="icon-list"></span>�˵��б�</strong></div>
  <h2><span class="icon-user"></span>��������</h2>
  <ul style="display:block">
    <li><a href="set.php" target="right"><span class="icon-caret-right"></span>�ʼ�����</a></li>
    <li><a href="basetj.php" target="right"><span class="icon-caret-right"></span>����ͳ��</a></li>
    <li><a href="tongji1.php" target="right"><span class="icon-caret-right"></span>��ֵͳ��</a></li>  
    <li><a href="tongji2.php" target="right"><span class="icon-caret-right"></span>�Ա�ͳ��</a></li>   
    <li><a href="tongji3.php" target="right"><span class="icon-caret-right"></span>����ͳ��1</a></li>  
	 <li><a href="tongji4.php" target="right"><span class="icon-caret-right"></span>����ͳ��2</a></li>  
	  <li><a href="tongji5.php" target="right"><span class="icon-caret-right"></span>����ͳ��3</a></li>  
     
  </ul>   
   
</div>
<script type="text/javascript">
$(function(){
  $(".leftnav h2").click(function(){
	  $(this).next().slideToggle(200);	
	  $(this).toggleClass("on"); 
  })
  $(".leftnav ul li a").click(function(){
	    $("#a_leader_txt").text($(this).text());
  		$(".leftnav ul li a").removeClass("on");
		$(this).addClass("on");
  })
});
</script>
<ul class="bread">
  <li><a href="index.php" target="right" class="icon-home"> ��ҳ</a></li>
  <li><a href="##" id="a_leader_txt">��վ��Ϣ</a></li>
  <li><b>��ǰ���ԣ�</b><span style="color:red;">����</php></span>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp  </li>
</ul>
<div class="admin">
  <iframe scrolling="auto" rameborder="0" src="tongji1.php" name="right" width="100%" height="100%"></iframe>
</div>
<div style="text-align:center;">
<p>��Դ:<a href=" " target="_blank"> </a></p>
</div>
</body>
</html>

