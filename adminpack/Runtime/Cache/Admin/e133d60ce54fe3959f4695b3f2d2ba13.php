<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>报销确认</title>
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

    <script>
        function reim_confirm() {
            var reim_id = new Array();
            var i = 0;
            $("input[name= 'checkedtd']:checkbox").each(function () {
                if ($(this).prop("checked") == true) {
                    reim_id[i] = $(this).val();
                    i += 1;
                }
            });

            if (reim_id.length == 0) {
                layer.alert("请至少选择一条记录！", {icon: 0});
                return false;
            }

            $.ajax({
                type: "post",
                url: "index.php?s=/Financial/reimConfirm&act=doReimConfirm",
                data: {reim_id: reim_id},
                dataType: "JSON",
                success: function (data) {
                    if (data.state == 1) {
                        var url = "<?php echo U('Financial/reimConfirm',$paramUrl);?>";
                        layer.alert(data.msg, {icon: 1}, function () {
                            window.location.href = url
                        });
                    } else if (data.state == 0) {
                        layer.alert(data.msg, {icon: 2});
                    } else {
                        layer.alert(data.msg, {icon: 2});
                    }
                }
            })
        }

        //财务打回报销单
        function reim_refuse() {
            var reim_id = new Array();
            var amount = new Array();
            var i = 0;
            $("input[name= 'checkedtd']:checkbox").each(function () {
                if ($(this).prop("checked") == true) {
                    reim_id[i] = $(this).val();
                    amount[i] = $("input[name='" + reim_id[i] + "_AMOUNT']").val();
                    i += 1;
                }
            });
            if (reim_id.length == 0) {
                layer.alert("请至少选择一条记录！", {icon: 0});
                return false;
            }
            $.ajax({
                type: "post",
                url: "index.php?s=/Financial/reim_refuse",
                data: {'reim_id': reim_id, 'amount': amount},
                dataType: "JSON",
                success: function (data) {
                    if (data.state == 1) {
                        var url = "<?php echo U('Financial/reimConfirm',$paramUrl);?>";
                        layer.alert(data.msg, {icon: 1}, function () {
                            window.location.href = url
                        });
                    } else if (data.state == 0) {
                        layer.alert(data.msg, {icon: 2});
                    }
                }
            })
        }

		$(function(){
			
		
			 /*** 编辑***/
			$('#reim_confirm_time').click(function () {
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
					$(this).html('取消编辑');
					$(this).attr('operate_type', 'cancel_purchase');
					$(this).after('<a id="save_purchase" class="btn btn-info btn-sm" href="javascript:;" onclick="save_purchase()">保存编辑</a>');
					$('#lower_price').show();
					$('#add_reim').hide();

					edit_purchase(purchaseId);
					updateCheckBoxStatus(true);  // 更新选择框的状态
					 
				} else if (operate_type == 'cancel_purchase') {  // 取消采购
					//location.reload();  // todo
					//return;
					var fid = $(this).attr('fid').split('#');
					cancel_purchase(fid);
					updateCheckBoxStatus(false);
					$(this).attr('fid', 0);
					$(this).html('编辑确认时间');
					$(this).attr('operate_type', 'edit_purchase');
					$('#save_purchase').remove();
					$('#lower_price').hide();
					$('#add_reim').show();
				}
			});
		
		})



		 // 采购
        function edit_purchase(fidList) {
            // 可编辑框
            var enableFieldEditor = function (fid) {
                // 费用发生时间
                $("input[name=" + fid + "_REIM_TIME]").parent().show().siblings('span').hide();

                 
            };

             

            

            for (var i = 0; i < fidList.length; i++) {
                var fid = fidList[i];  // 采购明细id
                enableFieldEditor(fid);  // 可编辑框
               
            }

            // 编辑栏样式控制
             
        }

        //取消采购编辑
        function cancel_purchase(fidlist) {
            // 转换成数组统一处理
            if (fidlist.constructor != Array) {
                fidlist = [fidlist];
            }

            // 取消一条采购明细的采购
            var cancelOneItem = function (fid) {
               
                $("input[name=" + fid + "_REIM_TIME]").parent().hide().siblings('span').show().siblings('.info').remove();
            };

            for (var i = 0; i < fidlist.length; i++) {
                var fid = fidlist[i];
                cancelOneItem(fid);
            }
        }

		function save_purchase(){
			//var purchase_id = $('#edit_purchase').attr('fid');
			//if (!isNaN(purchase_id)) {
				//查找其他参数是否已经填写
				var   REIMTIME = new Array();
				var reimDetailId  = new Array();
				var i=0;
				 $('.checkedtd').each(function () {
					if ($(this).prop("checked") == true) {
						reimDetailId[i] = $(this).val();
						 REIMTIME[i] = $("input[name='"+reimDetailId[i]+"_REIM_TIME']").val();
						 
						 i++;
					} 
				 });

				//if (Reim_time ) {
					$.ajax({
						type: "GET",
						url: "<?php echo U('Financial/ajax_update_reimtime');?>",
						data: {
							REIMTIME: REIMTIME,
							reimDetailId: reimDetailId
						},
						dataType: 'JSON',
						success: function (data) {
							if (data.status == 0) {
								layer.alert(data.msg, {icon: 2});
							}
							else if (data.status == 1) {
								layer.alert(data.msg, {icon: 1}, function (index) {
									layer.close(index);
									//updateCheckBoxStatus(false);
									window.location.href=window.location.href;
								});
							}
							else {
								var msg = data.msg ? data.msg : '操作异常';
								layer.alert(msg, {icon: 2, closeBtn: false});
							}
						}
					})
				//}
				//else {
					//layer.alert('采购无领用时,采购供应商、采购单价、采购数量都必须填写', {icon: 2});
				//}
			//}
			//else {
				//提醒用户选中采购明细
				//layer.alert('无法获取明细信息', {icon: 2});
			//}
		}
    </script>
</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <ul>
                <li><a href="<?php echo U('Financial/financialConfirm',$paramUr);?>">预收确认</a></li>
                <li><a href="<?php echo U('Financial/invoice',$paramUrl);?>">开票</a></li>
                <li class="selected"><a href="<?php echo U('Financial/reimConfirm',$paramUrl);?>">报销确认</a></li>
                <li><a href="<?php echo U('Financial/yw_invoice',$paramUrl);?>">业务开票</a></li>
                <li><a href="<?php echo U('Financial/business_change_invoice',$paramUrl);?>">业务换票</a></li>
                <li><a href="<?php echo U('Financial/business_refund_invoice',$paramUrl);?>">业务退票</a></li>
                <li><a href="<?php echo U('Financial/yw_refund',$paramUrl);?>">业务回款</a></li>
                <li><a href="<?php echo U('Financial/callback_commission',$paramUrl);?>">佣金索回</a></li>
            </ul>
        </div>
        <?php echo ($form); ?>
    </div>
</div>
<textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // 附带上上次搜索的结果
    });
</script>
<script>
$(function(){
	 $("#ifm").load(function(){
        var mainheight = $("#ifm").contents().find(".registerform").height();
        var before_registerform = $("#ifm").contents().find(".before-registerform").height();


        if(!mainheight)
            mainheight = $("#ifm").contents().find(".registerform2").height();


        if(before_registerform)
            mainheight = mainheight + before_registerform;

        mainheight = mainheight + 200;
        $("#ifm").height(mainheight);
    });
})
</script>
</body>
</html>