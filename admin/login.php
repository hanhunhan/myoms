<?php
include_once("../common/mysql.class.php");
include_once("../common/function.php");
if($_REQUEST['name']){  
	$sql = "SELECT count(id) as count FROM ".$DB_PREFIX."new_admin_user where username='".$_REQUEST['name']."'  and password=md5('".$_REQUEST['password']."')";
	$count = $db->getOne($sql);  
	if($count['count']){
		$_SESSION['username'] = $_REQUEST['name'];
		header("Location:index.php"); 
	}else{
		Jalert('�˺Ż����벻��ȷ');
	}
}

?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="renderer" content="webkit">
    <title>��¼</title>  
    <link rel="stylesheet" href="../css/pintuer.css">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="../js/jquery.js"></script>
    <script src="../js/pintuer.js"></script>  
</head>
<body>
<div class="bg"></div>
<div class="container">
    <div class="line bouncein">
        <div class="xs6 xm4 xs3-move xm4-move">
            <div style="height:150px;"></div>
            <div class="media media-y margin-big-bottom">           
            </div>         
            <form action="login.php" method="post">
            <div class="panel loginbox">
                <div class="text-center margin-big padding-big-top"><h1>��̨��������</h1></div>
                <div class="panel-body" style="padding:30px; padding-bottom:10px; padding-top:10px;">
                    <div class="form-group">
                        <div class="field field-icon-right">
                            <input type="text" class="input input-big" name="name" placeholder="��¼�˺�" data-validate="required:����д�˺�" />
                            <span class="icon icon-user margin-small"></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="field field-icon-right">
                            <input type="password" class="input input-big" name="password" placeholder="��¼����" data-validate="required:����д����" />
                            <span class="icon icon-key margin-small"></span>
                        </div>
                    </div>
                    <!--div class="form-group">
                        <div class="field">
                            <input type="text" class="input input-big" name="code" placeholder="��д�Ҳ����֤��" data-validate="required:����д�Ҳ����֤��" />
                           <img src="images/passcode.jpg" alt="" width="100" height="32" class="passcode" style="height:43px;cursor:pointer;" onclick="this.src=this.src+'?'">  
                                                   
                        </div>
                    </div-->
                </div>
                <div style="padding:30px;"><input type="submit" class="button button-block bg-main text-big input-big" value="��¼"></div>
            </div>
            </form>          
        </div>
    </div>
</div>

</body>
</html>