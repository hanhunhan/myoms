<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>����Ԥ��</title>
    <meta charset="GBK">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>-->
    <!--<link href="./Public/css/jquery-ui.css" type="text/css" rel="stylesheet"/>-->
    <!--<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>-->
    <!--<script type="text/javascript" src="./Public/validform/js/common.js"></script>-->
    <!--<script type="text/javascript" src="./Public/js/common.js"></script>-->
    <!--<script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>-->


    <!--<script type="text/javascript" src="./Public/js/jquery-ui.js"></script>-->

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

    <style>
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            /* ��ֹˮƽ������ */
            overflow-x: hidden;
        }
    </style>

</head>
<body>

<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab  ">
            <?php echo ($tabs); ?>
        </div>
        <?php echo ($form); ?>
        <script>
            $(function () {
				var SELECTID = '<?php echo ($SELECTID); ?>';
				$(".contractinfo-table tr").each(function(){
					if( $(this).attr('fid')== SELECTID ){
						$(this).click();
					}
					
				});

                //����¥��
                $("input[name='REL_PROPERTY']").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: "/tpp/adminpack/index.php?s=/Api/getHouselist",
                            dataType: "json",
                            data: {
                                'city': 'nj',//$("select option:selected").val(),
                                'search': request.term
                            },
                            success: function (obJect) {
                                response($.map(obJect.data.list, function (item) {
                                    return {
                                        label: item.itemname,
                                        value: item.itemname,
                                        REL_NEWHOUSEID: item.listid
                                    }
                                }));
                            }
                        });


                    },
                    select: function (event, ui) {
                        $.ajax({
                            url: "/tpp/adminpack/index.php?s=/Api/getHouseProperty",
                            dataType: "json",
                            data: {
                                'city': 'nj',//$("select option:selected").val(),
                                'search': ui.item.REL_NEWHOUSEID
                            },
                            success: function (obJect) {
                                $("input[name='PRO_ADDR']").val(obJect.data.loc);
                                $("input[name='REL_NEWHOUSEID']").val(obJect.data.listid);
                                $("input[name='DEV_ENT']").val(obJect.data.kfsname);
                                $("input[name='PROPERTY_CLASS']").val(obJect.data.channel_show_name);
                            }
                        })
                    }
                });

                //������Ŀ
                $("input[name='PRO_NAME']").autocomplete({
                    source: "<?php echo U('Api/getProjectName');?>"/*'/adminpack/index.php?s=/Api/getProjectName',*/
                });

                //�ʽ�ر�������ʾ
                if ('<?php echo ($hchange[VALUEE]); ?>') {
                    var FPSCALE = '<?php echo ($hchange[VALUEE]); ?> <span class="fclos fred">[ԭ]<?php echo ($hchange[ORIVALUEE]); ?> </span>';
                    $(".contractinfo-table tbody tr").each(function () {
                        var thisid = $(this).attr('fid');
                        var tag = thisid + "_" + "FPSCALE";
                        console.log(tag);
                        $("[name='" + tag + "']").parent().parent().find('span').first().html(FPSCALE);

                    });
                }

                /***
                 * ����Ƿ��ҷ��ճ����ʾ�����շѱ�׼�Լ��ⲿ������׼��������ʾ
                 * ����ѡ�������Ƿ���������ҷ��ճ���������ء���ʾ�������շѱ�׼�������ⲿ������׼��ҳǩ
                 */
                (function () {
                    // ���ص����շѱ�׼ҳǩ
                    function hideSingleFeeScaleTab() {
                        // �������շѱ�׼������
                        var singleFeeScaleReg = /\u5355\u5957\u6536\u8d39\u6807\u51c6/;
                        var lis = $('li.twolevelli');
                        for (var i = 0; i < lis.length; i++) {
                            var innerText = lis[i].innerText;
                            if (singleFeeScaleReg.test(innerText)) {
                                $(lis[i]).hide();
                            }
                        }
                    }

                    // ��ʾ�����շѱ�׼ҳǩ
                    function showSingleFeeScaleTab() {
                        $('li.twolevelli').show();
                    }
                    
                    // �����ⲿ������׼ҳǩ
                    function hideOutRewardScaleTab(){
                        // ���ⲿ������׼������
                        var outRewardScaleReg = /\u5916\u90e8\u6210\u4ea4\u5956\u52b1/;
                        var lis = $('li.twolevelli');
                        for (var i = 0; i < lis.length; i++) {
                            var innerText = lis[i].innerText;
                            if (outRewardScaleReg.test(innerText)) {
                                $(lis[i]).hide();
                            }
                        }
                        
                    }
                    
                    // ��ʾ�ⲿ������׼ҳǩ
                    function showOutRewardScaleTab() {
                        $('li.twolevelli').show();
                    }
                    
                    // ���·��ҷ��ճ�ҳ��
                    function updateFwfscPage() {
                        // �����ҷ��ճ����
                        var fwfscReg = /\u975e\u6211\u65b9\u6536\u7b79/;
                        var currentSelectedRowHtml = $('.itemlist.selected').text();
                        if (fwfscReg.test(currentSelectedRowHtml)) {
                            hideSingleFeeScaleTab();
                            hideOutRewardScaleTab();
                        } else {
                            showSingleFeeScaleTab();
                            showOutRewardScaleTab();
                        }
                    }

                    // ִ�з���
                    updateFwfscPage();
                    $('tr.itemlist').click(updateFwfscPage);
                })();
            });

            function budgetfeetotal() {
                url = appUrl + '/House/budGetFeeTotal/prjid/' + '<?php echo ($prjid); ?>';
                window.open(url);
            }
        </script>
    </div>
</div>
</body>
</html>