<?php
header("Content-Type:text/html; charset=utf-8");
$starttime = microtime(true);

for($i=1;$i<=5;$i++){
	/**********/
	$starttime = microtime(true);
	echo $v = file_get_contents("http://soap400.tel99.cn/searchInfo.aspx?D5=??&TelNumber=400????&StartTime=?????????&EndTime=??????????&TelNumberExt=400???");
	echo $runtime = microtime(true)-$starttime;
	echo "<br/>";
	/**********/
	$fpt=fopen('./log/ytlog.txt',"a+"); 
	fwrite($fpt,date("Y-m-d H:i:s")."----".$runtime."\n"); 
	fclose($fpt);
	sleep(1);
}
?>