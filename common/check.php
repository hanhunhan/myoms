<?php
if(!$_SESSION['username']){
	Jalert('���ȵ�¼','login.php');
	//$locl = $_SERVER['HTTP_HOST'];
	//header("Location:login.php"); 
	exit;
}
?>