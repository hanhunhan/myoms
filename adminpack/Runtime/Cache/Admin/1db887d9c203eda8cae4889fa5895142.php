<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>退房管理</title>
       <meta charset="GBK">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
        <link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>
        <link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all" />
        <link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all" />
        <script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>
        <script type="text/javascript" src="./Public/validform/js/common.js"></script>
        <script type="text/javascript" src="./Public/js/common.js"></script>
        <script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>
        <script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>
        <script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>
        <script type="text/javascript" src="Public/js/jquery.nicescroll.min.js"></script>

        <!--解决两个版本autocomplete冲突-->
        <?php if($showForm > 0): ?><script src="./Public/third/jquery_ui_autocomplete/ui/jquery.ui.core.js"></script>
            <script src="./Public/third/jquery_ui_autocomplete/ui/jquery.ui.widget.js"></script>
            <script src="./Public/third/jquery_ui_autocomplete/ui/jquery.ui.position.js"></script>
            <script type="text/javascript" src="./Public/third/jquery_ui_autocomplete/ui/jquery.ui.autocomplete.js"></script>
            <link rel="stylesheet" href="./Public/third/jquery_ui_autocomplete/themes/base/jquery.ui.all.css"><?php endif; ?>
        <script language="javascript" type="text/javascript" src="./Public/layer/layer.js"></script>
        <script src="//cdn.bootcss.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="containter">

            <div class="right fright j-right">

					<div class="handle-tab">
<ul>
<li class="selected">
<a  >退房管理</a>
</li>
 
</ul>
</div>
                 
                <?php echo ($form); ?>
            </div>
        </div>
    <script type="text/javascript">
	 
	$(function(){
		   $('html').niceScroll();
        // 获取上次的搜索条件
        var lastFilterResult = '<?php echo ($lastFilter); ?>';
        $('#last_filter_result').text(lastFilterResult);

			var fids  = new Array();
			$('.contractinfo-table table tr').each(function(){
				fids.push($(this).attr('fid'));  
			
			});
			if(fids){
				$.ajax({
					type: "POST",
					url: "<?php echo U('Member/return_member');?>",
					data:{'memberId':fids,'faction':'getfeescale'},
					dataType:"JSON",
					success:function(d){
						if(d.status ==1)
						{ 
							var dd = d.data; 
							$.each( dd,function(key,val){  
								 
								if(val['memberid']){   
									if($("input[name='"+val['memberid']+"_TOTAL_PRICE_AFTER']").val()>0) 
									$("input[name='"+val['memberid']+"_TOTAL_PRICE_AFTER']").parent().prev().append(val['dw']);
								}
							});
						}
						 
					}
				})
			}
	 
			$("#return_mem").click(function(){
				var memberId = new Array();
				var i = 0;
				$("input[name= 'checkedtd']:checkbox").each(function() 
				{   
					if ($(this).prop("checked") == true) 
					{  
					   memberId[i] = $(this).val();  
					   i += 1;
					}
				}); 
				
				if( i == 0 )
				{   
					layer.alert('请至少选择一条记录!', {icon: 2});
					return false;
				}
				
				$.ajax({
					type: "GET",
					url: "<?php echo U('Member/return_member');?>",
					data:{'memberId':memberId,'refund_method':'mid','re_type':'tuifang'},
					dataType:"JSON",
					success:function(data){
						if(data.state == 0)
						{
							layer.alert(data.msg, {icon: 2});
						}
						else if(data.state == 1)
						{
							layer.alert(data.msg, {icon: 1});
							window.location.reload();
						}
						else
						{
							var msg = data.msg ? data.msg : '操作异常';
							layer.alert(msg, {icon: 2});
						}
					}
				});

			});
		
		});
	</script>
        
    </body>
</html>