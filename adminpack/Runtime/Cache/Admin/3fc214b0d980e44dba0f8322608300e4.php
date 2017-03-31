<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>采购人员界面</title>
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
            overflow-x: hidden;
        }

        .warehouse-info-input {
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .contractinfo-table table {
            border-collapse:separate;
            margin-left: 0;
            border: none;
        }
    </style>
</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <?php if($ischildren != 1 and $ischildren != 2): echo ($tabs); endif; ?>
        </div>
        <?php echo ($form); ?>
    </div>
    <div style="display:none; padding: 10px;" id='warehouse_info'>
        <table class="table table-bordered" align='center'>
            <tr>
                <td>品名</td>
                <td id='dialog_product_name'></td>
            </tr>
            <tr>
                <td>规格/型号</td>
                <td id='dialog_model_num'></td>
            </tr>
            <tr>
                <td>采购仓库库存数</td>
                <td id='dialog_warhouse_num'></td>
            </tr>
            <tr>
                <td>置换仓库库存数</td>
                <td id='displace_warhouse_num'></td>
            </tr>
            <tr>
                <td>已领用数</td>
                <td><input class="warehouse-info-input" type='text' name='used_num' id='dialog_used_num' value='0'
                           disabled=''></td>
            </tr>
            <tr>
                <td>本次领用数</td>
                <td><input class="warehouse-info-input" type='text' name='dialog_use_num' id='dialog_use_num' value=''>&nbsp;(减少领用数量填负数)
                </td>
            </tr>
        </table>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        $(".contractinfo-table tbody tr").click(function () {
            $(this).siblings().removeClass("selected");
            $(this).addClass("selected");
            var fid = $(this).attr('fid');
            $('[name*="_PROJECTNAME"]').closest('td').css('background', '#fff');
            $('[name="' + fid + '_PROJECTNAME"]').closest('td').css('background', '#eee');
        });

        $('.del-record').removeAttr('onclick').unbind('click').bind('click', function (event) {
            function rejectSuccess(res, status, xhr) {
                if (res) {
                    res = JSON.parse(res);
                }
                var msg;
                if (res.status == true || parseInt(res.status) == 1) {
                    msg = res.message || '操作成功';
                    alert(msg);
                    location.reload();
                } else {
                    msg = res.message || '操作失败';
                    alert(msg);
                }
            }

            function rejectError(xhr, status, err) {

            }

            event.preventDefault();
            var fid = $(this).attr('fid');
            $.ajax({
                url: '<?php echo U("Purchasing/ajaxRejectPurchasing");?>',
                type: 'POST',
                data: {
                    fid: fid
                },
                success: rejectSuccess,
                error: rejectError
            });
        });

        //选中执行函数
        function checkboxevent(obj) {
            var fid = $(obj).val();
            if ($(obj).is(':checked')) {
                if (!$("#selecttr" + fid).length) {
                    $(obj).after('<input name="selecttr[]" id="selecttr' + fid + '" value="' + fid + '" type="hidden">');
                }
            } else {
                $("#selecttr" + fid).remove();
                //不通过按钮取消采购，取消选中，自动取消采购
                cancel_purchase(fid);
                $('#edit_purchase').attr('fid', 0);
                $('#edit_purchase').html('采购');
                $('#edit_purchase').attr('operate_type', 'edit_purchase');
                $('#save_purchase').remove();
                //隐藏历史价格
                $('#lower_price').hide();
            }
        }

        //选中
        $('.checkedtd').each(function () {
            $(this).click(function () {
                checkboxevent(this);
            });
        });

        //全选
        $("#checkall").click(function () {
            if (this.checked) {
                $("input[name='checkedtd']").each(function () {
                    this.checked = true;
                    checkboxevent(this);
                });
            }
            else {
                $("input[name='checkedtd']").each(function () {
                    this.checked = false;
                    checkboxevent(this);
                });
            }
        });

        //默认隐藏
        $('#lower_price').hide();

        /***采购编辑***/
        $('#edit_purchase').click(function () {
            var updateCheckBoxStatus = function (checked) {
                if (checked) {
                    $("input[name='checkedtd']:checkbox").each(function () {
                        $(this).prop("disabled", checked);  // 不可用
                    });
                    $('[name="checkall"]').prop("disabled", checked);  // 全选按钮不可用
                } else {
                    $("input[name='checkedtd']:checkbox").each(function () {
                        $(this).prop("disabled", checked);  // 不可用
                        $(this).prop("checked", checked);
                    });
                    $('[name="checkall"]').prop("disabled", checked)  // 全选按钮不可用
                            .prop("checked", checked);  // 全选按钮不可用
                }
            };

            var setProjNameFieldFixed = function() {
                var fixedNextColumns = [];
                $(".contractinfo-table tr").each(function() {
                    var elem = $(this).children('td, th').eq(0);
                    fixedNextColumns.push(elem);
                });

                $(".contractinfo-table tr").each(function(index) {
                    var elem = $(this).children('td, th').eq(3);
                    elem.css({
                        'position': 'absolute',
                        'left': 10,
                        'top': fixedNextColumns[index].offset().top + 'px',
                        'width': '200px',
                        'padding': 0,
                        'margin': 0
                    });
                    var height = $(fixedNextColumns[index]).outerHeight(true);

                    var backgroundColor = '#fff';
                    if (index == 0) {
                        backgroundColor = '#3c8dbc';
                        elem.css('border', '1px solid #c5ccdc');
                    } else if (index == 1) {
                        backgroundColor = '#eee';
                    }

                    elem.css({
                        'background': backgroundColor,
                        'height': height + 'px',
                        'line-height': height + 'px',
                        'border-right': '1px solid #c5ccdc'
                    });
                });

                $('.contractinfo-table').css({
                    'overflow-x': 'scroll',
                    'overflow-y': 'visible'
                });

                $('.contractinfo-table table').css({
                    'margin-left': '196px',
                    'border-collapse': 'separate'
                });
            };

            var operate_type = $(this).attr('operate_type');
            if (operate_type == 'edit_purchase') {
                var purchaseId = new Array();
                $("input[name='checkedtd']:checkbox").each(function () {
                    if ($(this).prop("checked") == true) {
                        purchaseId[purchaseId.length] = $(this).val();
                    }
                });

                if (purchaseId.length <= 0) {
                    layer.alert('请至少选择一条采购明细', {icon: 2});
                    return false;
                }

                //更新按钮文本、按钮操作类型value，添加fid
                $(this).attr('fid', purchaseId.join('#'));
                $(this).html('取消采购');
                $(this).attr('operate_type', 'cancel_purchase');
                $(this).after('<a id="save_purchase" class="btn btn-info btn-sm" href="javascript:;" onclick="save_purchase()">保存采购</a>');
                $('#lower_price').show();
                $('#add_reim').hide();

                edit_purchase(purchaseId);
                updateCheckBoxStatus(true);  // 更新选择框的状态
                setProjNameFieldFixed();  // 设置项目名称为固定列
            } else if (operate_type == 'cancel_purchase') {  // 取消采购
                location.reload();  // todo
                return;
                var fid = $(this).attr('fid').split('#');
                cancel_purchase(fid);
                updateCheckBoxStatus(false);
                $(this).attr('fid', 0);
                $(this).html('采购');
                $(this).attr('operate_type', 'edit_purchase');
                $('#save_purchase').remove();
                $('#lower_price').hide();
                $('#add_reim').show();
            }
        });

        //大宗采购特殊显示(领用库存数量、领用总价、是否资金池、是否扣非字段内容建议展示为“-”)
        $(".itemlist").each(function () {
            var fid = $(this).attr('fid');
            var purchase_type = $("select[name=" + fid + "_TYPE]").val();

            if (purchase_type == 2) {
                $("input[name=" + fid + "_USE_NUM]").parent().siblings('span').html('--');
                $("input[name=" + fid + "_USE_TOATL_PRICE]").parent().siblings('span').html('--');
                $("input[name=" + fid + "_IS_FUNDPOOL]:checked").parent().parent('.spanhidden').siblings('span').html('--');
                $("input[name=" + fid + "_IS_KF]:checked").parent().parent('.spanhidden').siblings('span').html('--');
            }
        });

        // 采购
        function edit_purchase(fidList) {
            // 可编辑框
            var enableFieldEditor = function (fid) {
                // 费用发生时间
                $("input[name=" + fid + "_COST_OCCUR_TIME]").parent().show().siblings('span').hide();

                //购买价格可编辑
                $("input[name=" + fid + "_PRICE]").parent().show().siblings('span').hide();

                //购买数量可编辑
                $("input[name=" + fid + "_NUM]").parent().show().siblings('span').hide();
                var purchase_type = $("select[name=" + fid + "_TYPE]").val();

                //领用数量可编辑
                if (purchase_type == 1) {
                    $("input[name=" + fid + "_USE_NUM]").parent().show().siblings('span').hide();
                }

                // 供应商文本框
                $("select[name=" + fid + "_S_ID]").parent().hide().siblings('span').hide();

                //供应商文字
                var Supplier_text_val = $("select[name=" + fid + "_S_ID]").parent().siblings('span').html();
                $("select[name=" + fid + "_S_ID]").parent().after("<input name='" + fid + "_S_NAME' class = 'Supplier_Text' type = 'text' value='" + Supplier_text_val + "'>");

                //供应商编号
                var Supplier_id_val = $("select[name=" + fid + "_S_ID]").val();
                $("select[name=" + fid + "_S_ID]").parent().after("<input name='" + fid + "_S_ID_GET' type = 'hidden' value='" + Supplier_id_val + "'>");
            };

            // 供应商名称
            var enableSupplierAutoCompleteEditor = function (fid) {
                $("input[name=" + fid + "_S_NAME]").autocomplete({
                    source: function (request, response) {
                        var supplier_name = request.term;
                        $.ajax({
                            url: "<?php echo U('Supplier/get_supplier_by_keyword');?>",
                            type: "GET",
                            dataType: "JSON",
                            data: {'keyword': encodeURI(supplier_name)},
                            success: function (data) {
                                //判断返回数据是否为空，不为空返回数据。
                                if (data[0]['id'] > 0) {
                                    response(data);
                                    $("input[name=" + fid + "_S_ID_GET]").val('');
                                }
                                else {
                                    response(data);
                                    $("input[name=" + fid + "_S_ID_GET]").val('');
                                }
                            }
                        });
                    },
                    minLength: 1,
                    removeinput: 0,
                    select: function (event, ui) {
                        if (ui.item.id > 0) {
                            var supplier_id = ui.item.id;
                            $("input[name=" + fid + "_S_ID_GET]").val(supplier_id);
                            removeinput = 2;
                        } else {
                            removeinput = 1;
                            $("input[name=" + fid + "_S_ID_GET]").val('');
                            open_add_supplier_window(fid);
                        }
                    },
                    close: function (event) {
                        if (typeof(removeinput) == 'undefined' || removeinput == 1) {
                            $(this).val('');
                            $("input[name=" + fid + "_S_ID_GET]").val('');
                        }
                    }
                }).autocomplete("instance")._renderItem = function (ul, item) {
                    if (item.id > 0) {
                        var ul_text = item.label + '&nbsp;&nbsp;' + item.telno;
                    } else {
                        var ul_text = '无符合条件的供应商，点击添加';
                    }
                    return $("<li>")
                            .append("<a>" + ul_text + "</a>")
                            .appendTo(ul);
                };
            };

            // 为空值的编辑框设置默认的值
            var setDefaultValueToEmptyField = function (fid) {
                var useNum = $("[name='" + fid + "_USE_NUM']").val();  // 领用数量
                var buyNum = $("[name='" + fid + "_NUM']").val();  // 购买数量

                if (!parseInt(useNum) && !parseInt(buyNum)) {
                    var numLimit = $("[name='" + fid + "_NUM_LIMIT']").val();  // 申请数量
                    var priceLimit = $("[name='" + fid + "_PRICE_LIMIT']").val();  // 成交价
                    $("[name='" + fid + "_NUM']").val(numLimit);
                    $("[name='" + fid + "_PRICE']").val(priceLimit);
                }
            };

            for (var i = 0; i < fidList.length; i++) {
                var fid = fidList[i];  // 采购明细id
                enableFieldEditor(fid);  // 可编辑框
                enableSupplierAutoCompleteEditor(fid);  // 自动完成框
                setDefaultValueToEmptyField(fid);
            }

            // 编辑栏样式控制
            $('input[name*="_S_NAME"]').addClass('form-control').css('width', '160px');
            $('input.BUY_PRICE').css('width', '100px');
            $('input.BUY_NUM').css('width', '100px');
            $('input.COST_OCCUR_TIME').css('width', '170px');
        }

        //取消采购编辑
        function cancel_purchase(fidlist) {
            // 转换成数组统一处理
            if (fidlist.constructor != Array) {
                fidlist = [fidlist];
            }

            // 取消一条采购明细的采购
            var cancelOneItem = function (fid) {
                $("input[name=" + fid + "_PRICE]").parent().hide().siblings('span').show().siblings('.info').remove();
                $("input[name=" + fid + "_NUM]").parent().hide().siblings('span').show().siblings('.info').remove();
                $("input[name=" + fid + "_S_ID]").parent().hide().siblings('span').show().siblings('.info').remove();
                $("select[name=" + fid + "_S_ID]").parent().hide().siblings('span').show();
                $("input[name=" + fid + "_S_NAME]").remove();
                $("input[name=" + fid + "_S_ID_GET]").remove();
                $("input[name=" + fid + "_USE_NUM]").parent().hide().siblings('span').show().siblings('.info').remove();
                $("input[name=" + fid + "_COST_OCCUR_TIME]").parent().hide().siblings('span').show().siblings('.info').remove();
            };

            for (var i = 0; i < fidlist.length; i++) {
                var fid = fidlist[i];
                cancelOneItem(fid);
            }
        }

        /**提醒库存情况**/
        $('.USE_NUM').focus(function () {
                    $(this).blur();

                    /***当前行采购明细编号***/
                    var purchase_list_id = $(this).parents('tr').attr('fid');

                    /***品牌***/
                    var brand = $("input[name=" + purchase_list_id + "_BRAND]").val();
                    /***型号***/
                    var model = $("input[name=" + purchase_list_id + "_MODEL]").val();
                    /***名称***/
                    var product_name = $("input[name=" + purchase_list_id + "_PRODUCT_NAME]").val();
                    /***最高限价***/
                    var price_limit = parseFloat($("input[name=" + purchase_list_id + "_PRICE_LIMIT]").val());
                    //已领领用数量
                    var used_num = parseFloat($("input[name=" + purchase_list_id + "_USE_NUM]").val());
                    //购买数量
                    var status = parseInt($("select[name=" + purchase_list_id + "_STATUS]").val());
                    var buy_num = 0;
                    if (status) {
                        buy_num = parseInt($("input[name=" + purchase_list_id + "_NUM]").val());
                    }

                    if (brand != '' && model != '' && product_name != '' && price_limit > 0) {
                        $('#dialog_product_name').html(product_name);
                        $('#dialog_model_num').html(brand + '/' + model);
                        $('#dialog_used_num').val(used_num);

                        //异步查询符合条件的物品的库存情况
                        $.ajax({
                            url: "<?php echo U('Warehouse/ajax_get_warehouse_num');?>",
                            dataType: 'JSON',
                            data: {
                                'brand': brand,
                                'model': model,
                                'product_name': product_name,
                                'price_limit': price_limit
                            },
                            success: function (data) {
                                if (data.state == 1) {
                                    /***申请采购数量***/
                                    var apply_buy_num = $("input[name=" + purchase_list_id + "_NUM_LIMIT]").val();

                                    var total_num = parseInt(data.total_num);
                                    var apply_buy_num = parseFloat(apply_buy_num);
                                    var displace_total_num = parseInt(data.displace_total_num);

                                    //采购仓库库存情况
                                    $('#dialog_warhouse_num').html(total_num);

                                    // 置换仓库库存数
                                    $('#displace_warhouse_num').html(displace_total_num);

                                    //允许领用数量
                                    var enable_use_num = parseFloat(apply_buy_num - used_num - buy_num);

                                    if ((total_num + displace_total_num) > enable_use_num && enable_use_num > 0) {
                                        $('#dialog_use_num').val(enable_use_num);
                                    }
                                    else if ((total_num + displace_total_num) <= enable_use_num && enable_use_num > 0) {
                                        $('#dialog_use_num').val(total_num + displace_total_num);
                                    }
                                    else {
                                        $('#dialog_use_num').val(0);
                                    }
                                }
                                else if (data.state == 0) {
                                    //库存情况
                                    $('#dialog_warhouse_num').html(data.total_num);
                                    //$('#dialog_use_num').attr("readonly",true);
                                    //$('#dialog_use_num').attr("disabled","disabled");
                                    $('#dialog_use_num').val('0');
                                    $('#displace_warhouse_num').html('0');
                                }
                                else {
                                    var msg = data.msg ? data.msg : '功能异常';
                                    layer.alert(msg, {icon: 2});
                                    return false;
                                }
                            }
                        });
                    }

                    //弹框提醒库存情况
                    layer.open({
                        type: 1,
                        title: '库存情况',
                        btn: ['领 用', '取 消'],
                        area: ['800px', '400px'],
                        content: $('#warehouse_info')
                        //确认操作
                        , yes: function (index, layero) {
                            //申请领用数量
                            var apply_num = parseFloat($('#dialog_use_num').val());
                            //库存数量
                            var warehouse_num = parseFloat($('#dialog_warhouse_num').html());
                            var displace_warehouse_num = parseInt($("#displace_warhouse_num").html()); // 置换仓库中的库存数

                            if (apply_num != 0 && purchase_list_id > 0) {
                                if (apply_num > (warehouse_num + displace_warehouse_num)) {
                                    layer.alert('申请数量不能大于库存数量', {icon: 2});
                                    return false;
                                }

                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo U('Warehouse/ajax_get_from_warehouse2');?>",
                                    data: {
                                        'apply_num': apply_num,
                                        'purchase_list_id': purchase_list_id
                                    },
                                    dataType: "JSON",
                                    success: function (data) {
                                        if (data.state == 0) {
                                            layer.close(index);
                                            layer.alert(data.msg, {icon: 2});
                                        } else if (data.state == 1) {
                                            //成功领用个数
                                            var used_num = parseFloat(data.use_num);

                                            //填充领用总价
                                            $("input[name=" + purchase_list_id + "_USE_TOATL_PRIC]").val(data.use_total_price);
                                            $("input[name=" + purchase_list_id + "_USE_TOATL_PRICE]").parent().prev().html(data.use_total_price);

                                            //填充领用数量
                                            $("input[name=" + purchase_list_id + "_USE_NUM]").val(used_num);
                                            $("input[name=" + purchase_list_id + "_USE_NUM]").parent().hide();
                                            $("input[name=" + purchase_list_id + "_USE_NUM]").parent().prev().show().html(used_num);

                                            /***采购申请数量***/
                                            var apply_buy_num = $("input[name=" + purchase_list_id + "_NUM_LIMIT]").val();
                                            var wait_buy_num = apply_buy_num - used_num > 0 ? apply_buy_num - used_num : 0;

                                            //默认购买数量
                                            $("input[name=" + purchase_list_id + "_NUM]").val(wait_buy_num);

                                            // 更新采购状态
                                            var statusText = '未采购';
                                            if (data.purchase_status == 1) {
                                                statusText = '已采购';
                                            }
                                            $("[name=" + purchase_list_id + "_STATUS]").val(data.purchase_status);
                                            $("[name=" + purchase_list_id + "_STATUS]").parent().prev().text(statusText);

                                            layer.close(index);
                                            layer.open({
                                                content: data.msg,
                                                icon: 1,
                                                btn: ['确定'],
                                                yes: function() {
                                                    location.reload();
                                                }
                                            });
                                        } else {
                                            layer.close(index);
                                            var msg = data.msg ? data.msg : '操作异常';
                                            layer.alert(msg, {icon: 2, closeBtn: false},
                                                    function () {
                                                        window.location.reload();
                                                    });
                                        }
                                    }
                                })
                            }
                            else {
                                layer.alert('申请数量必须填写且大于0', {icon: 2});
                            }
                        }
                        //取消操作
                        , cancel: function (index) {
                            layer.close(index);
                        }
                    });
                }
        );
    });


    /***采购购买数量\供应商\采购金额填写***/
    function save_purchase() {
        // 获取采购数据
        function getPurchaseData(purchaseId) {
            if (purchaseId) {
                var purchaseList = purchaseId.split('#');

                var purchaseData = [];  // 采购详情信息
                for (var i = 0; i < purchaseList.length; i++) {
                    var oneRec = {}, purchase_id = purchaseList[i];
                    var supplier_id = parseInt($("input[name=" + purchase_id + "_S_ID_GET]").val());
                    var buy_price = parseFloat($("input[name=" + purchase_id + "_PRICE]").val());
                    var buy_num = parseFloat($("input[name=" + purchase_id + "_NUM]").val());
                    var use_num = parseFloat($("input[name=" + purchase_id + "_USE_NUM]").val());
                    var cost_occur_time = $("input[name=" + purchase_id + "_COST_OCCUR_TIME]").val();
                    if ((use_num > 0 || (buy_price > 0 && supplier_id > 0 && buy_num > 0)) && cost_occur_time) {
                        oneRec['purchase_id'] = purchase_id;
                        oneRec['supplier_id'] = supplier_id;
                        oneRec['buy_price'] = buy_price;
                        oneRec['buy_num'] = buy_num;
                        oneRec['use_num'] = use_num;
                        oneRec['cost_occur_time'] = cost_occur_time;  // 费用发生时间

                        purchaseData.push(oneRec);
                    } else {
                        layer.alert('采购无领用时,采购供应商、采购单价、采购数量、费用发生时间都必须填写', {icon: 2});
                        return false;
                    }
                }

                return purchaseData;
            }

            return false;
        }

        // 异步调用成功
        function ajaxSuccess(data, xhr, status) {
            if (data.status == 0) {
                layer.alert(data.msg, {icon: 2});
            }
            else if (data.status == 1) {
                layer.alert(data.msg, {icon: 1}, function (index) {
                    layer.close(index);
                    window.location.reload();
                });
            }
            else {
                var msg = data.msg ? data.msg : '操作异常';
                layer.alert(msg, {icon: 2, closeBtn: false});
            }
        }

        var purchase_id = $('#edit_purchase').attr('fid');
        var purchaseData = getPurchaseData(purchase_id);  // 采购详情信息
        if (!purchaseData) {
            return;
        }
        if (purchaseData.constructor == Array && purchaseData.length > 0) {

        }
        $.ajax({
            type: "POST",
            url: "<?php echo U('Purchasing/ajax_update_purchase_buy_info');?>",
            data: {purchase_data: purchaseData},
            dataType: 'JSON',
            success: ajaxSuccess
        });

        return;

        if (!isNaN(purchase_id)) {
            //查找其他参数是否已经填写
            var supplier_id = parseInt($("input[name=" + purchase_id + "_S_ID_GET]").val());
            var buy_price = parseFloat($("input[name=" + purchase_id + "_PRICE]").val());
            var buy_num = parseFloat($("input[name=" + purchase_id + "_NUM]").val());
            var use_num = parseFloat($("input[name=" + purchase_id + "_USE_NUM]").val());

            if (use_num > 0 || (buy_price > 0 && supplier_id > 0 && buy_num > 0)) {
                $.ajax({
                    type: "GET",
                    url: "<?php echo U('Purchasing/ajax_update_purchase_buy_info');?>",
                    data: {
                        data: purchaseData
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        if (data.status == 0) {
                            layer.alert(data.msg, {icon: 2});
                        }
                        else if (data.status == 1) {
                            layer.alert(data.msg, {icon: 1}, function (index) {
                                layer.close(index);
                                window.location.reload();
                            });
                        }
                        else {
                            var msg = data.msg ? data.msg : '操作异常';
                            layer.alert(msg, {icon: 2, closeBtn: false});
                        }
                    }
                })
            }
            else {
                layer.alert('采购无领用时,采购供应商、采购单价、采购数量都必须填写', {icon: 2});
            }
        }
        else {
            //提醒用户选中采购明细
            layer.alert('无法获取采购明细信息', {icon: 2});
        }
    }


    //判断选择
    function ischeck() {
        var count = 0;
        $('.checkedtd').each(function () {
            if (this.checked) {
                count++;
            }
        });

        return count;
    }

    //从合同中移除
    $(".delete_from_contract").click(function () {
        var purchase_details_id = $(this).parent().filter('.fedit').attr('fid');

        if (purchase_details_id > 0) {
            layer.confirm('确定要从合同中移除？', {
                        btn: ['确定', '取消'],
                        title: '取消合同关系?',
                        closeBtn: false
                    },
                    function (index, layero) {
                        //确认操作
                        $.ajax({
                            type: "POST",
                            url: '<?php echo U("Purchasing/delete_from_contract");?>',
                            data: {'purchase_details_id': purchase_details_id},
                            dataType: "JSON",
                            success: function (data) {
                                if (data.state == 0) {
                                    layer.close(index);
                                    layer.alert(data.msg, {icon: 2, closeBtn: false},
                                            function () {
                                                window.location.reload();
                                            });
                                }
                                else if (data.state == 1) {
                                    layer.close(index);
                                    layer.alert(data.msg, {icon: 1, closeBtn: false},
                                            function () {
                                                window.location.reload();
                                            });
                                }
                                else {
                                    layer.close(index);
                                    var msg = data.msg ? data.msg : '操作异常';
                                    layer.alert(msg, {icon: 2, closeBtn: false},
                                            function () {
                                                window.location.reload();
                                            });
                                }
                            }
                        })
                    },
                    function (index) {
                        layer.close(index);
                    });
        }
        else {
            var msg = data.msg ? data.msg : '操作异常，删除失败';
            layer.alert(msg, {icon: 2, closeBtn: false},
                    function () {
                        window.location.reload();
                    });
        }
    })

    //新增合同
    function addcontract() {
        var purchaseId = new Array();
        var i = 0;
        $("input[name='checkedtd']:checkbox").each(function () {
            if ($(this).prop("checked") == true) {
                purchaseId[i] = $(this).val();
                i += 1;
            }
        });

        if (i == 0) {
            layer.alert('请先选择采购明细', {icon: 2});
            return false;
        }

        /**异步处理请求***/
        $.ajax({
            type: "GET",
            url: "<?php echo U('Purchasing/add_contract');?>",
            data: {'purchaseId': purchaseId},
            dataType: 'JSON',
            success: function (data) {
                if (data.status == 0) {
                    layer.alert(data.msg, {icon: 2});
                }
                else if (data.status == 1) {
                    layer.alert(data.msg, {icon: 1}, function (index) {
                        layer.close(index);
                        window.location.reload();
                    });
                }
                else {
                    var msg = data.msg ? data.msg : '操作异常';
                    layer.alert(msg, {icon: 2, closeBtn: false});
                }
            }
        })
    }

    //打开已有合同列表窗口
    function aptocontract() {
        var demo = $(".registerform").Validform();
        var result = demo.check();
        if (result) {
            if (ischeck()) {
                if ($('input[name=aptocontractId]').length < 1)$('.registerform').append('<input type="hidden" name="aptocontractId" value="">');
                layer.open({
                    type: 2,
                    title: '选择已有合同',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['580px', '90%'],
                    content: '<?php echo U("Purchasing/contract?layer=1");?>' //iframe的url
                });
            } else {
                layer.alert('请先选择采购明细', {icon: 2});
            }
        }
        else {
            layer.alert('请先填写数据', {icon: 2});
        }
    }


    //提交选择的已有合同
    function submitaptocontract() {
        var contract_id = $('input[name=aptocontractId]').val();
        var purchaseId = new Array();
        var i = 0;
        $("input[name='checkedtd']:checkbox").each(function () {
            if ($(this).prop("checked") == true) {
                purchaseId[i] = $(this).val();
                i += 1;
            }
        });

        if (contract_id > 0) {
            $.ajax({
                type: "GET",
                url: '<?php echo U("Purchasing/append_to_contract");?>',
                data: {'aptocontractId': contract_id, 'selecttr': purchaseId},
                dataType: "JSON",
                success: function (data) {
                    if (data.status == 0) {
                        layer.close();
                        layer.alert(data.msg, {icon: 2, closeBtn: false},
                                function () {
                                    window.location.reload();
                                });
                    }
                    else if (data.status == 1) {
                        layer.close();
                        layer.alert(data.msg, {icon: 1, closeBtn: false},
                                function () {
                                    window.location.reload();
                                });
                    }
                    else {
                        layer.close();
                        var msg = data.msg ? data.msg : '操作异常';
                        layer.alert(msg, {icon: 2, closeBtn: false},
                                function () {
                                    window.location.reload();
                                });
                    }
                }
            })
        }
        else {
            layer.alert('请先选择已有合同', {icon: 2});
        }
    }

    //获取商品低价记录
    function get_lower_price() {
        var purchase_list_id = 0;
        $("input[name= 'checkedtd']:checkbox").each(function () {
            if ($(this).prop("checked") == true && $(this).closest('tr').hasClass('selected')) {
                purchase_list_id = $(this).val();
                return false;
            }
        });

        if (purchase_list_id) {
            var iframe_lower_price = layer.open({
                type: 2,
                title: '历史采购价格',
                content: '<?php echo U("Supplier/get_lower_price_supplier");?>' + '&purchase_list_id=' + purchase_list_id,
                area: ['40%', '60%'],
                btn: ['设为供应商', '取消'],
                yes: function (index) {
                    var supplier_id = $(".supplier_id:checked", window.frames["layui-layer-iframe" + iframe_lower_price].document).val();
                    var supplier_name = $("#" + supplier_id + "_name", window.frames["layui-layer-iframe" + iframe_lower_price].document).attr('data');
                    //设置供应商
                    if (supplier_id > 0 && purchase_list_id > 0 && supplier_name != '') {
                        set_supplier(purchase_list_id, supplier_name, supplier_id);
                    }
                    else {
                        layer.alert('设置失败');
                    }
                    //关闭当前窗口
                    layer.close(index);
                },
                cancel: function (index) {
                    layer.close(index);
                }
            });
        }
        else {
            layer.alert('请选择一条采购明细记录', {icon: 2});
            return false;
        }
    }

    /**
     *
     * @param int purchase_id 采购明细编号
     */
    function set_supplier(purchase_id, s_name, s_id) {
        $("input[name=" + purchase_id + "_S_NAME]").val(s_name);
        $("input[name=" + purchase_id + "_S_ID_GET]").val(s_id);
    }

    //显示供应商添加弹框
    function open_add_supplier_window(fid) {
        var iframe_add_supplier = layer.open({
            type: 2,
            title: '添加供应商',
            content: '<?php echo U("Supplier/supplier_manage");?>' + '&showForm=3&layer_num=1',
            area: ['65%', '40%'],
            btn: ['保存', '取消'],
            yes: function (index) {
                var name = $(".NAME", window.frames["layui-layer-iframe" + iframe_add_supplier].document).val();
                var address = $(".ADDRESS", window.frames["layui-layer-iframe" + iframe_add_supplier].document).val();
                var truename = $(".CONTACT", window.frames["layui-layer-iframe" + iframe_add_supplier].document).val();
                var telno = $(".CONTACT_TELNO", window.frames["layui-layer-iframe" + iframe_add_supplier].document).val();
                var city_id = $(".CITY_ID", window.frames["layui-layer-iframe" + iframe_add_supplier].document).val();
                var status = $(".STATUS", window.frames["layui-layer-iframe" + iframe_add_supplier].document).val();

                if (name.trim() == '') {
                    layer.alert('供应商名称必须填写', {icon: 2});
                    return false;
                }

                if (address.trim() == '') {
                    layer.alert('地址必须填写', {icon: 2});
                    return false;
                }

                if (truename.trim() == '') {
                    layer.alert('联系人必须填写', {icon: 2});
                    return false;
                }

                if (telno.trim() == '') {
                    layer.alert('联系电话必须填写', {icon: 2});
                    return false;
                }

                if (city_id.trim() == '') {
                    layer.alert('城市必须填写', {icon: 2});
                    return false;
                }

                if (status.trim() == '') {
                    layer.alert('状态必须选择', {icon: 2});
                    return false;
                }

                $.ajax({
                    type: "POST",
                    url: '<?php echo U("Supplier/ajax_add_supplier_info");?>',
                    data: {
                        'name': name, 'address': address, 'truename': truename,
                        'telno': telno, 'city_id': city_id, 'status': status
                    },
                    dataType: "JSON",
                    success: function (data) {
                        if (data.status == 'noauth') {
                            layer.alert(data.msg, {icon: 2, closeBtn: false}, function () {
                                layer.closeAll();
                            });
                            return false;
                        }

                        if (data.state == 0) {
                            layer.alert(data.msg, {icon: 2, closeBtn: false}, function () {
                                layer.closeAll();
                            });
                        }
                        else if (data.state == 1) {
                            if (data.supplier_id > 0 && fid > 0) {
                                //设置供应商
                                set_supplier(fid, name, data.supplier_id);
                            }

                            layer.alert(data.msg, {icon: 1, closeBtn: false}, function () {
                                layer.closeAll();
                            });
                        }
                        else {
                            var msg = data.msg ? data.msg : '操作异常';
                            layer.alert(msg, {icon: 2, closeBtn: false}, function () {
                                layer.closeAll();
                            });
                        }
                    }
                })
            },
            cancel: function (index) {
                layer.close(index);
            }
        });
    }

    function addReim() {
        var purchaseList = [];
        $("input.checkedtd").each(function(index, elem) {
            if ($(elem).prop('checked') && $(elem).val()) {
                purchaseList.push($(elem).val());
            }
        });

        if (purchaseList.length == 0) {
            layer.alert('请至少选择一条采购明细', {icon: 2});
            return;
        }

        $.ajax({
            url:"<?php echo U('Reimbursement/apply_purchase_reim');?>",
            type: 'POST',
            data: {
                purchase_list: purchaseList
            },
            success: onAddReimSuccess
        });

        // 添加采购申请的回调函数
        function onAddReimSuccess(data) {
            if (data.status) {
                layer.open({
                    content: data.msg || '生成报销申请成功',
                    end: function() {
                        location.reload();
                    },
                    icon: 1
                });
            } else {
                layer.alert(data.msg || '生成报销申请失败', {icon: 2});
            }
        }
    }
</script>
<textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // 附带上上次搜索的结果
    });
</script>
</body>
</html>