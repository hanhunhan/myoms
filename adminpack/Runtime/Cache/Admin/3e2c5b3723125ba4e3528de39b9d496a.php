<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk"/>
    <title>365经营管理后台</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./Public/css/style.css" type="text/css" rel="stylesheet"/>
    <script src="./Public/js/jquery.js" type="text/javascript"></script>
</head>

<frameset rows="50,*" cols="*" frameborder="no" border="0" framespacing="0">
    <frame src="<?php echo U('Index/top');?>" name="topFrame" scrolling="No" noresize="noresize" id="topFrame" title="topFrame"
           frameborder="no" border="0" framespacing="0"/>
    <frameset cols="230,*" class="mainIframe active" id="topmainIframe" frameborder="no" border="0" framespacing="0">
        <frame src="<?php echo U('Index/left');?>" name="leftFrame" scrolling="auto" noresize="noresize" id="leftFrame"
               title="leftFrame"/>
        <frame src="<?php if(empty($url)): echo U('Index/welcome'); else: ?> <?php echo ($url); endif; ?>" name="mainFrame" id="mainFrame"
               title="mainFrame"/>
    </frameset>
</frameset>
<noframes>
    <body>
    </body>
</noframes>
</html>