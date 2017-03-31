<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="gb2312">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>365经营管理系统后台</title>

    <!-- Bootstrap 3.3.6 -->
    <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="./Public/css/signin.css" rel="stylesheet">

</head>
<script>
    function changeVerify(){
        var timenow = new Date().getTime();
        document.getElementById('verifyImg').src="<?php echo U('Index/verify');?>&t="+timenow;
    }
</script>
<body>

<div class="signin">
    <div class="signin-head"><img src="./Public/images/tit.png" alt=""></div>
    <div class="signin-form-div">
    <form class="form-signin" role="form" action="<?php echo U('Index/login');?>" method="post">
        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i>
            </span> <input type="text" class="form-control" id="uname" name="uname" placeholder="用户名">
        </div>

        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i>
            </span> <input type="password" class="form-control" id="psw" name="psw" placeholder="密码">
        </div>

        <div class="input-group">
            <span class="input-group-addon"><i class="glyphicon glyphicon-barcode"></i>
            </span>
            <input type="text" class="form-control postcode" id="postcode" name="postcode" placeholder="验证码">
            <span class="input-group-addon postcode" ><a href="javascript:;" class="login-checknum">
                <img onclick="javascript:changeVerify()" title="看不清? 点击换验证码！" id="verifyImg" class="verifyImg" src="<?php echo U('Index/verify');?>"></a>
            </span>
        </div>

        <input type="hidden" name="act" value="login" />
        <button class="btn btn-lg btn-primary btn-block" type="submit">登录</button>
    </form>
    </div>
    <div class="copy">&copy2006-2016 江苏三六五网络股份有限公司 </div>
</div>

</body>
</html>