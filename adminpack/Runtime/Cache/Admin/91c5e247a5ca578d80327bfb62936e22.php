<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>ҳǩ����</title>
    <meta charset="GBK">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>-->
    <!--<link href="./Public/css/jquery-ui.css" type="text/css" rel="stylesheet"/>-->
    <!--<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/common.js"></script>-->
    <!--<script type="text/javascript" src="./Public/js/common.js"></script>-->
    <!--<script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>-->
    <!---->
    <!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>-->
    <!--<script type="text/javascript" src="./Public/js/jquery-ui.js"></script>-->
    <!---->
    <!--<script type="text/javascript" src="./Public/layer/layer.js"></script>-->
    <!--<link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all" />-->
    <!--<link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all" />-->
    <link href="Public/third/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="Public/css/style2.css?time=20160815" type="text/css" rel="stylesheet"/>
<link href="Public/css/jquery-ui.css" type="text/css" rel="stylesheet"/>
<link rel="stylesheet" href="./Tpl/css/jquery.treeview.css" />
<link rel="stylesheet" href="./Tpl/css/screen.css" />
<link rel="stylesheet" href="./Tpl/css/boxy.css" />
<link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all" />
<link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all" />
<!--select 2 style-->
<link rel="stylesheet" href="./Public/select2/select2.css" type="text/css" media="all"/>

<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>
<script type="text/javascript" src="./Public/validform/js/common.js?time=20160815"></script>


<script type="text/javascript" src="./Public/js/common.js?time=20160815"></script>
<script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>
<script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>
<script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>
<script src="Public/third/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="./Public/js/jquery-ui.js"></script>

<script src="./Tpl/js/jquery.cookie.js" type="text/javascript"></script>
<script src="./Tpl/js/jquery.treeview.js" type="text/javascript"></script>
<script src="./Tpl/js/jquery.boxy.js" type="text/javascript"></script>

<script type="text/javascript" src="Public/js/jquery.nicescroll.min.js"></script>

<!--������-->
<script language="javascript" type="text/javascript" src="./Public/layer/layer.js"></script>
<script language="javascript" type="text/javascript" src="./Public/layer/extend/layer.ext.js"></script>
<!--select2 js-->
<script type="text/javascript" src="./Public/select2/select2.js"></script>
<script type="text/javascript" src="./Public/js/template.js"></script>

<script>
    $(function() {
//        $('html').niceScroll();
        // ��ȡ�ϴε���������
        var lastFilterResult = '<?php echo ($lastFilter); ?>';
        $('#last_filter_result').text(lastFilterResult);
    });
</script>

    <style>
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            /* ��ֹˮƽ������ */
            overflow-x: hidden;
        }
    </style>

</head>
<body>

<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab  ">
            <ul>
                <li class="selected"><a href="<?php echo U('Tabs/index');?>">ҳǩ����</a></li>
            </ul>
        </div>
        <?php echo ($form); ?>
        <div>
        </div>
        <script>
            function openrolelist(obj) {
                var thisid = $(obj).parent().attr('fid');
                layer.open({
                    type: 2,
                    title: 'ҳǩ����',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['580px', '90%'],
                    content: '<?php echo U("Tabs/rolelist");?>' + '&tabid=' + thisid //iframe��url
                });
            }
        </script>
    </div>
</div>
</body>
</html>