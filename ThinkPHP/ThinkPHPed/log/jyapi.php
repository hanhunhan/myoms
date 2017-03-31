<?php
error_reporting(7);
ini_set("display_errors",true);
header("Content-Type:text/html; charset=utf-8");
$soap = new SoapClient('http://interface.web4008.com/servicenowse.asmx?WSDL');

	$starttime = microtime(true);
	$sd["LoginName"]="4008170116";
	$sd["Pwd"]="365";

	$v = $soap->GetBalance($sd);


	var_dump(get_object_vars($v));echo "<br/>";

}
?>