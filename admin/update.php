<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	$connection = OpenConnection();
	include_once("vars.php");

	// php 验证，暂略

	if($ID != ""){
		$sql="UPDATE {$tbl} SET title='".addslashes($title)."', pid='$pid', fuid='$TO_ID', tuid='$COPY_TO_ID' WHERE ID=$ID";
		exequery($connection,$sql);

		$tid = $ID;
	}
	else{
		$sql = "INSERT INTO {$tbl} (title, pid, uid, fuid, tuid) VALUES ('".addslashes($title)."', '$pid', '$LOGIN_USER_ID', '$TO_ID', '$COPY_TO_ID')";
		exequery($connection,$sql);

		$tid = mysql_insert_id();
	}

	$sql = "DELETE FROM zzqf_user_result WHERE TID='".$tid."'";
	exequery($connection,$sql);

	$sql = "SELECT USER_NAME FROM USER WHERE USER_ID='$TO_ID'";
	$rs = exequery($connection,$sql);
	$r = mysql_fetch_array($rs);
	$USER_NAME = $r[USER_NAME];

	$arr_tuid = explode(",",$COPY_TO_ID);
	foreach($arr_tuid as $uid){
		$uid = trim($uid);
		if($uid == "") continue;

		$sql = "INSERT INTO zzqf_user_result (tid, pid, uid, fuid, tuid) VALUES ('$tid', '$pid', '$LOGIN_USER_ID', '$TO_ID', '$uid')";
		exequery($connection,$sql);

		$sql = "SELECT USER_NAME, MOBIL_NO FROM USER WHERE USER_ID='$uid'";
		$rs = exequery($connection,$sql);
		$r = mysql_fetch_array($rs);
		$TO_USER_NAME = $r[USER_NAME];
		$mobileno = $r[MOBIL_NO];

		// 邮件通知
		$SUBJECT = "诚邀您对 ".$USER_NAME." 进行“组织气氛评估”";
		$CONTENT = "尊敬的".$TO_USER_NAME."：<br /><br />　　鉴于公司组织气氛评估的需要，现诚邀您对 ".$USER_NAME." 进行“组织气氛评估”，请您在2个工作日内，登陆个人OA系统，完成在线测评。请您放心、客观、公正评估，我们将对您提供的信息严格保密！再次感谢您的积极参与！<br /><br />　　在线测评地址：登陆个人OA->我的办公桌->组织气氛测评";

		$sql = "INSERT INTO EMAIL(FROM_ID, TO_ID, SUBJECT, CONTENT, SEND_TIME, READ_FLAG, SEND_FLAG, DELETE_FLAG) VALUES ('$LOGIN_USER_ID', '$uid', '$SUBJECT', '$CONTENT', '".date("Y-m-d H:i:s",time())."', '0', '1', '0')";
		exequery($connection,$sql);

		// SMS通知
		if($mobileno != ""){
			$msg = "诚邀您登陆个人OA->我的办公桌->组织气氛测评，参与<".$USER_NAME.">的组织气氛评估。";
			$url="http://mysms.house365.com:81/index.php/Interface/apiSendMobil/jid/29/depart/4/city/jt/mobileno/".$mobileno."/msg/?msg=".urlencode($msg);
			@file_get_contents($url);
		}
	}

	if($OP == 1) header("location:modify.php?OP=1&ID=".$ID.$url_next);
	else header("location:index.php?t=1".$url_next);
?>