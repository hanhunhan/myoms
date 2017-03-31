<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>项目列表</title>
    <meta charset="GBK">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <!--<link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>-->
    <!--<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/common.js"></script>-->
    <!--<script type="text/javascript" src="./Public/js/common.js"></script>-->
    <!--<script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>-->

    <!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>-->

    <!--<link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all"/>-->
    <!--<link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all"/>-->
</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <ul>
                <li class="selected"><a href="<?php echo U('Case/createcase');?>">新增项目</a></li>
            </ul>
        </div>
        <div class="select-case-type">
            <h3 class="text-center">请选择业务类型</h3>

            <form id="checkform" action="" method="post" class="">
                <div class="case-type-container">
                    <div class="checkbox">
                        <label class="control-label">
                            <input class="casetype" name="casetype" value="ds" type="checkbox"/> 电商
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input class="casetype" name="casetype" value="fx" type="checkbox"/> 分销
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input class="casetype" name="casetype" value="yg" type="checkbox"/> 硬广
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input class="casetype" name="casetype" value="hd" type="checkbox"/> 活动
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input class="casetype" name="casetype" value="cp" type="checkbox"/> 产品
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input class="casetype" name="casetype" value="fwfsc" type="checkbox"/> 非我方收筹
                        </label>
                    </div>
                </div>
                <div class="handle-btn">
                    <input type="hidden" value="" name="checktype" id="checktypeid"/>
                    <input class="btn btn-primary" type="submit" value="保 存">
                    <input class="btn btn-default" type="button" onclick="window.location.href='<?php echo U('Case/projectlist');?>'"
                           value="关 闭">
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    appUrl = "__APP__";
    actionUrl = "__ACTION__";
    $(document).ready(function () {
        $("#checkform").submit(function (e) {
            var sign = 0;
            var checktype = '';
            $('input[name="casetype"]:checked').each(function () {
                if ($(this).val()) {
                    checktype += ',' + $(this).val();
                    sign = 1;
                }
            });

            if (sign == 0) {
                alert("请选择类型");
                return false;
            } else {
                $("#checktypeid").val(checktype);//return false;
            }
        });
    });
</script>
</body>
</html>