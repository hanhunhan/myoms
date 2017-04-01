<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gbk"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>365�Է�������̨</title>
    <!--Font Awesome 4.6.3-->
    <link href="//cdn.bootcss.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <!-- Bootstrap 3.3.6 -->
    <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="./Public/css/style.css" type="text/css" rel="stylesheet"/>
    <link href="Public/css/adminLTE.like.css" type="text/css" rel="stylesheet"/>
    <script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
    <script src="//cdn.bootcss.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

    <!--[if lt IE 9]>
    <script src="./Public/js/html5shiv.min.js"></script>
    <script src="./Public/js/respond.min.js"></script>
    <![endif]-->
    <script>
        function redirect(channelid) {
            window.top.location.href = "<?php echo U('Index/index');?>" + '&channelid=' + channelid;
        }

        $(function () {
            $('#toggle_menu').click(function () {
                var mainFrame = $(window.parent.document).find('.mainIframe');
                if (mainFrame.hasClass('active')) {
                    mainFrame.attr("cols", '0,*');
                    mainFrame.removeClass('active');
                } else {
                    mainFrame.attr("cols", '230,*');
                    mainFrame.addClass('active');
                }
            });

            $('#workflow').click(function () {
                $(window.parent.document).find('#mainFrame').attr('src', "<?php echo U('Flow/workStep');?>");
            });
        });
    </script>
</head>
<body class="skin-blue">
<div class="">
    <div class="main-header">
        <!-- Logo -->
        <p href="index2.html" class="logo pull-left">
            <img src="Public/images/OMSlogo.png"/>
        </p>

        <div class="toggle-bar"><a id="toggle_menu" href="#" title="��ʾ/���ز˵�"><i class="fa fa-bars"></i></a></div>
        <!-- Header Navbar: style can be found in header.less -->
        <div class="navbar navbar-static-top pull-right">
            <!-- Navbar Right Menu -->
            <div class="navbar-custom-menu pull-left">
                <div class="city-box pull-left">
                    <div class="pull-left title">����</div>
                    <div class="pull-left list">
                        <select class="pull-left"
                                onchange="redirect(this.options[this.options.selectedIndex].value)"
                                id="redirectCity">
                            <?php if(is_array($powercity)): $i = 0; $__LIST__ = $powercity;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($vo != $channelid): ?><option value="<?php echo ($vo); ?>">---<?php echo ($cityname[$vo]); ?>---</option>
                                    <?php else: ?>
                                    <option value="<?php echo ($vo); ?>" selected='selected'>---<?php echo ($cityname[$vo]); ?>---</option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>

                <ul class="nav navbar-nav pull-left oms-menu-nav">
                    <li class="pull-left">
                        <a href="javascript:void(0)" id="workflow" title="������������" class="workflow">
                            <i class="fa fa-flag-o"></i>
                            <?php if($todoNum > 0): ?><span class="label label-danger todo-num"><?php echo ($todoNum); ?></span><?php endif; ?>
                        </a>
                    </li>
                    <li class="pull-left">
                        <a href="<?php echo U('Index/welcome');?>" target="mainFrame"><i
                                class="glyphicon glyphicon-user text-yellow"></i>&nbsp;��������</a>
                    </li>
                    <li class="pull-left">
                        <a href="#"><i class="glyphicon glyphicon-briefcase"></i>&nbsp;&nbsp;OA</a>
                    </li>
                    <li class="pull-left">
                        <a href="<?php echo U('Index/loginOut');?>"><i class="glyphicon glyphicon-off green-light"></i>&nbsp;�˳�</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>