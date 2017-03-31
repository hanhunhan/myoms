<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>采购明细</title>
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
        .ui-autocomplete-input {
            background-image: none;
        }
    </style>
</head>
<body>
<div class="containter"><?php echo ($form); ?></div>
<div id="user_change" class='containter' style='display:none;height: 50px;'>
    <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center" valign="middle">
        <tr>
            <td align="center">
                <span class="fclos" style="display: inline;height: 50px;">采购人：</span>
            </td>
            <td align="left">
                    <span class="fclos" style="display: inline;height: 50px;">
                        <select name="buyer_user" id='buyer_user'>
                            <option value="0">请选择</option>
                            <?php if(is_array($purchase_user)): $i = 0; $__LIST__ = $purchase_user;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($user); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </span>
            </td>
        </tr>
    </table>
</div>

<!--采购任务明细-->
<script id="purchaseTaskDetail" type="text/html">
    <div class="contractinfo-table">
        <table>
            <thead>
            <tr>
                <td>编号</td>
                <td>任务序号</td>
                <td>任务名称</td>
                <td>供应商</td>
                <td>开始时间</td>
                <td>结束时间</td>
                <td>总人数</td>
                <td>工资总费用</td>
                <td>奖金总费用</td>
                <td>金额合计</td>
                <td>报销金额</td>
                <td>备注</td>
                <td>状态</td>
                <td>已反馈至侦客</td>
                <td>操作</td>
            </tr>
            </thead>
            <tbody>
            {{each list}}
            <tr>
                <td>{{$value.ID}}</td>
                <td>{{$value.TASK_ID}}</td>
                <td>{{$value.TASK_NAME}}</td>
                <td>{{$value.SUPPLIER}}</td>
                <td>{{$value.EXEC_START}}</td>
                <td>{{$value.EXEC_END}}</td>
                <td>{{$value.TOTAL_NUM}}</td>
                <td>{{$value.TOTAL_WAGES}}</td>
                <td>{{$value.TOTAL_BONUS}}</td>
                <td>{{$value.TOTAL_MONEY}}</td>
                <td>{{$value.REIM_MONEY}}</td>
                <td>{{$value.MARK}}</td>
                <td>{{$value.STATUS}}</td>
                <td>{{$value.IS_BACK_TO_ZK}}</td>
                <td>
                    <a target="" class="contrtable-link fedit J-export-file" data-file="1" href="index.php?s=/Purchasing/export_bee_file/file/1/id/{{$value.ID}}"  class="btn btn-info btn-xs">汇总表</a>
                    <a target="" class="contrtable-link fedit J-export-file" data-file="2" href="index.php?s=/Purchasing/export_bee_file/file/2/id/{{$value.ID}}" class="btn btn-info btn-xs">明细表</a>
                    <a target="" class="contrtable-link fedit J-export-file" data-file="3" href="index.php?s=/Purchasing/export_bee_file/file/3/id/{{$value.ID}}" class="btn btn-info btn-xs">带看奖明细</a>
                </td>
            </tr>
            {{/each}}

            </tbody>
        </table>
    </div>

</script>
<script type="text/javascript">
    $(function () {
        // 费用选择
        var feeOptions = '<?php echo ($feeOptions); ?>';
        if (feeOptions) {
            $('select[name="FEE_ID"]')
                    .html(feeOptions)
                    .addClass('js-example-basic-single')
                    .val($('input[name="FEE_ID_OLD"]').val())
                    .unbind('focus')
                    .select2({
                        allowClear: true,
                        noResults: '没有找到相关信息'
                    });
        }
    });



    var purOptions = '<?php echo ($purOptions); ?>';
    if (purOptions) {
        $('select[name="P_ID"]')
                .html(purOptions)
                .addClass('js-example-basic-single')
                .val($('input[name="P_ID_OLD"]').val())
                .unbind('focus')
                .select2({
                    allowClear: true,
                    noResults: '没有找到相关信息'
                });
    }

    //加载扩展
    layer.config({
        extend: 'extend/layer.ext.js'
    });

    /*变更采购人*/
    $("#change_buyer").click(function () {
        var purchase_id = new Array();
        var i = 0;
        $("input[name= 'checkedtd']:checkbox").each(function () {
            if ($(this).prop("checked") == true) {
                purchase_id[i] = $(this).val();
                i += 1;
            }
        });

        if (i == 0) {
            layer.alert('请至少选择一条采购明细', {icon: 2});
            return false;
        }

        //iframe层
        layer.open({
            type: 1,
            title: '变更采购人',
            shadeClose: true,
            shade: 0.8,
            btn: ['确 定', '取 消'],
            area: ['280px', '150px'],
            content: $('#user_change'),
            yes: function (index, layero) {
                var buyer_id = $("#buyer_user").val();
                if (buyer_id > 0) {
                    $.ajax({
                        type: "POST",
                        url: "index.php?s=/Purchase/change_buyer",
                        data: {'purchase_id': purchase_id, 'buyer_id': buyer_id},
                        dataType: "JSON",
                        success: function (data) {
                            if (data.state == 1) {
                                layer.alert(data.msg, {icon: 1, closeBtn: false},
                                        function () {
                                            layer.close(index);
                                            window.location.reload();
                                        });
                            }
                            else if (data.state == 0) {
                                layer.alert(data.msg, {icon: 2, closeBtn: false},
                                        function () {
                                            layer.close(index);
                                            window.location.reload();
                                        });
                            }
                            else {
                                var msg = data.msg ? data.msg : '操作异常';
                                layer.alert(msg, {icon: 2, closeBtn: false},
                                        function () {
                                            layer.close(index);
                                            window.location.reload();
                                        });
                            }
                        }
                    })
                }
                else {
                    layer.tips('请选择采购人', '#buyer_user');
                    return false;
                }
            }
        });
    });

    /*申请退库*/
    $("#return_stock").click(function () {
        var purchase_id = 0;
        var i = 0;
        $("input[name= 'checkedtd']:checkbox").each(function () {
            if ($(this).prop("checked") == true) {
                purchase_id = $(this).val();
                i += 1;
            }
        });

        if (i != 1) {
            layer.alert('请选择一条采购明细', {icon: 2});
            return false;
        }

        layer.prompt({
            title: '填写退库数量',
            formType: 0,//prompt风格，支持0-2
            maxlength: 13, //可输入文本的最大长度，默认500
        }, function (back_num, index, elem) {

            var purchase_status = $("select[name='" + purchase_id + "_STATUS']").val();
            if (purchase_status != 2) {
                layer.alert('退库申请失败，已报销的采购才能申请退库', {icon: 2});
                return false;
            }

            var return_status = $("select[name='" + purchase_id + "_BACK_STOCK_STATUS']").val();
            if (return_status == 1) {
                layer.alert('退库状态的采购明细，无法再次申请退库', {icon: 2});
                return false;
            }

            //已退库数量
            var stock_num = parseFloat($("input[name='" + purchase_id + "_STOCK_NUM']").val());
            //领用库存数量
            var use_num = parseFloat($("input[name='" + purchase_id + "_USE_NUM']").val());
            //购买数量
            var buy_num = parseFloat($("input[name='" + purchase_id + "_NUM']").val());
            //允许申请数量
            var apply_back_num_enable = use_num + buy_num - stock_num;

            if (back_num > apply_back_num_enable) {
                layer.alert('申请退库数量不能超过' + apply_back_num_enable, {icon: 2});
                return false;
            }

            $.ajax({
                type: "GET",
                url: "index.php?s=/Warehouse/return_to_warehouse",
                data: {'purchase_id': purchase_id, 'apply_back_num': back_num},
                dataType: "JSON",
                success: function (data) {
                    if (data.state == 1) {
                        layer.alert(data.msg, {icon: 1, closeBtn: false},
                                function () {
                                    layer.close(index);
                                    window.location.reload();
                                });
                    }
                    else if (data.state == 0) {
                        layer.alert(data.msg, {icon: 2, closeBtn: false},
                                function () {
                                    layer.close(index);
                                    window.location.reload();
                                });
                    }
                    else {
                        var msg = data.msg ? data.msg : '操作异常';
                        layer.alert(msg, {icon: 2, closeBtn: false},
                                function () {
                                    layer.close(index);
                                    window.location.reload();
                                });
                    }
                }
            })
        });
    });

    // 废弃采购
    $('#abandon_purchase').click(function() {
        layer.confirm('确定废弃采购？', {
            btn: ['确定','取消'] //按钮
        }, function(){
            doAbandonPurchase();
        }, function(){
//            layer.msg('的确很重要', {icon: 1});
            // todo
        });
        // 执行废弃采购
        function doAbandonPurchase() {
//            return false;
            var purchaseDetailList = [];
            $("input[name= 'checkedtd']:checkbox").each(function () {
                if ($(this).prop("checked") == true) {
                    purchaseDetailList.push($(this).val());
                }
            });

            for(var i =0 ; i < purchaseDetailList.length ; i++){
                var  feeId = $("input[name='" + purchaseDetailList[i] + "_FEE_ID_OLD']").val();
                if(feeId == 58 ){
                    layer.alert("亲,编号为"+ purchaseDetailList[i]+"的明细的费用类型为兼职人员,不能废弃采购", {icon: 7});
                    return;
                }
            }

            if (purchaseDetailList.length == 0) {
                layer.alert('请至少选择一条采购明细', {icon: 7});
                return;
            }

            // 请求废弃采购申请调用成功的回调函数
            function onAbandonPurchaseSuccess(resp, status, xhr) {
                if (resp) {
                    resp = JSON.parse(resp);
                }

                if (resp.status == 'noauth') {
                    var message = resp.msg || '权限不足';
                    layer.alert(message, {icon: 2});
                    return;
                }

                if (resp.status) {
                    var message = resp.message || '废弃采购申请成功';
                    layer.alert(message, {icon: 1});
                    location.reload();
                } else {
                    var message = resp.msg || resp.message || "满足以下条件的采购明细才可废弃：<br/>1. 采购申请已通过审批；<br/> 2.采购明细为未采购；<br/> 3. 采购申请未加入合同。";
                    layer.alert(message, {icon: 2});
                }
            }

            // 请求废弃采购申请调用失败的回调函数
            function onAbandonPurchaseError(xhr, status, error) {

            }

            // 请求废弃采购
            $.ajax({
                url: "<?php echo U('Purchase/abandon');?>",
                data: {
                    purchase_detail_list: purchaseDetailList
                },
                type: 'POST',
                success: onAbandonPurchaseSuccess,
                error: onAbandonPurchaseError
            });
        }
    });


    $('#view_task_details').click(function(){
        var purchaseDetailList = [];
        $("input[name= 'checkedtd']:checkbox").each(function () {
            if ($(this).prop("checked") == true) {
                purchaseDetailList.push($(this).val());
            }
        });
        if ( purchaseDetailList.length == 0 ||  purchaseDetailList.length > 1) {
            layer.alert('请选择一条采购明细', {icon: 7});
            return;
        }

        for(var i = 0 ; i < purchaseDetailList.length ; i++){
            var  feeId = $("input[name='" + purchaseDetailList[i] + "_FEE_ID_OLD']").val();
            if(feeId != 58 ){
                layer.alert("亲,编号为"+ purchaseDetailList[i]+"的明细的费用类型不是兼职人员,不能查看采购任务明细", {icon: 7});
                return;
            }
        }
        //获取采购任务明细
        $.ajax({
            url: "<?php echo U('Purchase/ajaxGetPurchaseTaskData');?>",
            data: {
                purchaseDetailList: purchaseDetailList,
            },
            type: 'POST',
            success: onGetDataSuccess
        });
        function onGetDataSuccess(resp , xhr) {
            //console.log(resp.data.list)
            if ($.type(resp) === 'string') {
                resp = JSON.parse(resp);
            }

            if (resp.status == 'noauth') {
                var msg = resp.msg || '没有权限';
                layer.open({
                    content: msg,
                    icon: 2
                });
                return;
            }

            if (!resp.status) {
                var msg = resp.msg || '获取数据失败';
                layer.open({
                    content: msg,
                    icon: 2
                });
                return;
            }

            var data = {
                list: resp.data.list ,
            }

            parent.layer.open({
                type: 1,
                title: '采购任务明细',
                skin: 'layui-layer-rim',
                btn: ['确定'],
                area: ['1000px', 'auto'],
                content: template('purchaseTaskDetail', data),
            })
        }
        })



    var allowProductNameAutoComplete = '<?php echo ($product_name_autocomplete); ?>';
    if (parseInt(allowProductNameAutoComplete) > 0) {
        // 通过品名联想库存商品
        $("input[name='PRODUCT_NAME']").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "<?php echo U('Warehouse/ajaxMatchedStorage');?>",
                    type: "GET",
                    dataType: "JSON",
                    data: {keyword: request.term},
                    success: function (data) {
                        if (data.status == 'noauth') {
                            location.reload();
                        } else {
                            //判断返回数据是否为空，不为空返回数据。
                            if (data[0]['value'] > 0) {
                                response(data);
                            } else {
                                response(data);
                            }
                        }
                    }
                });
            },
            select: function (event, ui) {
                var item = ui.item;
                $("input[name='BRAND']").val(item['brand']);
                $("input[name='MODEL']").val(item['model']);
                $("input[name='PRICE_LIMIT']").val(item['price']);
            }
        })
                .data("ui-autocomplete")._renderItem = function (ul, item) {
            return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append("<a>" + item.label + "</a>")
                    .appendTo(ul);
        };
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