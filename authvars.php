<?
	$arr_auth_admin_users = array("admin", "tanyinbing", "baijunhong", "yangluli");

	if($if_admin == true && !in_array($LOGIN_USER_ID,$arr_auth_admin_users)){
		echo('<script type="text/javascript">
			alert("�޴�Ȩ��");
			history.go(-1);
		</script>"');
		exit();
	}
?>