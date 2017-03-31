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

    <style>
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            /* 防止水平滚动条 */
            overflow-x: hidden;
            z-index: 9999;
        }
    </style>
</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <ul>
                <li class="selected"><a href="<?php echo U('Case/projectlist');?>">项目列表</a></li>
            </ul>
        </div>
        <?php echo ($form); ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        // 控制浮动菜单的显示，点击在项目可操作按钮上，不弹出浮动菜单
        $(".itemlist").unbind('click');
        $(".itemlist").on("click", function(event) {
            var target = $(event.target);
            if (target.hasClass('btn')) {
                target = target.parent();
            }
            var val = target.find('input').val();
            if (!val || val == 1) {
                var table_top = $(this).offset().top + 28 + "px";
                var tr_index = $("tr").index($(this));
                var list_length = $(".itemlist").length;
                var ab_length = list_length - tr_index;
                var tab_top = $("table").height() + 22 + "px";
                $('.buttons').css("position", "absolute");
                $(".buttons").css("top", table_top);
                $(".buttons").toggleClass("btn_show");
            }
        });

        $(".contractinfo-table tbody tr").click(function (event) {
            $(this).siblings().removeClass("selected");
            $(this).addClass("selected");
        });
        $(".contractinfo-table tbody tr").each(function (index) {
            var tagP = $(this).attr("fid") + "_" + "PSTATUS";
            var tagB = $(this).attr("fid") + "_" + "BSTATUS";
            var tagM = $(this).attr("fid") + "_" + "MSTATUS";
            var tagA = $(this).attr("fid") + "_" + "ASTATUS";
            var tagAC = $(this).attr("fid") + "_" + "ACSTATUS";
            // 非我方收筹项目状态
            var tagSC = $(this).attr('fid') + '_' + 'SCSTATUS';
            var thisid = $(this).attr("fid");

            // 项目状态
            var COLUMNS = {
                'P': $('[name="' + tagP + '"]'),  // 立项列
                'B': $('[name="' + tagB + '"]'),  // 电商列
                'M': $('[name="' + tagM + '"]'),  // 分销列
                'A': $('[name="' + tagA + '"]'),  // 硬广列
                'AC': $('[name="' + tagAC + '"]'),// 活动列
                'SC': $('[name="' + tagSC + '"]') // 非我方收筹项目列
            };

            // 非电商项目不显示案场预收入和确认案场预收入
            if ((!COLUMNS.B.val() || COLUMNS.B.val() == '') && (!COLUMNS.M.val() || COLUMNS.M.val() == '')) {
                // 案场与预收入
                var tagAdvance = $(this).attr('fid') + '_BUSINESS_INCOME';
                $('[name="' + tagAdvance + '"]').closest('td').find('span').first().text('');

                // 确认案场预收入
                var tagConfirmAdvances = $(this).attr('fid') + '_CONFIRM_INCOME';
                $('[name="' + tagConfirmAdvances + '"]').closest('td').find('span').first().text('');
            }

            // 用匿名函数进行封装，后面的一样
            // 立项状态
            (function () {
                if (COLUMNS.P.val() >= 2) {
                    if (COLUMNS.AC.val() >= 1) {  // 如果是活动推广项目
                        COLUMNS.P.closest('td').click(function () {
                            var url = appUrl + '/Activ/activPro&tabNum=8&showOpinion=1&prjid=' + thisid;
                            window.location.href = url;
                        }).css({"cursor": 'pointer'});
                    } else if (COLUMNS.SC.val() >= 1) {  // 如果包含非我方收筹项目
                        if (COLUMNS.M.val() >= 1) {  // 非我方收筹与分销的组合
                            COLUMNS.P.closest('td').click(function () {
                                var url = appUrl + '/House/projectDetail&prjid=' + thisid + '&tabNum=20';
                                location.href = url;
                            }).css({'cursor': 'pointer'});

                        } else {  // 只有非我方收筹
                            COLUMNS.P.closest('td').click(function () {
                                var url = appUrl + '/House/projectDetail&prjid=' + thisid + '&tabNum=23';
                                location.href = url;
                            }).css({'cursor': 'pointer'});
                        }
                    } else {  // 其他项目
                        COLUMNS.P.closest('td').click(function () {
                            var url = appUrl + '/House/projectDetail&prjid=' + thisid + '&tabNum=20';
                            window.location.href = url;
                        }).css({"cursor": 'pointer'});
                    }
                }
            })();

            // 电商状态
            (function () {
                if (COLUMNS.B.val() >= 2) {
                    COLUMNS.B.closest('td').click(function () {
                        var url = appUrl + '/Business/index&prjid=' + thisid;
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();

            // 分销状态
            (function () {
                if (COLUMNS.M.val() >= 2) {
                    COLUMNS.M.closest('td').click(function () {
                        var url = appUrl + '/MemberDistribution/index/prjid/' + thisid + '/TAB_NUMBER/4/CASE_TYPE/fx';
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();

            // 硬广状态
            (function () {
                if (COLUMNS.A.val() >= 2) {
                    COLUMNS.A.closest('td').click(function () {
                        var url = appUrl + '/Advert/index/is_from/1/CASE_TYPE/yg&prjid=' + thisid;
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();

            // 活动列
            (function () {
                if (COLUMNS.AC.val() >= 2) {
                    COLUMNS.AC.closest('td').click(function () {
                        var url = appUrl + '/Advert/index/is_from/2/CASE_TYPE/hd/&prjid=' + thisid;
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();

            // 非我方收筹状态
            (function () {
                if (COLUMNS.SC.val() >= 2) {
                    COLUMNS.SC.closest('td').click(function () {
                        var url = appUrl + '/Advert/index/is_from/2/CASE_TYPE/fwfsc/&prjid=' + thisid;
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();
        });
    });

    //业务津贴
    function benefits() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_PSTATUS";   //立项
        var tagB = thisid + "_BSTATUS";   //电商
        var tagM = thisid + "_MSTATUS";   //分销
        var tagAS = thisid + "_ASTATUS";    //硬广
        var tagCP = thisid + "_CPSTATUS";  //产品
        var tagAC = thisid + "_ACSTATUS"; //独立活动
        var tagSC = thisid + "_SCSTATUS"; // 非我方收筹

        var SC_STATUS = $("[name='" + tagSC + "']").val();  // 非我方收筹状态
        var M_STATUS = $("[name='" + tagM + "']").val();  // 分销状态
        if ($("[name='" + tagCP + "']").val()) {
            layer.alert('产品项目不能申请津贴', {icon: 0});
            return false;
        }
//        if (SC_STATUS && M_STATUS) {  // 非我方收筹与分销的组合
//            // todo
//        } else if ($("[name='" + tagM + "']").val() && !$("[name='" + tagB + "']").val()) {
//            layer.alert('分销项目不能申请津贴', {icon: 0});
//            return false;
//        }
        if (thisid) {
            if ($("[name='" + tagP + "']").val() == 3 || $("[name='" + tagAS + "']").val() == 2 || $("[name='" + tagAS + "']").val() == 4) {
                var url = appUrl + '/Benefits/benefits&prjid=' + thisid + "&TAB_NUMBER=14";
                window.location.href = url;
            } else  layer.alert('该项目立项未办结或已被终止', {icon: 0});
        }
        else layer.alert('请先选择项目', {icon: 0});
    }

    //预算外其他费用
    function otherBenefits() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS"; //立项状态
        var tagCP = thisid + "_CPSTATUS";//产品
        var tagAC = thisid + "_ACSTATUS";//独立活动
        var tagA = thisid + "_ASTATUS"; //硬广
        var tagM = thisid + "_MSTATUS";   // 分销
        var tagB = thisid + "_BSTATUS";   //电商
        var tagSC = thisid + "_SCSTATUS"; // 非我方收筹

        var SC_STATUS = $("[name='" + tagSC + "']").val();  // 非我方收筹状态
        var M_STATUS = $("[name='" + tagM + "']").val();  // 分销状态

//        if (SC_STATUS && M_STATUS) {
//            // todo
//        } else if ($("[name='" + tagM + "']").val() && !$("[name='" + tagB + "']").val()) {
//            layer.alert('分销项目不能申请预算外费用', {icon: 0});
//            return false;
//        }

        if ($("[name='" + tagCP + "']").val() || $("[name='" + tagA + "']").val() || $("[name='" + tagAC + "']").val()) {
            layer.alert('只有包含<span style="color:red">电商</span>、<span style="color:red">分销</span>或<span style="color:red">非我方收筹</span>业务才能申请预算外费用！', {icon: 0});
            return false;
        }
        if (thisid) {
            if ($("[name='" + tagP + "']").val() == 3) {
                var url = appUrl + '/Benefits/otherBenefits&prjid=' + thisid + "&TAB_NUMBER=15";
                window.location.href = url;
            } else {
                layer.alert('该项目立项未办结或已被终止', {icon: 0});
            }
        } else {
            layer.alert('请先选择项目', {icon: 0});
        }
    }

    // 资金池费用
    function fundPoolCost() {
        var thisId = $(".contractinfo-table tbody .selected").attr('fid');
        if (parseInt(thisId) <= 0) {
            layer.alert('请选择一个项目', {icon: 0});
            return;
        }

        var P = $('[name=' + thisId + "_" + "PSTATUS]").val(), // 立项状态
            CP = $('[name=' + thisId + "_CPSTATUS]").val(),  // 产品
            AC = $('[name=' + thisId + "_ACSTATUS]").val(), // 独立活动
            A = $('[name=' + thisId + "_ASTATUS]").val(),  // 硬广
            M = $('[name=' + thisId + "_MSTATUS]").val(),  // 分销
            B = $('[name=' + thisId + "_BSTATUS]").val(),  // 电商
            SC = $('[name=' + thisId + "_SCSTATUS]").val();  // 非我方收筹

        // 只有“办结”状态才可以进行费用申请
        if (parseInt(P) !== 3) {
            layer.alert('该项目立项未办结或已被终止', {icon: 0});
            return;
        }

        // 只有电商有资金池费用相关概念 
        
        if (!B) {
            
            layer.alert('只有<span style="color:red">电商</span>业务才能申请资金池费用！', {icon: 0});
            return;
        } else {
            if (B != 2 && B != 4 && B != 3) {
                layer.alert('只有处于<span style="color:red">执行中</span>或者<span style="color:red">已经决算</span>或者<span style="color:red">周期结束</span>的业务才能资金池费用！', {icon: 0});
                return;
            }
        }

        $.ajax({
            url: "<?php echo U('House/ajaxIsFundPoolProject');?>",
            data: {
                projId: thisId  // 项目ID
            },
            success: onFundPoolCostStatus
        });

        function onFundPoolCostStatus(data) {
            if (data.status) {  // 是资金池项目
                location.href = appUrl + '/Benefits/fundPoolCost&prjId=' + thisId + "&TAB_NUMBER=15";
            } else {
                layer.alert('非资金池项目不能申请资金池费用', {icon: 0});
            }
        }
    }

    //垫资比例调整
    function payout_change() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";    //立项
        var tagAS = thisid + "_ASTATUS";       //硬广
        var tagCP = thisid + "_CPSTATUS";     //产品
        var tagAC = thisid + "_ACSTATUS";     //活动
        var tagSC = thisid + "_SCSTATUS";     // 非我方收筹
        if ($("[name='" + tagCP + "']").val() || $("[name='" + tagAS + "']").val() || $("[name='" + tagAC + "']").val()) {
            layer.alert('非<span style="color: red">电商</span>、<span style="color: red">分销</span>或<span style="color: red">非我方收筹</span>项目不能申请垫资比例', {icon: 0});
            return false;
        }
        if (thisid) {
            if ($("[name='" + tagP + "']").val() == 3) {
                var url = "<?php echo U('Payout_change/payout_change');?>" + "/prjid/" + thisid + "/layer/1";
                var iframe_payout_change = layer.open({
                    type: 2,
                    title: '请选择业务类型',
                    btn: ['确认', '取消'],
                    shadeClose: true,
                    shade: 0.8,
                    area: ['300px', '40%'],
                    content: [url, "no"],
                    yes: function (index, layero) { //或者使用btn1
                        var scale_type;
                        var dome = window.frames["layui-layer-iframe" + iframe_payout_change].document.getElementsByName("scale_type");
                        for (var i = 0, len = dome.length; i < len; i++) {
                            if (dome[i].checked == true) {
                                scale_type = dome[i].value;
                            }
                        }
                        var url = appUrl + '/Payout_change/payout_change/prjid/' + thisid + "/scale_type/" + scale_type + "/is_ajax/1";
                        $.ajax({
                            type: "post",
                            url: url,
                            data: "",
                            dataType: "JSON",
                            success: function (data) {
                                if (data.state == 0) {
                                    layer.alert(data.msg, {icon: 2});
                                } else if (data.state == 1) {
                                    var url = appUrl + '/Payout_change/payout_change/prjid/' + thisid + "/scale_type/" + scale_type;
                                    url = url + "/is_ajax/0/last_payout_list_id/" + data.msg;
                                    window.location.href = url;
                                }
                            }
                        })
                    },
                    cancel: function (index) {
                        layer.close(index);
                    }
                });
            } else {
                layer.alert('该项目立项未办结或已被终止', {icon: 0});
            }
        } else {
            layer.alert('请先选择项目', {icon: 0});
        }
    }

    //成本划拨
    function allocation() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');

        var tagP = thisid + "_" + "PSTATUS";    //立项
        var tagA = thisid + "_ASTATUS";         //硬广
        var tagCP = thisid + "_CPSTATUS";     //产品
        var tagAC = thisid + "_ACSTATUS";     //活动

        //规则选择
        var select = 0;
        $.ajax({
            url: "<?php echo U('Payout_change/payout_change');?>" + "/prjid/" + thisid + "/check_chengbenhuabo/1",
            dataType: "json",
            async: false,
            data: {
                prjid: thisid
            },
            success: function (data) {
                if (data.data && data.data.flag_count == 0) {
                    layer.alert('该项目不符合划拨条件！', {icon: 0});
                    return false;
                } else {
                    if (data.status == 'noauth') {
                        layer.alert(data.msg, {icon: 2});
                        return false;
                    }
                    select = 1;
                }
            }
        });

        /*******如果项目类型有两个及其两个以上符合要求*****/
        if (select == 1) {
            var url = "<?php echo U('Payout_change/payout_change');?>" + "/prjid/" + thisid + "/layer/1/chengbenhuabo/1";
            var iframe_payout_change = layer.open({
                type: 2,
                title: '请选择业务类型 和 是否扣非',
                btn: ['确认', '取消'],
                shadeClose: true,
                shade: 0.8,
                area: ['300px', '40%'],
                content: [url, "no"],
                yes: function (index, layero) { //或者使用btn1
                    var scale_type;
                    var koufei;

                    //业务类型
                    var dome = window.frames["layui-layer-iframe" + iframe_payout_change].document.getElementsByName("scale_type");
                    for (var i = 0, len = dome.length; i < len; i++) {
                        if (dome[i].checked == true) {
                            scale_type = dome[i].value;
                        }
                    }

                    //扣非类型
                    koufei = window.frames["layui-layer-iframe" + iframe_payout_change].document.getElementsByName("koufei")[0].value;

                    if (scale_type && koufei != '') {
                        //直接跳转过去
                        var url = appUrl + '/Cost/allocationApply/project_id/' + thisid + "/from/projectList/project_type_id/" + scale_type + "/koufei/" + koufei;
                        window.location.href = url;
                    }
                    else {
                        layer.alert("请选择业务类型和是否扣非！");
                    }
                }, cancel: function (index) { //或者使用btn2
                    layer.close(index);
                }
            });
        }
    }

    function createcase() {
        var url = appUrl + '/Case/createcase';
        window.location.href = url;
    }

    //项目变更
    function cchange() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";
        var acStatus = thisid + "_" + "ACSTATUS";
        var type;
        if ($("[name='" + acStatus + "']").val()) {
            type = 3;
        } else {
            type = 1;
        }

        if ($("[name='" + tagP + "']").val() == 3) {
            if (thisid) {
                var url = appUrl + '/Case/project_change/type/' + type + '/prjid/' + thisid;
                $.ajax({
                    url: url,
                    dataType: "json",
                    data: {
                        act: 'checkprjChange',
                        from: 'projectList'
                    },
                    success: function (data) {
                        //console.log(data);
                        if (data.status == 'y') {
                            window.location.href = url;
                        } else  layer.alert(data.info, {icon: 0});

                    }

                });
            } else {
                layer.alert('请先选择项目', {icon: 0});
            }
        } else {
            if ($("[name='" + tagP + "']").val() == 0) {
                layer.alert('该类型无法进行项目变更', {icon: 0});
            } else layer.alert('请选择执行中的项目', {icon: 0});
        }
    }

    //项目下活动申请
    function activitiesap() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');

        var tagP = thisid + "_" + "PSTATUS";
        var tagAC = thisid + "_" + "ACSTATUS";
        var tagA = thisid + "_" + "ASTATUS";

        if ($("[name='" + tagAC + "']").val()) {
            layer.alert('独立活动不允许再申请活动！', {icon: 0});
        } else {
            if ($("[name='" + tagP + "']").val() == 3 || $("[name='" + tagA + "']").val() == 2 || $("[name='" + tagA + "']").val() == 4) {
                if (thisid) {
                    var url = appUrl + '/Activ/activProX/tabNum/8/prjid/' + thisid;
                    window.location.href = url;
                }
                else  layer.alert('请先选择项目', {icon: 0});
            } else   layer.alert('该项目立项未办结', {icon: 0});
        }
    }

    //标准调整
    function feescale_change_list() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";
        var tagB = thisid + "_" + "BSTATUS";
        var tagM = thisid + "_" + "MSTATUS";
        var tagCP = thisid + "_CPSTATUS";
        var tagSC = thisid + "_SCSTATUS";
        if ($("[name='" + tagCP + "']").val()) {
            layer.alert('产品项目不能申请标准调整', {icon: 0});
            return false;
        }
        if (thisid) {
            if (!$("[name = '" + tagB + "']").val() && !$("[name = '" + tagM + "']").val() && !$("[name = '" + tagSC + "']").val()) {
                layer.alert("该项目没有<span style='color:red'>电商</span>或<span style='color:red'>分销</span>业务，不能申请标准调整 !", {icon: 0});
                return false;
            }
            if ($("[name='" + tagP + "']").val() == 3) {
                url = appUrl + '/Feescale_change/feescale_change_list/prjid/' + thisid;
                window.location.href = url;
            }
            else {
                layer.alert('该项目立项未办结或已被终止', {icon: 0});
            }
        }
        else {
            layer.alert("请先选择项目", {icon: 0});
        }
    }

    //项目权限
    function project_auth() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";
        if (thisid) {
            //if($("[name='"+tagP+"']").val()==3){
            url = appUrl + '/House/projectAuth/prjid/' + thisid;
            window.location.href = url;
            //}else   layer.alert('该项目立项未办结');
        } else {
            layer.alert("请先选择项目", {icon: 0});
        }
    }

    //项目决算
    function finalaccounts() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');

        var tagP = thisid + "_" + "PSTATUS";
        var tagA = thisid + "_" + "ASTATUS";
        var tagCP = thisid + "_" + "CPSTATUS";
        var tagAC = thisid + "_" + "ACSTATUS";
        if (thisid) {
            if ($("[name='" + tagP + "']").val() == 3 && $("[name='" + tagA + "']").val() == 0 && $("[name='" + tagCP + "']").val() == 0 && $("[name='" + tagAC + "']").val() == 0) {
                url = appUrl + '/Case/project_finalaccounts/prjid/' + thisid;
                // window.location.href = url;

                $.ajax({
                    url: url,
                    dataType: "json",
                    data: {
                        act: 'checkproject'
                    },
                    success: function (data) {
                        if (data.status == 'y') {
                            window.location.href = url;
                        } else  layer.alert(data.info, {icon: 0});

                    }

                });
            } else if ($("[name='" + tagP + "']").val() == 4 && $("[name='" + tagA + "']").val() == 0 && $("[name='" + tagCP + "']").val() == 0 && $("[name='" + tagAC + "']").val() == 0) {
                window.location.href = appUrl + '/Case/project_finalaccounts/prjid/' + thisid;
            } else {
                if ($("[name='" + tagA + "']").val()) {
                    layer.alert('硬广类型项目不允许决算', {icon: 0});
                } else if ($("[name='" + tagCP + "']").val()) {
                    layer.alert('产品类型项目不允许决算', {icon: 0});
                } else if ($("[name='" + tagAC + "']").val()) {
                    layer.alert('独立活动类型项目不允许决算', {icon: 0});
                } else
                    layer.alert('该项目立项状态不允许决算', {icon: 0});
            }
        } else {
            layer.alert("请先选择要决算的项目", {icon: 0});
        }
    }
    //项目终止
    function termination_choose() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";
        var tagA = thisid + "_" + "ASTATUS";
        var tagCP = thisid + "_" + "CPSTATUS";
        var tagAC = thisid + "_" + "ACSTATUS";

        if (thisid) {
            if ($("[name='" + tagP + "']").val() == 3 && $("[name='" + tagA + "']").val() == 0 && $("[name='" + tagCP + "']").val() == 0 && $("[name='" + tagAC + "']").val() == 0) {
                layer.open({
                    type: 2,
                    title: '选择要终止的业务类型',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['280px', '40%'],
                    content: '<?php echo U("Case/project_termination");?>' + '&layer=1&prjid=' + thisid //iframe的url
                });
            } else if ($("[name='" + tagP + "']").val() == 5) {
                layer.alert('该项目立项状态不允许终止', {icon: 0});
            } else {
                if ($("[name='" + tagA + "']").val()) {
                    layer.alert('硬广类型项目不允许终止', {icon: 0});
                } else if ($("[name='" + tagCP + "']").val()) {
                    layer.alert('产品类型项目不允许终止', {icon: 0});
                } else if ($("[name='" + tagAC + "']").val()) {
                    layer.alert('活动类型项目不允许终止', {icon: 0});
                } else
                    layer.alert('该项目立项状态不允许终止', {icon: 0});
            }
        } else {
            layer.alert("请先选择要终止的项目", {icon: 0});
        }


    }
    function termination(str, blength) {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        url = appUrl + '/Case/project_termination/prjid/' + thisid;
        // window.location.href = url;

        if (blength == 0) {
            window.location.href = url;
        } else if (str) {
            $.ajax({
                url: url,
                dataType: "json",
                data: {
                    act: 'checkproject',
                    caseids: str
                },
                success: function (data) {
                    if (data.status == 'y') {

                        window.location.href = url;
                    } else  layer.alert(data.info, {icon: 0});

                }

            });

        } else   layer.alert('请选择终止的类型');
    }

    //上传附件
    function upload_files(){
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        if (thisid) {
                var url = "<?php echo U('Project/upload_files');?>" + "/prjid/" + thisid ;
           layer.open({
                    type: 2,
                    title: '上传附件',
                    shadeClose: true,
                    area : ['80%', '80%'],
                    content: url,
                    cancel: function (index) {
                        layer.close(index);
                    }
                });
        } else {
            layer.alert('请先选择项目', {icon: 0});
        }
    }

    //删除项目
    function delproject(obj) {
        if (confirm('确定删除?')) {
            var thisid = $(obj).parent().parent().attr('fid');
            var url = appUrl + '/Case/del_project/prjid/' + thisid;
            $.ajax({
                url: url,
                dataType: "json",
                data: {},
                success: function (data) {
                    if (data.status == 'y') {
                        layer.alert(data.info);
                        $(obj).parent().parent().remove();

                    } else  layer.alert(data.info, {icon: 0});
                }
            });
        }
    }
	//查看流程图
	function viewFlow(obj){
		var thisid = $(obj).parent().parent().attr('fid');
		var url = appUrl + '/Flow/viewFlow/prjid/' + thisid;
		window.open(url);
	}
	//立项提交
	function project_set(){
		var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";
        var acStatus = thisid + "_" + "ACSTATUS";
        var type;
        if ($("[name='" + acStatus + "']").val()) {
            type = 3;
        } else {
            type = 1;
        }

        if ($("[name='" + tagP + "']").val() == 2  ) {
            if (thisid) {
                var url = appUrl + '/Touch/ProjectSet/show/prjid/' + thisid;
                if (type == 3) {
                    url = appUrl + '/Touch/Activ/process/prjId/' + thisid;
                }
                window.location.href = url;
            } else {
                layer.alert('请先选择项目', {icon: 0});
            }
        } else {
            if ($("[name='" + tagP + "']").val() == 0  ) {
                layer.alert('该类型无法进行项目变更', {icon: 0});
            } else layer.alert('请选择未提交审核的项目', {icon: 0});
        }

	}
</script>
</body>
<script>
    var fixThTop;
    var fixThWidth;
    var thWidthArr = new Array();

    $(document).ready(function () {
        fixThWidth = $('.fixth').width();
        fixThTop = $('.fixth').offset().top;

        $(".contractinfo-table th").each(function(i){
            thWidthArr[i] = $(this).width();
        });

        $('.fixth').css("width",fixThWidth);
        $('.fixth').css("background-color","#3c8dbc");
        $('.fixth').css('z-index',99);
    });

    $(window).scroll(function() {
        var scrollTop = $(window).scrollTop();
        if (scrollTop > fixThTop) {
            $('.contractinfo-table th').each(function(i){
                $(this).width('');
                $(this).width(thWidthArr[i] + 'px');
            });
            $('.fixthdisplay').css("display","table-row");
            $('.fixth').css("position","absolute");
            $('.fixth').css("top",scrollTop);
        }
        else
        {
            $('.fixthdisplay').css("display","none");
            $('.fixth').css("position","");
            $('.fixth').css("top",fixThTop);
        }
    });
</script>

</html>