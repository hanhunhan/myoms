<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>��Ŀ�б�</title>
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
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            /* ��ֹˮƽ������ */
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
                <li class="selected"><a href="<?php echo U('Case/projectlist');?>">��Ŀ�б�</a></li>
            </ul>
        </div>
        <?php echo ($form); ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        // ���Ƹ����˵�����ʾ���������Ŀ�ɲ�����ť�ϣ������������˵�
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
            // ���ҷ��ճ���Ŀ״̬
            var tagSC = $(this).attr('fid') + '_' + 'SCSTATUS';
            var thisid = $(this).attr("fid");

            // ��Ŀ״̬
            var COLUMNS = {
                'P': $('[name="' + tagP + '"]'),  // ������
                'B': $('[name="' + tagB + '"]'),  // ������
                'M': $('[name="' + tagM + '"]'),  // ������
                'A': $('[name="' + tagA + '"]'),  // Ӳ����
                'AC': $('[name="' + tagAC + '"]'),// ���
                'SC': $('[name="' + tagSC + '"]') // ���ҷ��ճ���Ŀ��
            };

            // �ǵ�����Ŀ����ʾ����Ԥ�����ȷ�ϰ���Ԥ����
            if ((!COLUMNS.B.val() || COLUMNS.B.val() == '') && (!COLUMNS.M.val() || COLUMNS.M.val() == '')) {
                // ������Ԥ����
                var tagAdvance = $(this).attr('fid') + '_BUSINESS_INCOME';
                $('[name="' + tagAdvance + '"]').closest('td').find('span').first().text('');

                // ȷ�ϰ���Ԥ����
                var tagConfirmAdvances = $(this).attr('fid') + '_CONFIRM_INCOME';
                $('[name="' + tagConfirmAdvances + '"]').closest('td').find('span').first().text('');
            }

            // �������������з�װ�������һ��
            // ����״̬
            (function () {
                if (COLUMNS.P.val() >= 2) {
                    if (COLUMNS.AC.val() >= 1) {  // ����ǻ�ƹ���Ŀ
                        COLUMNS.P.closest('td').click(function () {
                            var url = appUrl + '/Activ/activPro&tabNum=8&showOpinion=1&prjid=' + thisid;
                            window.location.href = url;
                        }).css({"cursor": 'pointer'});
                    } else if (COLUMNS.SC.val() >= 1) {  // ����������ҷ��ճ���Ŀ
                        if (COLUMNS.M.val() >= 1) {  // ���ҷ��ճ�����������
                            COLUMNS.P.closest('td').click(function () {
                                var url = appUrl + '/House/projectDetail&prjid=' + thisid + '&tabNum=20';
                                location.href = url;
                            }).css({'cursor': 'pointer'});

                        } else {  // ֻ�з��ҷ��ճ�
                            COLUMNS.P.closest('td').click(function () {
                                var url = appUrl + '/House/projectDetail&prjid=' + thisid + '&tabNum=23';
                                location.href = url;
                            }).css({'cursor': 'pointer'});
                        }
                    } else {  // ������Ŀ
                        COLUMNS.P.closest('td').click(function () {
                            var url = appUrl + '/House/projectDetail&prjid=' + thisid + '&tabNum=20';
                            window.location.href = url;
                        }).css({"cursor": 'pointer'});
                    }
                }
            })();

            // ����״̬
            (function () {
                if (COLUMNS.B.val() >= 2) {
                    COLUMNS.B.closest('td').click(function () {
                        var url = appUrl + '/Business/index&prjid=' + thisid;
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();

            // ����״̬
            (function () {
                if (COLUMNS.M.val() >= 2) {
                    COLUMNS.M.closest('td').click(function () {
                        var url = appUrl + '/MemberDistribution/index/prjid/' + thisid + '/TAB_NUMBER/4/CASE_TYPE/fx';
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();

            // Ӳ��״̬
            (function () {
                if (COLUMNS.A.val() >= 2) {
                    COLUMNS.A.closest('td').click(function () {
                        var url = appUrl + '/Advert/index/is_from/1/CASE_TYPE/yg&prjid=' + thisid;
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();

            // ���
            (function () {
                if (COLUMNS.AC.val() >= 2) {
                    COLUMNS.AC.closest('td').click(function () {
                        var url = appUrl + '/Advert/index/is_from/2/CASE_TYPE/hd/&prjid=' + thisid;
                        window.location.href = url;
                    }).css({"cursor": 'pointer'});
                }
            })();

            // ���ҷ��ճ�״̬
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

    //ҵ�����
    function benefits() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_PSTATUS";   //����
        var tagB = thisid + "_BSTATUS";   //����
        var tagM = thisid + "_MSTATUS";   //����
        var tagAS = thisid + "_ASTATUS";    //Ӳ��
        var tagCP = thisid + "_CPSTATUS";  //��Ʒ
        var tagAC = thisid + "_ACSTATUS"; //�����
        var tagSC = thisid + "_SCSTATUS"; // ���ҷ��ճ�

        var SC_STATUS = $("[name='" + tagSC + "']").val();  // ���ҷ��ճ�״̬
        var M_STATUS = $("[name='" + tagM + "']").val();  // ����״̬
        if ($("[name='" + tagCP + "']").val()) {
            layer.alert('��Ʒ��Ŀ�����������', {icon: 0});
            return false;
        }
//        if (SC_STATUS && M_STATUS) {  // ���ҷ��ճ�����������
//            // todo
//        } else if ($("[name='" + tagM + "']").val() && !$("[name='" + tagB + "']").val()) {
//            layer.alert('������Ŀ�����������', {icon: 0});
//            return false;
//        }
        if (thisid) {
            if ($("[name='" + tagP + "']").val() == 3 || $("[name='" + tagAS + "']").val() == 2 || $("[name='" + tagAS + "']").val() == 4) {
                var url = appUrl + '/Benefits/benefits&prjid=' + thisid + "&TAB_NUMBER=14";
                window.location.href = url;
            } else  layer.alert('����Ŀ����δ�����ѱ���ֹ', {icon: 0});
        }
        else layer.alert('����ѡ����Ŀ', {icon: 0});
    }

    //Ԥ������������
    function otherBenefits() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS"; //����״̬
        var tagCP = thisid + "_CPSTATUS";//��Ʒ
        var tagAC = thisid + "_ACSTATUS";//�����
        var tagA = thisid + "_ASTATUS"; //Ӳ��
        var tagM = thisid + "_MSTATUS";   // ����
        var tagB = thisid + "_BSTATUS";   //����
        var tagSC = thisid + "_SCSTATUS"; // ���ҷ��ճ�

        var SC_STATUS = $("[name='" + tagSC + "']").val();  // ���ҷ��ճ�״̬
        var M_STATUS = $("[name='" + tagM + "']").val();  // ����״̬

//        if (SC_STATUS && M_STATUS) {
//            // todo
//        } else if ($("[name='" + tagM + "']").val() && !$("[name='" + tagB + "']").val()) {
//            layer.alert('������Ŀ��������Ԥ�������', {icon: 0});
//            return false;
//        }

        if ($("[name='" + tagCP + "']").val() || $("[name='" + tagA + "']").val() || $("[name='" + tagAC + "']").val()) {
            layer.alert('ֻ�а���<span style="color:red">����</span>��<span style="color:red">����</span>��<span style="color:red">���ҷ��ճ�</span>ҵ���������Ԥ������ã�', {icon: 0});
            return false;
        }
        if (thisid) {
            if ($("[name='" + tagP + "']").val() == 3) {
                var url = appUrl + '/Benefits/otherBenefits&prjid=' + thisid + "&TAB_NUMBER=15";
                window.location.href = url;
            } else {
                layer.alert('����Ŀ����δ�����ѱ���ֹ', {icon: 0});
            }
        } else {
            layer.alert('����ѡ����Ŀ', {icon: 0});
        }
    }

    // �ʽ�ط���
    function fundPoolCost() {
        var thisId = $(".contractinfo-table tbody .selected").attr('fid');
        if (parseInt(thisId) <= 0) {
            layer.alert('��ѡ��һ����Ŀ', {icon: 0});
            return;
        }

        var P = $('[name=' + thisId + "_" + "PSTATUS]").val(), // ����״̬
            CP = $('[name=' + thisId + "_CPSTATUS]").val(),  // ��Ʒ
            AC = $('[name=' + thisId + "_ACSTATUS]").val(), // �����
            A = $('[name=' + thisId + "_ASTATUS]").val(),  // Ӳ��
            M = $('[name=' + thisId + "_MSTATUS]").val(),  // ����
            B = $('[name=' + thisId + "_BSTATUS]").val(),  // ����
            SC = $('[name=' + thisId + "_SCSTATUS]").val();  // ���ҷ��ճ�

        // ֻ�С���ᡱ״̬�ſ��Խ��з�������
        if (parseInt(P) !== 3) {
            layer.alert('����Ŀ����δ�����ѱ���ֹ', {icon: 0});
            return;
        }

        // ֻ�е������ʽ�ط�����ظ��� 
        
        if (!B) {
            
            layer.alert('ֻ��<span style="color:red">����</span>ҵ����������ʽ�ط��ã�', {icon: 0});
            return;
        } else {
            if (B != 2 && B != 4 && B != 3) {
                layer.alert('ֻ�д���<span style="color:red">ִ����</span>����<span style="color:red">�Ѿ�����</span>����<span style="color:red">���ڽ���</span>��ҵ������ʽ�ط��ã�', {icon: 0});
                return;
            }
        }

        $.ajax({
            url: "<?php echo U('House/ajaxIsFundPoolProject');?>",
            data: {
                projId: thisId  // ��ĿID
            },
            success: onFundPoolCostStatus
        });

        function onFundPoolCostStatus(data) {
            if (data.status) {  // ���ʽ����Ŀ
                location.href = appUrl + '/Benefits/fundPoolCost&prjId=' + thisId + "&TAB_NUMBER=15";
            } else {
                layer.alert('���ʽ����Ŀ���������ʽ�ط���', {icon: 0});
            }
        }
    }

    //���ʱ�������
    function payout_change() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";    //����
        var tagAS = thisid + "_ASTATUS";       //Ӳ��
        var tagCP = thisid + "_CPSTATUS";     //��Ʒ
        var tagAC = thisid + "_ACSTATUS";     //�
        var tagSC = thisid + "_SCSTATUS";     // ���ҷ��ճ�
        if ($("[name='" + tagCP + "']").val() || $("[name='" + tagAS + "']").val() || $("[name='" + tagAC + "']").val()) {
            layer.alert('��<span style="color: red">����</span>��<span style="color: red">����</span>��<span style="color: red">���ҷ��ճ�</span>��Ŀ����������ʱ���', {icon: 0});
            return false;
        }
        if (thisid) {
            if ($("[name='" + tagP + "']").val() == 3) {
                var url = "<?php echo U('Payout_change/payout_change');?>" + "/prjid/" + thisid + "/layer/1";
                var iframe_payout_change = layer.open({
                    type: 2,
                    title: '��ѡ��ҵ������',
                    btn: ['ȷ��', 'ȡ��'],
                    shadeClose: true,
                    shade: 0.8,
                    area: ['300px', '40%'],
                    content: [url, "no"],
                    yes: function (index, layero) { //����ʹ��btn1
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
                layer.alert('����Ŀ����δ�����ѱ���ֹ', {icon: 0});
            }
        } else {
            layer.alert('����ѡ����Ŀ', {icon: 0});
        }
    }

    //�ɱ�����
    function allocation() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');

        var tagP = thisid + "_" + "PSTATUS";    //����
        var tagA = thisid + "_ASTATUS";         //Ӳ��
        var tagCP = thisid + "_CPSTATUS";     //��Ʒ
        var tagAC = thisid + "_ACSTATUS";     //�

        //����ѡ��
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
                    layer.alert('����Ŀ�����ϻ���������', {icon: 0});
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

        /*******�����Ŀ���������������������Ϸ���Ҫ��*****/
        if (select == 1) {
            var url = "<?php echo U('Payout_change/payout_change');?>" + "/prjid/" + thisid + "/layer/1/chengbenhuabo/1";
            var iframe_payout_change = layer.open({
                type: 2,
                title: '��ѡ��ҵ������ �� �Ƿ�۷�',
                btn: ['ȷ��', 'ȡ��'],
                shadeClose: true,
                shade: 0.8,
                area: ['300px', '40%'],
                content: [url, "no"],
                yes: function (index, layero) { //����ʹ��btn1
                    var scale_type;
                    var koufei;

                    //ҵ������
                    var dome = window.frames["layui-layer-iframe" + iframe_payout_change].document.getElementsByName("scale_type");
                    for (var i = 0, len = dome.length; i < len; i++) {
                        if (dome[i].checked == true) {
                            scale_type = dome[i].value;
                        }
                    }

                    //�۷�����
                    koufei = window.frames["layui-layer-iframe" + iframe_payout_change].document.getElementsByName("koufei")[0].value;

                    if (scale_type && koufei != '') {
                        //ֱ����ת��ȥ
                        var url = appUrl + '/Cost/allocationApply/project_id/' + thisid + "/from/projectList/project_type_id/" + scale_type + "/koufei/" + koufei;
                        window.location.href = url;
                    }
                    else {
                        layer.alert("��ѡ��ҵ�����ͺ��Ƿ�۷ǣ�");
                    }
                }, cancel: function (index) { //����ʹ��btn2
                    layer.close(index);
                }
            });
        }
    }

    function createcase() {
        var url = appUrl + '/Case/createcase';
        window.location.href = url;
    }

    //��Ŀ���
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
                layer.alert('����ѡ����Ŀ', {icon: 0});
            }
        } else {
            if ($("[name='" + tagP + "']").val() == 0) {
                layer.alert('�������޷�������Ŀ���', {icon: 0});
            } else layer.alert('��ѡ��ִ���е���Ŀ', {icon: 0});
        }
    }

    //��Ŀ�»����
    function activitiesap() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');

        var tagP = thisid + "_" + "PSTATUS";
        var tagAC = thisid + "_" + "ACSTATUS";
        var tagA = thisid + "_" + "ASTATUS";

        if ($("[name='" + tagAC + "']").val()) {
            layer.alert('�������������������', {icon: 0});
        } else {
            if ($("[name='" + tagP + "']").val() == 3 || $("[name='" + tagA + "']").val() == 2 || $("[name='" + tagA + "']").val() == 4) {
                if (thisid) {
                    var url = appUrl + '/Activ/activProX/tabNum/8/prjid/' + thisid;
                    window.location.href = url;
                }
                else  layer.alert('����ѡ����Ŀ', {icon: 0});
            } else   layer.alert('����Ŀ����δ���', {icon: 0});
        }
    }

    //��׼����
    function feescale_change_list() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";
        var tagB = thisid + "_" + "BSTATUS";
        var tagM = thisid + "_" + "MSTATUS";
        var tagCP = thisid + "_CPSTATUS";
        var tagSC = thisid + "_SCSTATUS";
        if ($("[name='" + tagCP + "']").val()) {
            layer.alert('��Ʒ��Ŀ���������׼����', {icon: 0});
            return false;
        }
        if (thisid) {
            if (!$("[name = '" + tagB + "']").val() && !$("[name = '" + tagM + "']").val() && !$("[name = '" + tagSC + "']").val()) {
                layer.alert("����Ŀû��<span style='color:red'>����</span>��<span style='color:red'>����</span>ҵ�񣬲��������׼���� !", {icon: 0});
                return false;
            }
            if ($("[name='" + tagP + "']").val() == 3) {
                url = appUrl + '/Feescale_change/feescale_change_list/prjid/' + thisid;
                window.location.href = url;
            }
            else {
                layer.alert('����Ŀ����δ�����ѱ���ֹ', {icon: 0});
            }
        }
        else {
            layer.alert("����ѡ����Ŀ", {icon: 0});
        }
    }

    //��ĿȨ��
    function project_auth() {
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        var tagP = thisid + "_" + "PSTATUS";
        if (thisid) {
            //if($("[name='"+tagP+"']").val()==3){
            url = appUrl + '/House/projectAuth/prjid/' + thisid;
            window.location.href = url;
            //}else   layer.alert('����Ŀ����δ���');
        } else {
            layer.alert("����ѡ����Ŀ", {icon: 0});
        }
    }

    //��Ŀ����
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
                    layer.alert('Ӳ��������Ŀ���������', {icon: 0});
                } else if ($("[name='" + tagCP + "']").val()) {
                    layer.alert('��Ʒ������Ŀ���������', {icon: 0});
                } else if ($("[name='" + tagAC + "']").val()) {
                    layer.alert('�����������Ŀ���������', {icon: 0});
                } else
                    layer.alert('����Ŀ����״̬���������', {icon: 0});
            }
        } else {
            layer.alert("����ѡ��Ҫ�������Ŀ", {icon: 0});
        }
    }
    //��Ŀ��ֹ
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
                    title: 'ѡ��Ҫ��ֹ��ҵ������',
                    shadeClose: true,
                    shade: 0.8,
                    area: ['280px', '40%'],
                    content: '<?php echo U("Case/project_termination");?>' + '&layer=1&prjid=' + thisid //iframe��url
                });
            } else if ($("[name='" + tagP + "']").val() == 5) {
                layer.alert('����Ŀ����״̬��������ֹ', {icon: 0});
            } else {
                if ($("[name='" + tagA + "']").val()) {
                    layer.alert('Ӳ��������Ŀ��������ֹ', {icon: 0});
                } else if ($("[name='" + tagCP + "']").val()) {
                    layer.alert('��Ʒ������Ŀ��������ֹ', {icon: 0});
                } else if ($("[name='" + tagAC + "']").val()) {
                    layer.alert('�������Ŀ��������ֹ', {icon: 0});
                } else
                    layer.alert('����Ŀ����״̬��������ֹ', {icon: 0});
            }
        } else {
            layer.alert("����ѡ��Ҫ��ֹ����Ŀ", {icon: 0});
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

        } else   layer.alert('��ѡ����ֹ������');
    }

    //�ϴ�����
    function upload_files(){
        var thisid = $(".contractinfo-table tbody .selected").attr('fid');
        if (thisid) {
                var url = "<?php echo U('Project/upload_files');?>" + "/prjid/" + thisid ;
           layer.open({
                    type: 2,
                    title: '�ϴ�����',
                    shadeClose: true,
                    area : ['80%', '80%'],
                    content: url,
                    cancel: function (index) {
                        layer.close(index);
                    }
                });
        } else {
            layer.alert('����ѡ����Ŀ', {icon: 0});
        }
    }

    //ɾ����Ŀ
    function delproject(obj) {
        if (confirm('ȷ��ɾ��?')) {
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
	//�鿴����ͼ
	function viewFlow(obj){
		var thisid = $(obj).parent().parent().attr('fid');
		var url = appUrl + '/Flow/viewFlow/prjid/' + thisid;
		window.open(url);
	}
	//�����ύ
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
                layer.alert('����ѡ����Ŀ', {icon: 0});
            }
        } else {
            if ($("[name='" + tagP + "']").val() == 0  ) {
                layer.alert('�������޷�������Ŀ���', {icon: 0});
            } else layer.alert('��ѡ��δ�ύ��˵���Ŀ', {icon: 0});
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