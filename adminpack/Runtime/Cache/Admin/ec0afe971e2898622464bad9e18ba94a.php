<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>�ɹ�����</title>
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

</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <?php echo ($tabs); ?>
        </div>
        <div style="position: absolute;top:20px;right: 80px;"><label class="label label-danger">����һ�-�����Ĳ˵�</label></div>
        <?php echo ($form); ?>
        <input type="hidden" name="url_current" id="url_current" value="<?php echo U('Purchase/opinionFlow',$paramUrl);?>">
    </div>
</div>
<script type="text/javascript">
    //�ύ�ɹ�����
    $("#sub_purchase").click(function () {
        var p_id = $('.itemlist').filter('.selected').attr('fid');
        var status = $("select[name=" + p_id + "_STATUS]").val();

        if (status == 0) {
            layer.confirm(
                    'ȷ��Ҫ�ύ�ɹ�������(�ɹ��������вɹ���ϸ���ᱻ�ύ)��',
                    {title: '�ύ�ɹ�����'},
                    function (index) {
                        var _this = this;
                        //�Ƿ񳬳����ʱ���
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
                                alert("�������������~~");
                            }
                        });

                        if(_this.is_over){
                            if(!confirm("����Ŀ�ɱ��ѳ������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ�\n��ȷ��Ҫ������"))
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
            layer.alert('δ�ύ���Ĳɹ���������ύ', {icon: 2});
        }
    });

    // �鿴�ɹ�����ͼ
    $('#show_flow_step').click(function () {
        // �����첽���õĴ�����
        // ��ȡ������ID�첽���óɹ��ص�����
        function getFlowIdSuccess(resp, status, xhr) {
            if (resp) {
                resp = JSON.parse(resp);
            }

            if (resp.status == 'noauth') {
                var message = resp.msg || 'Ȩ�޲���';
                layer.alert(message, {icon: 2});
                return;
            }

            if (resp.status) {
                var viewFlowUrl = "<?php echo U('Flow/viewFlow');?>";
                viewFlowUrl += '/FLOWID/' + resp.data;
                layer.open({
                    type: 2,
                    title: '����ͼ',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['90%', '90%'],
                    content: viewFlowUrl
                });

            } else {
                var message = resp.message || '��ȡ������IDʧ��';
                layer.alert(message, {icon: 7});
            }
        }

        // ��ȡ������ID�첽����ʧ�ܻص�����
        function getFlowIdFail(xhr, status, error) {
            layer.alert('�����������쳣�����Ժ�����!', {icon: 2});
        }

        // ��ʾ����ͼ
        var selectedItem = $('.contractinfo-table .itemlist.selected');
        if (!selectedItem) {
            layer.alert('��ѡ��һ���ɹ�����!', {icon: 7});
            return;
        }
        var fid = selectedItem.attr('fid');
        if (fid) {
            if ($("select[name='" + fid + "_STATUS']").val() == 0) {
                layer.alert('�òɹ�������δ��������!', {icon: 7});
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

    // �Ƿ���ʾ��ҳ�ڵ�������ť
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
            alert('�����ʹ�ʱ�䲻��С�ڵ�ǰʱ��');
            $('input[name="END_TIME"]').val('');
        }
    });
</script>
<textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // �������ϴ������Ľ��
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