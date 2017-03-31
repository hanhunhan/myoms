<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>项目详情</title>
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

		<style>
			.ui-autocomplete {
				max-height: 200px;
				overflow-y: auto;
				/* 防止水平滚动条 */
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
		<input type="hidden" value="<?php echo ($prjId); ?>" id="prjId" />
	  <div>   
  </div>
<script>
	$(function(){
		//关联楼盘
		$("input[name='REL_PROPERTY']").bind('click',function(){
			if($("select[name='CIT_ID']").val()){
				relatedHouse();
			}else{
				alert('请先选择城市');
			}
		});
		

		$("select[name='CIT_ID']").bind('change',function(){

				$("input[name='PRO_ADDR']").parent().prev("span").text('');
				$("input[name='PRO_ADDR']").val('');
				
				$("input[name='PRO_LISTID']").parent().prev("span").text('');
				$("input[name='PRO_LISTID']").val('');
				
				$("input[name='DEV_ENT']").parent().prev("span").text('');
				$("input[name='DEV_ENT']").val('');

				$("input[name='PROPERTY_CLASS']").val('');
				$("input[name='REL_PROPERTY']").val('');

				$("#REL_NEWHOUSEID").val('');
				$("#FORNANJING").val('');

		});


		 //关联项目
		  $("input[name='PRO_NAME']").autocomplete({
			  source:"<?php echo U('Api/getProjectName');?>"
		  });

		
		
		var redirectCity= $(window.parent.parent.topFrame.document).find("#redirectCity").val();
		 
		$("input[name='CONTRACT_NUM']").change(function(){
			if(redirectCity && $("input[name='CONTRACT_NUM']").val()){
				$.ajax({
					url:"<?php echo U('Api/getIsContract2');?>",
					dataType:"json",
					data:{
						'cityId':redirectCity,
						'contractNum':$("input[name='CONTRACT_NUM']").val(),
						'projectId':$("[name='PROJECT_ID']").val()
					},
					success:function(obJect){  
						if(obJect.status){
							if(obJect.state){
								$("input[name='ISCONTRACT']").eq(0).attr("checked",true);
							}else{
								$("input[name='ISCONTRACT']").eq(1).attr("checked",true);
							}
						}else{
							if(obJect.msg) alert(obJect.msg); else
							alert(obJect);
							$("input[name='CONTRACT_NUM']").val("");
							$("input[name='CONTRACT_NUM']").attr("class","Validform_error form-control");
						}
					}

				});
			}
		});
		
		$(":radio[name='ISFUNDPOOL']").bind('change',function(){

			isFundpool();
		});
		isFundpool();

	   var  demo=$(".registerform").Validform();
	   demo.config({
			beforeSubmit:function(curform){

				if($("#formtype").val()=='grid'){
					var str = '';
					 $(".fcanel").each(function(){
						str += ','+$(this).attr('fid');
					 });
					var addids  = $('.newadds').length;  
					 if(str){
						$("#IDS").val(str);  
					 }else if(addids){
					 }else{ alert('请编辑或者添加！'); return false;}
				}

				$("input[name='filesvalue']").each(function(){
					var urll = new Array();
					var fieldName = $(this).attr('tfield'); 
					$("[name='filename_"+fieldName+"']").each(function(){
						var filename = $(this).attr('filename');
						filename = filename.replace("-",'');
						filename = filename.replace(",",'');
						var strr = $(this).attr('id')+'-'+filename.substr(0,32)+'-'+$(this).attr('filesize');
						urll.push(strr); 
					});
					var filecode = urll.join(','); 
					 
					if(filecode){
						//$("input[name='"+fieldName+"']").val(filecode);
						if($("input[name='"+fieldName+"']").length>0){
							$("input[name='"+fieldName+"']").val(filecode);
						}else {
							var inputt = "<input name='"+fieldName+"' type='hidden' value='"+filecode+"' />";
							$(this).after(inputt);
						}
					}
				});

				if($("input[name='PRO_ADDR']").val())
				{
					return true;
				}
				else
				{
					alert('关联楼盘要通过联想选择哦');
					return false;
				}
			}
	   });
	})
	
	//资金池
	function isFundpool()
	{
		var val = $(":radio[name='ISFUNDPOOL']:checked").val();
		
		if(val == -1)//是
		{
			$("input[name='FPSCALE']").removeAttr("ignore");
			$("textarea[name='SPECIALFPDESCRIPTION']").attr("ignore","ignore");
		}
		else if(val == 0)//特殊
		{
			$("input[name='FPSCALE']").attr("ignore","ignore");
			$("textarea[name='SPECIALFPDESCRIPTION']").removeAttr("ignore");
		}
		else if(val == 1)//否
		{
			$("input[name='FPSCALE']").attr("ignore","ignore");
			$("textarea[name='SPECIALFPDESCRIPTION']").attr("ignore","ignore");
		}
	}

	function relatedHouse(){
		//关联楼盘
		 $("input[name='REL_PROPERTY']").autocomplete({
			
			source:function(request, response){
				$.ajax({
					url:"<?php echo U('Api/getHouselist');?>",
					dataType:"json",
					data:{
						'city':$("select[name='CIT_ID']").val(),
						'search':request.term
					},
					success:function(obJect){
						if(!obJect.length)
						{
							$("input[name='PRO_ADDR']").parent().prev("span").text('');
							$("input[name='PRO_ADDR']").val('');
				
							$("input[name='PRO_LISTID']").parent().prev("span").text('');
							$("input[name='PRO_LISTID']").val('');
				
							$("input[name='DEV_ENT']").parent().prev("span").text('');
							$("input[name='DEV_ENT']").val('');

							$("input[name='PROPERTY_CLASS']").val('');
							//$("input[name='REL_PROPERTY']").val('');

							$("#REL_NEWHOUSEID").val('');
							$("#FORNANJING").val('');	
						}

						response($.map(obJect,function(item){
							return {
								label:item.itemname,
								value:item.itemname,
								PRJ_ID:item.prj_id,
								channel:item.channel
							}
						}));
					}

				});
			},
			select:function(event,ui){
				$.ajax({
					url:"<?php echo U('Api/getHouseProperty');?>",
					dataType:"json",
					data:{
						'city':$("select[name='CIT_ID']").val(),
						'search':ui.item.PRJ_ID,
						'channel':ui.item.channel
					},
					success:function(obJect){
						$("input[name='PRO_ADDR']").parent().prev("span").text(obJect.data.loc);
						$("input[name='PRO_ADDR']").val(obJect.data.loc);
						
						$("input[name='PRO_LISTID']").parent().prev("span").text(obJect.data.listid);
						$("input[name='PRO_LISTID']").val(obJect.data.listid);
						
						$("input[name='DEV_ENT']").parent().prev("span").text(obJect.data.kfsname);
						$("input[name='DEV_ENT']").val(obJect.data.kfsname);

						$("input[name='PROPERTY_CLASS']").val(obJect.data.channel_show_name);

						$("#REL_NEWHOUSEID").val(obJect.data.prj_id);
						if($("select[name='CIT_ID']").val() == '1'){
							
							$("#FORNANJING").val(obJect.data.njhouseid);
						}
					}

				});
				 
			}
		 });
	}
</script>
</body>
</html>