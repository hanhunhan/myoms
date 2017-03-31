<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'
        'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html>
<head>
    <title>ҳ����ʾ</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv='Refresh' content='{$waitSecond};URL={$jumpUrl}'>
    <style>
        html, body{margin:0; padding:0; border:0 none;font:14px Tahoma,Verdana;line-height:150%;background:white} a{text-decoration:none; color:#174B73; border-bottom:1px dashed gray} a:hover{color:#F60; border-bottom:1px dashed gray} div.message{margin:10% auto 0px auto;clear:both;padding:5px;border:1px solid silver; text-align:center; width:45%} span.wait{color:blue;font-weight:bold} span.error{color:red;font-weight:bold} span.success{color:blue;font-weight:bold} div.msg

        {margin:20px 0px}
    </style>
</head>
<body>
<div class="message">
    <div class="msg">
        <present name="message">
            <span class="success">{$msgTitle}{$message}</span>
            <else/>
            <span class="error">{$msgTitle}{$error}</span>
        </present>
    </div>
    <div class="tip">
        <present name="closeWin">
            ҳ�潫�� <span class="wait">{$waitSecond}</span> ����Զ��رգ��������ȴ����� <a href="{$jumpUrl}">����</a> �ر�
            <else/>
            ҳ�潫�� <span class="wait">{$waitSecond}</span> ����Զ���ת���������ȴ����� <a href="{$jumpUrl}">����</a> ��ת
        </present>
    </div>
</div>
<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
<script>
    $(function () {
        var waitSeconds = $('.wait').text();
        var countDown = setInterval(function() {
            waitSeconds -= 1;
            $('.wait').text(waitSeconds);
            if (waitSeconds == 0) {
                clearInterval(countDown);
            }
        }, 1000);
    });
</script>
</body>
</html>