<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>������ҳ</title>
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

    <script>
        var appUrl;
        //ͨ����ԱID����ȷ��
        $(function () {
            var url;
            $("#financialConfirm1").click(function () {
                var memberId = new Array();
                var i = 0;
                var confirmMethod = 1;
                $("input[name= 'checkedtd']:checkbox").each(function () {
                    if ($(this).prop("checked") == true) {
                        memberId[i] = $(this).val();
                        i += 1;
                    }
                });

                if (memberId.length <= 0) {
                    layer.alert("������ѡ��һ����¼", {icon: 0});
                    return false;
                }
                $.ajax({
                    type: "post",
                    url: "index.php?s=/Financial/financialConfirm",
                    data: {memberId: memberId, confirmMethod: confirmMethod},
                    dataType: "text",
                    success: function (data) {
                        if (data == 0) {
                            layer.alert("����ѡ��ļ�¼�а�����ȷ�ϵļ�¼,����û���κν��Ѽ�¼�����ܽ���ȷ�ϣ�������ѡ��", {icon: 0});
                        } else if (data == 1) {
                            layer.alert("��ѡ������һ����¼", {icon: 0});
                        } else if (data == 2) {
                            layer.alert("ϵͳ����", {icon: 1});
                        } else if (data == 3) {
                            layer.confirm('ȷ�ϳɹ�', {
                                btn: ['ȷ��'], //��ť
                                icon: 1
                            }, function () {
                                url = "<?php echo U('Financial/financialConfirm',$paramUrl);?>";
                                window.location.href = url;
                            }, function () {

                            });
                        } else if (data == 4) {
                            layer.alert("ȷ��ʧ��", {icon: 1});
                        } else if (data == 5) {
                            layer.alert("��ѡ�û���û��δȷ�Ͽ����û�н��Ѽ�¼", {icon: 0});
                        }
                    }
                })
            });

            //ȡ��ȷ��
            $("#cancleConfirm1").click(function () {
                var memberId = new Array();
                var i = 0;
                var cancleMethod = 1;
                $("input[name = checkedtd]:checkbox").each(function () {
                    if ($(this).prop("checked") == true) {
                        memberId[i] = $(this).val();
                        i += 1;
                    }
                });

                $.ajax({
                    type: "post",
                    url: "index.php?s=/Financial/cancleConfirm",
                    dataType: "text",
                    data: {memberId: memberId, cancleMethod: cancleMethod},
                    success: function (data) {
                        if (data == 0) {
                            layer.alert("����ѡ��ļ�¼�а���δȷ�ϵļ�¼������ȡ����������ѡ��", {icon: 0});
                        } else if (data == 1) {
                            layer.alert("��ѡ������һ����¼", {icon: 0});
                        } else if (data == 2) {
                            layer.alert("ϵͳ����", {icon: 2});
                        } else if (data == 3) {
                            layer.alert('ȡ���ɹ�', {
                                skin: 'layui-layer-lan', //��ʽ����
                                closeBtn: 0,
                                icon: 1
                            }, function () {
                                url = "<?php echo U('Financial/financialConfirm',$paramUrl);?>";
                                window.location.href = url;
                            });
                        } else if (data == 4) {
                            layer.alert("ȡ��ʧ��", {icon: 2});
                        } else if (data == 5) {
                            layer.alert(
                                    "��ѡ�û��д���û�б�ȷ�Ͽ�������û�û���κνɷѼ�,������ѡ��",
                                    {icon: 0},
                                    function (index) {
                                        url = "<?php echo U('Financial/financialConfirm',$paramUrl);?>";
                                        window.location.href = url;
                                        layer.close(index)
                                    }
                            );
                        }
                    }
                })
            });

            //������������
            $("#importBankData").click(function () {
                var url = "index.php?s=/Financial/importBankData";
                layer.open({
                    type: 2,
                    //btn:["ȷ��","ȡ��"],
                    title: '�����������ݶԱ�',
                    content: url,
                    area: ['50%', '50%'],
                    cancel: function (index) {
                        window.location.reload();
                    }
                });
            })
        })
    </script>
</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <ul>
                <li class="selected"><a href="<?php echo U('Financial/financialConfirm',$paramUr);?>">Ԥ��ȷ��</a></li>
                <li><a href="<?php echo U('Financial/invoice',$paramUrl);?>">��Ʊ</a></li>
                <li><a href="<?php echo U('Financial/reimConfirm',$paramUrl);?>">����ȷ��</a></li>
                <li><a href="<?php echo U('Financial/yw_invoice',$paramUrl);?>">ҵ��Ʊ</a></li>
                <li><a href="<?php echo U('Financial/business_change_invoice',$paramUrl);?>">ҵ��Ʊ</a></li>
                <li><a href="<?php echo U('Financial/business_refund_invoice',$paramUrl);?>">ҵ����Ʊ</a></li>
                <li><a href="<?php echo U('Financial/yw_refund',$paramUrl);?>">ҵ��ؿ�</a></li>
                <li><a href="<?php echo U('Financial/callback_commission',$paramUrl);?>">Ӷ������</a></li>
            </ul>
        </div>
        <?php echo ($form); ?>
    </div>
</div>
<textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // �������ϴ������Ľ��
    });
</script>
</body>
</html>