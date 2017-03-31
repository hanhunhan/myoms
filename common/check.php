<?php
if(!$_SESSION['username']){
	Jalert('гКох╣гб╪','login.php');
	//$locl = $_SERVER['HTTP_HOST'];
	//header("Location:login.php"); 
	exit;
}
?>