<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>�Ǹ��ֳɱ�</title>
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
        .uploadify-queue-item {
            background-color: transparent!important;
        }
    </style>
</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <?php echo ($tabs); ?>
        </div>
        <?php echo ($form); ?>
        <input type="hidden" name="url_current" id="url_current" value="<?php echo U('Purchase/opinionFlow',$paramUrl);?>">
    </div>
</div>
<script>
    $(function () {
        // ���л���¼�¼�
        /*$('tr.itemlist:first-child').addClass('selected');
        $('tr.itemlist').click(function () {
            $('.itemlist.selected').removeClass('selected');
            $(this).addClass('selected');
        });*/
        // $('tr.itemlist:first-child').addClass('selected').find('.checkedtd').prop("checked", true);
//        $('tr.itemlist').addClass('selected').find('.checkedtd').prop("checked", true);
        $('tr.itemlist').click(function () {
            if($(this).hasClass('selected'))
            {
                $(this).removeClass('selected');
                $(this).find('.checkedtd').prop("checked", false);
            }
            else
            {
                $(this).addClass('selected');
                $(this).find('.checkedtd').prop("checked", true);
            }
        });

        // �ύ��������ť����
        $("#non_cash_cost_commit").click(function () {
            // var selectedId = $('tr.itemlist.selected').attr('fid');
            var selectedIds = '';
            var statusNum = 0;
            $('tr.itemlist.selected').each(function(){
                var selectedId = $(this).attr('fid');
                var status = $("select[name=" + selectedId + "_STATUS]").val();
                if(status == 0)
                {
                    selectedIds += selectedId + '-';
                    statusNum = 1;
                }
            })
            selectedIds = selectedIds.substr(0, selectedIds.length - 1);
            if (statusNum == 1) {
                layer.confirm(
                        'ȷ��Ҫ�ύ��������',
                        {title: '�ύ������'},
                        function (index) {

                            var url_base = $('#url_current').val(),
                                    //url = url_base + "/noncashcost_id/" + selectedId + '/FLOWTYPE/feifuxianchengbenshenqing';
									//url = '__APP__/Purchase/opinionFlow/CASE_TYPE/ds/prjid/<?php echo ($projectID); ?>/TAB_NUMBER/26' + '/noncashcost_id/' + selectedIds + '/FLOWTYPE/feifuxianchengbenshenqing';
									 url = '__APP__/Touch/PurchaseNocash/process&noncashcost_id='+selectedIds;
                            window.location.href = url;
                        }
                );
            }
            else {
                layer.alert('δ�ύ������������ύ', {icon: 2});
            }
        });
		var getContractNo = function(){
			 $("select[name='SCALETYPE']").find("option").remove();
            $("select[name='SCALETYPE']").append("<option value=''>��ѡ��</option>");
            if(redirectCity && $("input[name='CONTRACT_NO']").val()){
                $.ajax({
                    url:"<?php echo U('Api/getIsContract');?>",
                    dataType:"json",
                    data:{
                        'cityId':redirectCity,
                        'contractNum':$("input[name='CONTRACT_NO']").val()
                    },
                    success:function(obJect){
                        if(obJect.status){
                            var scaleTypeArr  = [];
							var scaletype = '<?php echo ($scaletype); ?>';
							var selected = '';
                            $.each(obJect.data, function(key, value) {
								
                                switch (value.SCALETYPE){
									 
                                    case '1':
										if(1==scaletype) 
										 scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'" selected="selected" >����</option>');
										else 
                                        scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'"  >����</option>');
                                        break;
                                    case '2':
										if(2==scaletype) scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'" selected="selected" >����</option>');
										else
                                        scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'"  >����</option>');
                                        break;
                                    case '3':
										if(3==scaletype)  
										 scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'" selected="selected" >Ӳ��</option>');
										else
                                        scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'"  >Ӳ��</option>');
                                        break;
                                    case '4':
										if(4==scaletype) 
										 scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'"  selected="selected">�</option>');
										else 
                                        scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'"  >�</option>');
                                        break;
                                    case '5':
										if(5==scaletype) 
										  scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'" selected="selected" >��Ʒ</option>');
										else
                                        scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'"  >��Ʒ</option>');
                                        break;
                                    case '8':
										if(8==scaletype)  
										 scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'"  selected="selected">���ҷ��ճ�</option>');
										else
                                        scaleTypeArr.push('<option value="'+ value['SCALETYPE'] +'"  >���ҷ��ճ�</option>');
                                        break;
                                }
                            })
                                $("select[name='SCALETYPE']").append(scaleTypeArr.join(''));
                                var prjContract = "<?php echo ($prjContract); ?>";
                                var fillContract = $("input[name='CONTRACT_NO']").val();
                                if(prjContract != fillContract){
                                    $("select[name='FEE_ID']").parent().parent().parent().hide();
                                    $("select[name='FEE_ID']").val(0);
                                }

                            }else{
                            alert(obJect.msg);
                            $("input[name='CONTRACT_NO']").val("");
                            $("input[name='CONTRACT_NO']").attr("class","Validform_error");
                        }
                    }
                });
            }
		}
        // ����ͬ�Ƿ���Ч
        var redirectCity= $(window.parent.parent.topFrame.document).find("#redirectCity").val();
        $("input[name='CONTRACT_NO']").change(function(){
           getContractNo();
        });
		if($("input[name='CONTRACT_NO']").val() ){
			getContractNo();
		}

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

        //��ֱ���Ŀ����ʱ����ʾ�ֶη������ͺ�ҵ������
        var showForm = "<?php echo ($showForm); ?>";
        var feeId = "<?php echo ($feeId); ?>";
        if(showForm == 3 ){
            $("select[name='FEE_ID']").parent().parent().parent().hide();
        }
        if(showForm == 1 && feeId == 0){
            $("select[name='FEE_ID']").parent().parent().parent().hide();
        }
        var prjContract = "<?php echo ($prjContract); ?>";
        $("select[name='SCALETYPE']").change(function(){
            var fillContract = $("input[name='CONTRACT_NO']").val();
            var scaleType = $("select[name='SCALETYPE']").val();
            if(prjContract == fillContract && scaleType == 1 ){
                $("select[name='FEE_ID']").parent().parent().parent().show();
            }else{
                $("select[name='FEE_ID']").parent().parent().parent().hide();
                $("select[name='FEE_ID']").val(0);
            }
        })


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