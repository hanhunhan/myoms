<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>������ϸ</title>
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
            <ul>
                <li class="selected"><a href="<?php echo U('Cost/allocationDetails',$paramUrl);?>">��Ŀ����</a></li>
                <li><a href="<?php echo U('Cost/opinionFlow',$paramUrl);?>">���������</a></li>
            </ul>
        </div>
        <?php echo ($form); ?>
    </div>
</div>
<script type="text/javascript">
    $(function () {
        //���뻮��
        $("#commit_allocation").click(function () {
            var commit_id = 0;
            commit_id = $(".itemlist.selected").attr("fid");

            if (commit_id == 0) {
                layer.alert('��ѡ��һ����¼,���в���!', {icon: 2});
                return false;
            }
            window.location.href = 'index.php?s=/Cost/opinionFlow/&RECORDID=' + commit_id;
        });

        //�����༭
        $("#edit_allocation").click(function () {
            var commit_id = 0;
            commit_id = $(".itemlist.selected").attr("fid");

            if (commit_id == 0) {
                layer.alert('��ѡ��һ����¼,���в���!', {icon: 2});
                return false;
            }

            var projectID = $("input[name='" + commit_id + "_ID']").val();

            window.location.href = 'index.php?s=/Cost/allocationApply/transfer_id/' + commit_id + '/step/1/project_id/' + projectID;
        });

        //����ɾ��
        $("#del_allocation").click(function () {
            var commit_id = 0;
            commit_id = $(".itemlist.selected").attr("fid");
            var projectID = 0;

            if (commit_id == 0) {
                layer.alert('��ѡ��һ����¼,���в���!', {icon: 2});
                return false;
            }

            projectID = $("input[name='" + commit_id + "_ID']").val();

            //����ɾ����һ��ȷ��
            if (confirm("��ȷ��Ҫɾ���û�����¼��?")) {
                $.ajax({
                    url: "<?php echo U('Cost/opAllocation');?>" + "/act/del/transfer_id/" + commit_id + '/project_id/' + projectID,
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        if (data.state) {
                            layer.alert('ɾ���ɹ���', {icon: 0});
                            return false;
                        } else {
                            var msg = data.msg ? data.msg : '�Բ���ɾ��ʧ��<br />ֻ��<font color="#dc143c">δ�ύ���</font>�Ļ�������������ɾ����';
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
        $('#last_filter_result').text($('#last_filter_text').text());  // �������ϴ������Ľ��
    });
</script>
</body>
</html>