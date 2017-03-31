<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>划拨明细</title>
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

</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <ul>
                <li class="selected"><a href="<?php echo U('Cost/allocationDetails',$paramUrl);?>">项目划拨</a></li>
                <li><a href="<?php echo U('Cost/opinionFlow',$paramUrl);?>">工作流审核</a></li>
            </ul>
        </div>
        <?php echo ($form); ?>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        //申请划拨
        $("#commit_allocation").click(function () {
            var commit_id = 0;
            commit_id = $(".itemlist.selected").attr("fid");

            if (commit_id == 0) {
                layer.alert('请选择一条记录,进行操作!', {icon: 2});
                return false;
            }
            window.location.href = 'index.php?s=/Cost/opinionFlow/&RECORDID=' + commit_id;
        });

        //划拨编辑
        $("#edit_allocation").click(function () {
            var commit_id = 0;
            commit_id = $(".itemlist.selected").attr("fid");

            if (commit_id == 0) {
                layer.alert('请选择一条记录,进行操作!', {icon: 2});
                return false;
            }

            var projectID = $("input[name='" + commit_id + "_ID']").val();

            window.location.href = 'index.php?s=/Cost/allocationApply/transfer_id/' + commit_id + '/step/1/project_id/' + projectID;
        });

        //划拨删除
        $("#del_allocation").click(function () {
            var commit_id = 0;
            commit_id = $(".itemlist.selected").attr("fid");
            var projectID = 0;

            if (commit_id == 0) {
                layer.alert('请选择一条记录,进行操作!', {icon: 2});
                return false;
            }

            projectID = $("input[name='" + commit_id + "_ID']").val();

            //划拨删除做一次确认
            if (confirm("您确定要删除该划拨记录吗?")) {
                $.ajax({
                    url: "<?php echo U('Cost/opAllocation');?>" + "/act/del/transfer_id/" + commit_id + '/project_id/' + projectID,
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        if (data.state) {
                            layer.alert('删除成功！', {icon: 0});
                            return false;
                        } else {
                            var msg = data.msg ? data.msg : '对不起，删除失败<br />只有<font color="#dc143c">未提交审核</font>的划拨工作流才能删除！';
                            layer.alert(msg, {icon: 0});
                            return false;
                        }
                    }
                });
            }
        });
    });
</script>
<textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // 附带上上次搜索的结果
    });
</script>
</body>
</html>