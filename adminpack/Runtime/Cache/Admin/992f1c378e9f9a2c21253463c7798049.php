<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>������Ŀ��ϸ</title>
    <meta charset="GBK">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>-->
    <!--<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/common.js"></script>-->
    <!--<script type="text/javascript" src="./Public/js/common.js"></script>-->
    <!--<script language="javascript" type="text/javascript" src="./Public/layer/layer.js"></script>-->
    <!--<script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>-->
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

</head>
<body>
<div><?php echo ($form); ?></div>
<script type="text/javascript">
    $(document).ready(function () {
        var uid = "<?php echo $uid ?>";
        var auth_transfer = "<?php echo $auth_transfer ?>";

        //���ǰ��״̬��ʾ����
        $(".itemlist").each(function () {
            //��ȡ״̬���Ƿ�ȷ�ϣ�
            var status = $(this).find("td").eq(11).find(".spanshow").text();
            var pro_uid = $(this).find("td").eq(5).find("input:hidden").val();

            //if(status == 'ȷ��'  ||  uid != pro_uid)
            if (status == '��ȷ��' || !auth_transfer) {
                $(this).find(".checkedtd").remove();
                $(this).find("td").eq(13).find("select").remove();
            }

        });

        //����༭
        $("input[name='checkedtd']").each(function () {
            $(this).change(function () {
                var fid = $(this).val();
                if ($(this).prop("checked") == true) {
                    $("select[name = '" + fid + "_KOUFEI']").parent().css("display", "block");
                    $("select[name = '" + fid + "_KOUFEI']").parent().siblings().css("display", "none");
                    $("select[name = '" + fid + "_KOUFEI']").parent().width('80px');
                }
                else {
                    //ȥ����info��Ϣ
                    $(".info").remove();
                    $("select[name = '" + fid + "_KOUFEI']").parent().css("display", "none");
                    $("select[name = '" + fid + "_KOUFEI']").parent().siblings().css("display", "block");
                }
            });
        });
    });

    //ͨ�����������˿�
    $("#confirm_allocation").click(function () {
        var allocation_id = new Array();
        var i = 0;
        $("input[name='checkedtd']:checkbox").each(function () {
            if ($(this).prop("checked") == true) {
                allocation_id[i] = $(this).val();
                i += 1;
            }
        });

        if (i == 0) {
            layer.msg('������ѡ��һ��������Ŀ��', {icon: 2});
            return false;
        }

        for (var i = 0; i < allocation_id.length; i++) {
            var id = '#' + allocation_id[i] + "_allocation_bc";
            if ($(id).val() == 0) {
                layer.msg('��ѡ������Ŀ�Ļ���ҵ�����ͣ�', {icon: 2});
                return false;
            }
            //��ѡ���Ƿ�۷�
            if ($("select[name = '" + allocation_id[i] + "_KOUFEI']").val() == '') {
                layer.msg('��ѡ���Ƿ�۷ǣ�', {icon: 2});
                return false;
            }
        }

        $.ajax({
            type: "POST",
            url: "index.php?s=/Cost/showProAllocation",
            data: {
                'allocation_id': allocation_id,
                'act': 'save_pro_allocation',
                'formdata': $('.registerform').serialize()
            },
            dataType: "JSON",
            success: function (data) {
                if (data.state == 0) {
                    layer.alert(data.msg);
                }
                else if (data.state == 1) {
                    layer.alert(data.msg, {icon: 1, closeBtn: false},
                            function () {
                                window.location.reload();
                            });
                }
                else {
                    var msg = data.msg ? data.msg : '�����쳣';
                    layer.alert(msg);
                }
            }
        })
    })
</script>
<textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // �������ϴ������Ľ��
    });
</script>
</body>
</html>