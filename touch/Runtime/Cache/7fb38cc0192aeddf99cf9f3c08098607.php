<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <title></title>
    <meta http-equiv="content-type" content="text/html; charset=gbk"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,minimum-scale=1,user-scalable=no">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="stylesheet" type="text/css" href="./PUBLIC/CSS/business.css">
    <link rel="stylesheet" href="./PUBLIC/CSS/styles.css"/>
    <script type="text/javascript" src="./PUBLIC/JS/datejs/jquery-1.9.1.min.js" ></script>
    <script src="./PUBLIC/JS/valide.js" type="text/javascript"></script>
    <script>
        $(function(){
            var wrapheight = $('.wrapFir').height();
            var pinmuheight = $(window).height();
            if(wrapheight>=pinmuheight)
            {
                $('.wrapFir').css("height",wrapheight)
            }
            else{
                $('.wrapFir').css("height",pinmuheight)
            }
        })

        //form���
        function chkfrm(frm){
            if(frm.uname == ''){
                alert('�������û���');
                frm.uname.focus();
                return false;
            }

            if(frm.psw == ''){
                alert('����������');
                frm.psw.focus();
                return false;
            }

            return true;
        }
    </script>
</head>
<body>

<form onsubmit="return chkfrm(this)" id="login" method="post" action="">
    <div class="wrapFir">
        <div class="returnIndex">
            <a class="returnBtn" href=""></a>
            <p class="txt">365���ܰ칫</p>
        </div>
        <div class="header">
            <div class="header_ta"><img class="taimg" src="./PUBLIC/IMAGES/ta.png" alt=""/></div>
            <h1 class="headertxt">�û���¼</h1>
        </div>
        <div class="loginDiv">
            <input class="inputtxt" onblur="" type="hidden"  name="act" value="login" />
            <input class="inputtxt" onblur="" type="text"  name="uname" placeholder="�����������˺�">
            <input class="inputtxt" onblur="" type="password"  name="psw" placeholder="��������������" style="margin-bottom: 0px;">
        </div>
        <div class="loginbtnDiv">
            <button class="loginBtn">��¼</button>
        </div>
    </div>
</form>
</body>
</html>