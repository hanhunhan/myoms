<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>采购申请</title>
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

</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <?php echo ($tabs); ?>
        </div>
        <div style="position: absolute;top:20px;right: 80px;"><label class="label label-danger">鼠标右击-上下文菜单</label></div>
        <?php echo ($form); ?>
        <input type="hidden" name="url_current" id="url_current" value="<?php echo U('Purchase/opinionFlow',$paramUrl);?>">
    </div>
</div>
<script type="text/javascript">
    //提交采购申请
    $("#sub_purchase").click(function () {
        var p_id = $('.itemlist').filter('.selected').attr('fid');
        var status = $("select[name=" + p_id + "_STATUS]").val();

        if (status == 0) {
            layer.confirm(
                    '确定要提交采购申请吗(采购申请所有采购明细都会被提交)？',
                    {title: '提交采购申请'},
                    function (index) {
                        var _this = this;
                        //是否超出垫资比例
                        _this.is_over = false;

                        $.ajax({
                            type: "get",
                            url: "<?php echo U('Api/is_over_payout_limit');?>",
                            async: false,
                            data: {"p_id": p_id, "type": "purchase"},
                            dataType: "JSON",
                            success: function (res) {
                                if(res.data.state==true)
                                    _this.is_over = true;
                            },
                            error: function () {
                                alert("网络错误，请重试~~");
                            }
                        });

                        if(_this.is_over){
                            if(!confirm("该项目成本已超出垫资额度或超出费用预算（总费用>开票回款收入*付现成本率）\n您确定要继续吗？"))
                                return false;
                        }

                        $.ajax({
                            type: "GET",
                            url: "<?php echo U('Purchase/check_purchase_list_by_pid');?>",
                            data: {'p_id': p_id},
                            dataType: "JSON",
                            success: function (data) {
                                if (data.status == 1) {
//                                    var url_current = $('#url_current').val();
//                                    var url = url_current + "/purchase_id/" + p_id;
                                    var url = "<?php echo U('Touch/Purchase/process');?>" + '/RECORDID/' + p_id;
                                    window.location.href = url;
                                }
                                else {
                                    layer.alert(data.msg, {icon: 2, closeBtn: false},
                                            function () {
                                                layer.close(index);
                                                window.location.reload();
                                            });
                                }
                            }
                        })
                    }
            );
        }
        else {
            layer.alert('未提交过的采购申请才能提交', {icon: 2});
        }
    });

    // 查看采购流程图
    $('#show_flow_step').click(function () {
        // 定义异步调用的处理函数
        // 获取工作流ID异步调用成功回调函数
        function getFlowIdSuccess(resp, status, xhr) {
            if (resp) {
                resp = JSON.parse(resp);
            }

            if (resp.status == 'noauth') {
                var message = resp.msg || '权限不足';
                layer.alert(message, {icon: 2});
                return;
            }

            if (resp.status) {
                var viewFlowUrl = "<?php echo U('Flow/viewFlow');?>";
                viewFlowUrl += '/FLOWID/' + resp.data;
                layer.open({
                    type: 2,
                    title: '流程图',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['90%', '90%'],
                    content: viewFlowUrl
                });

            } else {
                var message = resp.message || '获取工作流ID失败';
                layer.alert(message, {icon: 7});
            }
        }

        // 获取工作流ID异步调用失败回调函数
        function getFlowIdFail(xhr, status, error) {
            layer.alert('服务器访问异常，请稍后重试!', {icon: 2});
        }

        // 显示流程图
        var selectedItem = $('.contractinfo-table .itemlist.selected');
        if (!selectedItem) {
            layer.alert('请选择一条采购申请!', {icon: 7});
            return;
        }
        var fid = selectedItem.attr('fid');
        if (fid) {
            if ($("select[name='" + fid + "_STATUS']").val() == 0) {
                layer.alert('该采购申请尚未发起工作流!', {icon: 7});
                return;
            }
            $.ajax({
                url: "<?php echo U('Purchase/getFlowId');?>",
                data: {
                    'purchaseId': fid
                },
                success: getFlowIdSuccess,
                error: getFlowIdFail
            });
        }
    });

    // 是否显示分页内的新增按钮
    var isShowOptionBtn = "<?php echo ($isShowOptionBtn); ?>";
    if (isShowOptionBtn == DISPLAY_OPTION_BTN.HIDE) {
        $.each($('.page a'), function (index, elem) {
            if (REG_EXPS.ADD.test($(elem).text())
                    || REG_EXPS.COMMIT.test($(elem).text())) {
                $(elem).remove();
            }
        });
    }

    $('input[name="END_TIME"]').blur(function() {
        if (!$('input[name="END_TIME"]').val().trim()) return;

        var postDate = new Date($('input[name="END_TIME"]').val());
        var nowDate = new Date();

        if (postDate< nowDate) {
            alert('最晚送达时间不得小于当前时间');
            $('input[name="END_TIME"]').val('');
        }
    });
</script>
<textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // 附带上上次搜索的结果
    });
</script>
<link rel="stylesheet" type="text/css" href="./Public/css/context.standalone.css">
<script src="./Public/js/context.js?t=1"></script>
<script>
    var conTextMenu = '<?php echo ($CONTEXT_MENU); ?>';
    var conTextMenuObj =JSON.parse(conTextMenu);

    $(document).ready(function(){
        context.init({preventDoubleContext: false});
        context.settings({compress: true});
        context.attach('html', conTextMenuObj);

        $(document).on('mouseover', '.me-codesta', function(){
            $('.finale h1:first').css({opacity:0});
            $('.finale h1:last').css({opacity:1});
        });

        $(document).on('mouseout', '.me-codesta', function(){
            $('.finale h1:last').css({opacity:0});
            $('.finale h1:first').css({opacity:1});
        });
    });
</script>
</body>
</html>