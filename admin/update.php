<?
	include_once("inc/auth.php");
	include_once("inc/utility_all.php");

	$connection = OpenConnection();
	include_once("vars.php");

	// php ��֤������

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

		// �ʼ�֪ͨ
		$SUBJECT = "�������� ".$USER_NAME." ���С���֯����������";
		$CONTENT = "�𾴵�".$TO_USER_NAME."��<br /><br />�������ڹ�˾��֯������������Ҫ���ֳ������� ".$USER_NAME." ���С���֯������������������2���������ڣ���½����OAϵͳ��������߲������������ġ��͹ۡ��������������ǽ������ṩ����Ϣ�ϸ��ܣ��ٴθ�л���Ļ������룡<br /><br />�������߲�����ַ����½����OA->�ҵİ칫��->��֯���ղ���";

		$sql = "INSERT INTO EMAIL(FROM_ID, TO_ID, SUBJECT, CONTENT, SEND_TIME, READ_FLAG, SEND_FLAG, DELETE_FLAG) VALUES ('$LOGIN_USER_ID', '$uid', '$SUBJECT', '$CONTENT', '".date("Y-m-d H:i:s",time())."', '0', '1', '0')";
		exequery($connection,$sql);

		// SMS֪ͨ
		if($mobileno != ""){
			$msg = "��������½����OA->�ҵİ칫��->��֯���ղ���������<".$USER_NAME.">����֯����������";
			$url="http://mysms.house365.com:81/index.php/Interface/apiSendMobil/jid/29/depart/4/city/jt/mobileno/".$mobileno."/msg/?msg=".urlencode($msg);
			@file_get_contents($url);
		}
	}

	if($OP == 1) header("location:modify.php?OP=1&ID=".$ID.$url_next);
	else header("location:index.php?t=1".$url_next);
?>