<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>业务人员</title>
    <meta charset="GBK">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Font Awesome 4.6.3-->
    <link href="//cdn.bootcss.com/font-awesome/4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <!-- Bootstrap 3.3.6 -->
    <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>
    <link href="Public/css/adminLTE.like.css" type="text/css" rel="stylesheet"/>
    <script type="text/javascript" src="Public/validform/js/jquery-1.9.1.min.js"></script>
    <script type="text/javascript" src="Public/js/jquery.nicescroll.min.js"></script>

    <!--[if lt IE 9]>
    <script src="./Public/js/html5shiv.min.js"></script>
    <script src="./Public/js/respond.min.js"></script>
    <![endif]-->
</head>
<body class="skin-red-light">
<!-- sidebar: style can be found in sidebar.less -->
<aside class="main-sidebar">
    <section class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <a href="<?php echo ($_SESSION['uinfo']['user_avatar']); ?>" target="_blank"><img src="<?php echo ($_SESSION['uinfo']['user_avatar']); ?>" class="" alt="User Image"></a>
            </div>
            <div class="pull-left info">
                <p><?php echo ($_SESSION['uinfo']['tname']); ?></p>
                <p class="user-gw"><?php echo ($_SESSION['uinfo']['user_gw']); ?></p>
                <!-- Status -->
                <a href="#" class="status"><i class="fa fa-circle text-success"></i> online</a>
            </div>
            <p class="clearfix"/>
            <div class="position"><strong><span class="label label-danger"><?php echo ($cityName); ?></span><span
                    class="label label-success"><?php echo ($groupName); ?></span></strong></div>
        </div>
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu">

            <?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li class="treeview">
                    <a href="#">
                        <i class="fa <?php echo ($menu_icons[$vo[id]]); ?>"></i>&nbsp;<span><?php echo ($vo["name"]); ?></span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <?php if(is_array($vo['smenu'])): $i = 0; $__LIST__ = $vo['smenu'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$smenu): $mod = ($i % 2 );++$i;?><li class="treeview-menu-item"><a
                                    href="<?php echo U($smenu[LOAN_ROLECONTROL].'/'.$smenu[LOAN_ROLEACTION].'?'.$smenu[param]);?>"
                                    target="mainFrame"><i
                                    class="glyphicon glyphicon-link"></i><?php echo ($smenu["LOAN_ROLENAME"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                    </ul>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </section>
</aside>
<script>
    $(function () {
        $(".j-close").click(function () {
            $(this).parent().parent().hide();
            $(".j-alertbg").hide();
        });
        $(".j-showalert").click(function () {
            $(".j-alertbg").show();
            var tag = "." + $(this).attr("id") + "-alert";
            $(tag).show();
        });
        $(".j-refresh").click(function () {
            location.reload();
        });
        $(".j-list tr").click(function () {
            $(this).siblings().removeClass("selected");
            $(this).addClass("selected");
            $(".j-number").text($(this).children().first().text());
        });

        $(".j-second").click(function () {
            if ($(this).siblings("ul").css("display") == 'none') {
                $(this).siblings("ul").show();
                $(this).css("background-position", "15px -88px");
                //隐藏前后ul对象
                var t = $(this).siblings("ul");
                $(this).parent().parent().find("ul").not(t).hide();
                $(this).parent().parent().find("a").not(this).css("background-position", "15px -118px");
            }
            else {
                $(this).siblings("ul").hide();
                $(this).siblings("a").css("background-position", "15px -118px");
            }
        });

        $('html').niceScroll();

        $('.sidebar-menu>li>a').click(function() {
            var that = $(this);
            var activeTreeview = $('.treeview.active');
            activeTreeview.find('ul.treeview-menu').slideUp('fast');

            if (that.parent('.treeview').hasClass('active')) {
                activeTreeview.removeClass('active');
            } else {
                that.siblings('ul.treeview-menu').toggle('fast');
                activeTreeview.removeClass('active');
                that.parent('.treeview').addClass('active');
            }
        });
    });
</script>
</body>
</html>