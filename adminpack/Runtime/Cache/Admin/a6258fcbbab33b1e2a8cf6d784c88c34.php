<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta charset="GBK">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>-->
    <!--<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/common.js"></script>-->
    <!--<script type="text/javascript" src="./Public/js/common.js"></script>-->
    <!--<script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>-->
    <!--<script language="javascript" type="text/javascript" src="./Public/layer/layer.js"></script>-->
    <!--<link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all"/>-->
    <!--<link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all"/>-->
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

<!--弹出框-->
<script language="javascript" type="text/javascript" src="./Public/layer/layer.js"></script>
<script language="javascript" type="text/javascript" src="./Public/layer/extend/layer.ext.js"></script>
<!--select2 js-->
<script type="text/javascript" src="./Public/select2/select2.js"></script>
<script type="text/javascript" src="./Public/js/template.js"></script>

<script>
    $(function() {
//        $('html').niceScroll();
        // 获取上次的搜索条件
        var lastFilterResult = '<?php echo ($lastFilter); ?>';
        $('#last_filter_result').text(lastFilterResult);
    });
</script>

    <script>
        $(function () {
            $('#last_filter_result').text($('#last_filter_text').text());  // 附带上上次搜索的结果

            $("#financialConfirm2").click(function () {
                var paymentId = new Array();
                var i = 0;
                var confirmMethod = 2;
                var url = "<?php echo U('Financial/financialConfirm');?>";
                $("input[name= 'checkedtd']:checkbox").each(function () {
                    if ($(this).prop("checked") == true) {
                        paymentId[i] = $(this).val();
                        i += 1;
                    }
                });
                if (paymentId.length <= 0) {
                    layer.alert("请至少选择一条记录", {icon: 0});
                    return false;
                }
                $.ajax({
                    type: "post",
                    //url: "index.php?s=/Financial/financialConfirm",
                    url: "<?php echo U('Financial/financialConfirm');?>",
                    data: {paymentId: paymentId, confirmMethod: confirmMethod},
                    dataType: "text",
                    success: function (data) {
                        if (data == 0) {
                            layer.alert("您所选择的记录中包含已确认的记录，不能重复确认，请重新选择", {icon: 0});
                        } else if (data == 1) {
                            layer.alert("请选择至少一条记录", {icon: 0});
                        } else if (data == 2) {
                            layer.alert("系统错误", {icon: 2});
                        } else if (data == 3) {
                            layer.alert("确认成功", {icon: 1}, function (index) {
                                window.open(url, "_parent");
                                layer.close(index);
                            });

                        } else if (data == 4) {
                            layer.alert("确认失败", {icon: 2});
                        } else if (data == 5) {
                            layer.alert("所选用户中没有未确认款项或还没有交费记录", {icon: 0});
                        }
                    }
                })
            });

            $("#cancleConfirm2").click(function () {
                var paymentId = new Array();
                var i = 0;
                var cancleMethod = 2;
                $("input[name= 'checkedtd']:checkbox").each(function () {
                    if ($(this).prop("checked") == true) {
                        paymentId[i] = $(this).val();
                        i += 1;
                    }
                });
                if (paymentId.length <= 0) {
                    layer.alert("请至少选择一条记录", {icon: 0});
                    return false;
                }
                $.ajax({
                    type: "post",
                    url: "index.php?s=/Financial/cancleConfirm",
                    data: {paymentId: paymentId, cancleMethod: cancleMethod},
                    dataType: "text",
                    success: function (data) {
                        if (data == 0) {
                            layer.alert("您所选择的记录中包含未确认的记录，不能取消，请重新选择", {icon: 0});
                        } else if (data == 1) {
                            layer.alert("请选择至少一条记录", {icon: 0});
                        } else if (data == 2) {
                            layer.alert("系统错误", {icon: 2});
                        } else if (data == 3) {
                            // layer.alert("取消成功",{icon:1},function(index){window.open(url,"_parent");layer.close(index);});
                            layer.confirm('取消成功', {
                                btn: ['确定'], //按钮
                                icon: 1
                            }, function () {
                                url = "<?php echo U('Financial/financialConfirm',$paramUrl);?>";
                                window.open(url, "_parent");
                            }, function () {

                            });
                        } else if (data == 4) {
                            layer.alert("取消失败", {icon: 2});
                        } else if (data == 5) {
                            layer.alert("所选用户中没有被确认款项，无法取消确认", {icon: 2});
                        }
                        else if (data == 6) {
                            layer.alert("您，所选的记录中不包含已确认的记录", {icon: 2});
                        }

                    }

                })

            })
        })
    </script>
</head>
<body>
<div class="containter">
    <?php echo ($form); ?>
    <textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
</div>
</body>
</html>