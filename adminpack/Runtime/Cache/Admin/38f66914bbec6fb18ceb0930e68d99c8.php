<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>�ɹ���ϸ</title>
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
                <span class="fclos" style="display: inline;height: 50px;">�ɹ��ˣ�</span>
            </td>
            <td align="left">
                    <span class="fclos" style="display: inline;height: 50px;">
                        <select name="buyer_user" id='buyer_user'>
                            <option value="0">��ѡ��</option>
                            <?php if(is_array($purchase_user)): $i = 0; $__LIST__ = $purchase_user;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user): $mod = ($i % 2 );++$i;?><option value="<?php echo ($key); ?>"><?php echo ($user); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </span>
            </td>
        </tr>
    </table>
</div>

<!--�ɹ�������ϸ-->
<script id="purchaseTaskDetail" type="text/html">
    <div class="contractinfo-table">
        <table>
            <thead>
            <tr>
                <td>���</td>
                <td>�������</td>
                <td>��������</td>
                <td>��Ӧ��</td>
                <td>��ʼʱ��</td>
                <td>����ʱ��</td>
                <td>������</td>
                <td>�����ܷ���</td>
                <td>�����ܷ���</td>
                <td>���ϼ�</td>
                <td>�������</td>
                <td>��ע</td>
                <td>״̬</td>
                <td>�ѷ��������</td>
                <td>����</td>
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
                    <a target="" class="contrtable-link fedit J-export-file" data-file="1" href="index.php?s=/Purchasing/export_bee_file/file/1/id/{{$value.ID}}"  class="btn btn-info btn-xs">���ܱ�</a>
                    <a target="" class="contrtable-link fedit J-export-file" data-file="2" href="index.php?s=/Purchasing/export_bee_file/file/2/id/{{$value.ID}}" class="btn btn-info btn-xs">��ϸ��</a>
                    <a target="" class="contrtable-link fedit J-export-file" data-file="3" href="index.php?s=/Purchasing/export_bee_file/file/3/id/{{$value.ID}}" class="btn btn-info btn-xs">��������ϸ</a>
                </td>
            </tr>
            {{/each}}

            </tbody>
        </table>
    </div>

</script>
<script type="text/javascript">
    $(function () {
        // ����ѡ��
        var feeOptions = '<?php echo ($feeOptions); ?>';
        if (feeOptions) {
            $('select[name="FEE_ID"]')
                    .html(feeOptions)
                    .addClass('js-example-basic-single')
                    .val($('input[name="FEE_ID_OLD"]').val())
                    .unbind('focus')
                    .select2({
                        allowClear: true,
                        noResults: 'û���ҵ������Ϣ'
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
                    noResults: 'û���ҵ������Ϣ'
                });
    }

    //������չ
    layer.config({
        extend: 'extend/layer.ext.js'
    });

    /*����ɹ���*/
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
            layer.alert('������ѡ��һ���ɹ���ϸ', {icon: 2});
            return false;
        }

        //iframe��
        layer.open({
            type: 1,
            title: '����ɹ���',
            shadeClose: true,
            shade: 0.8,
            btn: ['ȷ ��', 'ȡ ��'],
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
                                var msg = data.msg ? data.msg : '�����쳣';
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
                    layer.tips('��ѡ��ɹ���', '#buyer_user');
                    return false;
                }
            }
        });
    });

    /*�����˿�*/
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
            layer.alert('��ѡ��һ���ɹ���ϸ', {icon: 2});
            return false;
        }

        layer.prompt({
            title: '��д�˿�����',
            formType: 0,//prompt���֧��0-2
            maxlength: 13, //�������ı�����󳤶ȣ�Ĭ��500
        }, function (back_num, index, elem) {

            var purchase_status = $("select[name='" + purchase_id + "_STATUS']").val();
            if (purchase_status != 2) {
                layer.alert('�˿�����ʧ�ܣ��ѱ����Ĳɹ����������˿�', {icon: 2});
                return false;
            }

            var return_status = $("select[name='" + purchase_id + "_BACK_STOCK_STATUS']").val();
            if (return_status == 1) {
                layer.alert('�˿�״̬�Ĳɹ���ϸ���޷��ٴ������˿�', {icon: 2});
                return false;
            }

            //���˿�����
            var stock_num = parseFloat($("input[name='" + purchase_id + "_STOCK_NUM']").val());
            //���ÿ������
            var use_num = parseFloat($("input[name='" + purchase_id + "_USE_NUM']").val());
            //��������
            var buy_num = parseFloat($("input[name='" + purchase_id + "_NUM']").val());
            //������������
            var apply_back_num_enable = use_num + buy_num - stock_num;

            if (back_num > apply_back_num_enable) {
                layer.alert('�����˿��������ܳ���' + apply_back_num_enable, {icon: 2});
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
                        var msg = data.msg ? data.msg : '�����쳣';
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

    // �����ɹ�
    $('#abandon_purchase').click(function() {
        layer.confirm('ȷ�������ɹ���', {
            btn: ['ȷ��','ȡ��'] //��ť
        }, function(){
            doAbandonPurchase();
        }, function(){
//            layer.msg('��ȷ����Ҫ', {icon: 1});
            // todo
        });
        // ִ�з����ɹ�
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
                    layer.alert("��,���Ϊ"+ purchaseDetailList[i]+"����ϸ�ķ�������Ϊ��ְ��Ա,���ܷ����ɹ�", {icon: 7});
                    return;
                }
            }

            if (purchaseDetailList.length == 0) {
                layer.alert('������ѡ��һ���ɹ���ϸ', {icon: 7});
                return;
            }

            // ��������ɹ�������óɹ��Ļص�����
            function onAbandonPurchaseSuccess(resp, status, xhr) {
                if (resp) {
                    resp = JSON.parse(resp);
                }

                if (resp.status == 'noauth') {
                    var message = resp.msg || 'Ȩ�޲���';
                    layer.alert(message, {icon: 2});
                    return;
                }

                if (resp.status) {
                    var message = resp.message || '�����ɹ�����ɹ�';
                    layer.alert(message, {icon: 1});
                    location.reload();
                } else {
                    var message = resp.msg || resp.message || "�������������Ĳɹ���ϸ�ſɷ�����<br/>1. �ɹ�������ͨ��������<br/> 2.�ɹ���ϸΪδ�ɹ���<br/> 3. �ɹ�����δ�����ͬ��";
                    layer.alert(message, {icon: 2});
                }
            }

            // ��������ɹ��������ʧ�ܵĻص�����
            function onAbandonPurchaseError(xhr, status, error) {

            }

            // ��������ɹ�
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
            layer.alert('��ѡ��һ���ɹ���ϸ', {icon: 7});
            return;
        }

        for(var i = 0 ; i < purchaseDetailList.length ; i++){
            var  feeId = $("input[name='" + purchaseDetailList[i] + "_FEE_ID_OLD']").val();
            if(feeId != 58 ){
                layer.alert("��,���Ϊ"+ purchaseDetailList[i]+"����ϸ�ķ������Ͳ��Ǽ�ְ��Ա,���ܲ鿴�ɹ�������ϸ", {icon: 7});
                return;
            }
        }
        //��ȡ�ɹ�������ϸ
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
                var msg = resp.msg || 'û��Ȩ��';
                layer.open({
                    content: msg,
                    icon: 2
                });
                return;
            }

            if (!resp.status) {
                var msg = resp.msg || '��ȡ����ʧ��';
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
                title: '�ɹ�������ϸ',
                skin: 'layui-layer-rim',
                btn: ['ȷ��'],
                area: ['1000px', 'auto'],
                content: template('purchaseTaskDetail', data),
            })
        }
        })



    var allowProductNameAutoComplete = '<?php echo ($product_name_autocomplete); ?>';
    if (parseInt(allowProductNameAutoComplete) > 0) {
        // ͨ��Ʒ����������Ʒ
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
                            //�жϷ��������Ƿ�Ϊ�գ���Ϊ�շ������ݡ�
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
        $('#last_filter_result').text($('#last_filter_text').text());  // �������ϴ������Ľ��
    });
</script>
</body>
</html>