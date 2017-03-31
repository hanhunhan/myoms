<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>办卡会员</title>
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
                <div class="handle-tab"><?php echo ($tabs); ?></div>
                <?php echo ($form); ?>
            </div>
        </div>
        <script type="text/javascript">
            var filter_sql = "<?php echo urlencode($filter_sql); ?>";
            var sort_sql = "<?php echo $sort_sql; ?>";
            $(function(){

                var lastFilterResult = '<?php echo ($lastFilter); ?>';
                $('#last_filter_result').text(lastFilterResult);

//                $('html').niceScroll();  // 美化滚动条
				var case_type = '<?php echo ($case_type); ?>';
				if(case_type=='fx'){
					var fids  = new Array();
					$('.contractinfo-table table tr').each(function(){
						fids.push($(this).attr('fid'));  
					
					});
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
//					$('.contractinfo-table table tr').find('td:eq(22)').hide();
//					$('.contractinfo-table table tr').find('th:eq(22)').hide();
//					$('.contractinfo-table table tr').find('td:eq(23)').hide();
//					$('.contractinfo-table table tr').find('th:eq(23)').hide();
//					$('.contractinfo-table table tr').find('td:eq(24)').hide();
//					$('.contractinfo-table table tr').find('th:eq(24)').hide();
//                    $('.contractinfo-table table tr').find('td:eq(25)').hide();
//                    $('.contractinfo-table table tr').find('th:eq(25)').hide();
				}else{
//					$('.contractinfo-table table tr').find('td:eq(19)').hide();
//					$('.contractinfo-table table tr').find('th:eq(19)').hide();
//					$('.contractinfo-table table tr').find('td:eq(20)').hide();
//					$('.contractinfo-table table tr').find('th:eq(20)').hide();
//					$('.contractinfo-table table tr').find('td:eq(21)').hide();
//					$('.contractinfo-table table tr').find('th:eq(21)').hide();
//					$('.contractinfo-table table tr').find('td:eq(22)').hide();
//					$('.contractinfo-table table tr').find('th:eq(22)').hide();
//					$('.contractinfo-table table tr').find('td:eq(23)').hide();
//					$('.contractinfo-table table tr').find('th:eq(23)').hide();
				}
                $.widget("custom.autocomplete", $.ui.autocomplete, {
                    _renderItem: function( ul, item ) {
                        if(item.id > 0){
                            return $( "<li>" )
                            .data( "item.autocomplete", item )  
                            .append('<a class="ui-corner-all" tabindex="-1"><span class="ui_name">'+item.label
                            +'</span>&nbsp;&nbsp;<span class="ui_district">['+item.id+']</span></a>')
                            .appendTo( ul );
                        }else{
                            return $( "<li>" )
                            .data( "item.autocomplete", item )  
                            .append('<a class="ui-corner-all" tabindex="-1">'+item.label+'</a>')
                            .appendTo( ul );
                        }
                    }
                });
                
                $(".project_name").autocomplete({
                    source: function( request, response ) 
                    {   
                        var cmt_name = request.term;
						var case_type = '<?php echo ($case_type); ?>';
                        $.ajax({
                            url: "<?php echo U('Project/ajax_get_project_list');?>",
                            type: "GET",
                            dataType: "JSON",
                            data: {keyword: cmt_name,case_type:case_type},
                            success: function(data) 
                            {
                                if(data.status=='noauth'){
									alert(data.msg);
									window.location.href=window.location.href;
								}else{
								//判断返回数据是否为空，不为空返回数据。
                                if(data[0]['id'] > 0)
									{
										response(data);
									}
									else
									{
										response(data);
									}	
								}
                            }
                        });
                    },
                    minLength: 1,
                    removeinput: 0,
                    select: function(event , ui) 
                    {   
                        if(ui.item.id > 0)
                        {
                            var project_name = ui.item.label;
                            var project_id = ui.item.id;
                            var city_id = ui.item.city_id;
							var case_type = '<?php echo ($case_type); ?>';
                            $('#PRJ_ID').remove();
                            $('#CITY_ID').remove();
                            var str_input_prj_id = "<input type='hidden' name='PRJ_ID' id='PRJ_ID' value="+ project_id +">";
                            var str_input_city_id = "<input type='hidden' name='CITY_ID' id='CITY_ID' value="+ city_id +">";
                            $(this).after(str_input_prj_id);
                            $(this).after(str_input_city_id);
                            
                            //设置收费标准
							if(case_type=='ds') set_price_standard_select(project_id);
							if(case_type=='fx') set_price_standard_select_fx(project_id);
                            //设置新房楼盘编号
                            set_pro_list_id(project_id);
                            //设置会员来源
                            get_user_source(project_id);
                            removeinput = 2;
							//设置前后佣输入框
							set_front_after(project_id,case_type);
							
                        }
                        else
                        { 
                           removeinput = 1;
                        }
                    },
                    close: function(event) {
                        if(typeof(removeinput) == 'undefined' || removeinput == 1)
                        {
                            $(this).val('');
                            $('#PRJ_ID').remove();
                            $('#CITY_ID').remove();
                            //清空收费标准下拉列表
                            cancle_price_standard_select();
                            //清空新房楼盘INPUT
                            cancle_pro_list_id();
                            //清空会员来源下拉列表
                            cancle_user_source();
                        }
                    }
                });
                //直销人员
                $("input[name='DIRECTSALLER']").autocomplete({
                    source: function (request, response) {
                        $.ajax({
                            url: "index.php?s=/Api/getDirectSaller",
                            dataType: "JSON",
                            data: {
                                "search": request.term,
                                "roleId": $("#roleId").val()
                            },
                            success: function (obJect) {
                                response($.map(obJect, function (item) {
                                    return {
                                        label: item.deptname ,
                                        value: item.name,
                                        USERID: item.id,
                                        PHONE: item.phone,
                                        CITY: item.city,
                                    }
                                }));
										response(data);
                            }
                        });
                    },
                    minLength: 1,
                    removeinput: 0,
                    select: function (event, ui) {
                        if(ui.item.USERID > 0){
                            var city_id = ui.item.CITY;
                            var phone = ui.item.PHONE;
                            var str_input_phone = "<input type='hidden' name='DirectSallerPhone' id='DirectSallerPhone' value="+ phone +">";
                            var str_input_city_id = "<input type='hidden' name='DirectSallerCity' id='DirectSallerCity' value="+ city_id +">";
                            $(this).after(str_input_phone);
                            $(this).after(str_input_city_id);
                            removeinput = 2;
                        }else{
                            removeinput = 1;                        
                        }
                    },

                });
				if(!$(".TOTAL_PRICE").children('option:selected').val()){
					$(".AGENCY_REWARD").attr("disabled",true);  
				}
				if(!$(".TOTAL_PRICE_AFTER").children('option:selected').val()){
					$(".AGENCY_REWARD_AFTER").attr("disabled",true); 
				}
				$(".TOTAL_PRICE").change(function(){
					var t_select = $(this).children('option:selected').val();
					if(t_select>0){
						$(".AGENCY_REWARD").attr("disabled",false);
					}else{  
						$(".AGENCY_REWARD").attr("disabled",true);
					}	 
				
				});
				$(".TOTAL_PRICE_AFTER").change(function(){
					var t_select = $(this).children('option:selected').val();
					if(t_select>0){
						$(".AGENCY_REWARD_AFTER").attr("disabled",false);
					}else{  
						$(".AGENCY_REWARD_AFTER").attr("disabled",true);
					}	 
				
				});
                //判断收费标准 控制输入框显示
				function set_front_after(prj_id,case_type){
					if(case_type=='fx'){
						$.ajax({
                            type: "GET",
                            url: "<?php echo U('Project/ajax_get_fx_feescale');?>",
                            data:{'prj_id':prj_id, 'case_type': case_type },
                            dataType:"JSON",
                            success:function(data)
                            {
								if(data['dtsf_front']==1){
									$("select[name='TOTAL_PRICE']").parent().show();
								}else{  
									//$("select[name='TOTAL_PRICE']").parent().hide();
								}
								if(data['dtsf_after']==1){
									$("select[name='TOTAL_PRICE_AFTER']").parent().show();
								}else{
									//$("select[name='TOTAL_PRICE_AFTER']").parent().hide();
								}
								if(data['zjyj_front']==1){
									$("select[name='AGENCY_REWARD']").parent().show();
								}else{
									//$("select[name='AGENCY_REWARD']").parent().hide();
								}
								if(data['zjyj_after']==1){
									$("select[name='AGENCY_REWARD_AFTER']").parent().show();
								}else{
									//$("select[name='AGENCY_REWARD_AFTER']").parent().hide();
								}
								
							}
                                 
                        });
						
						
					}

				}
				//获取收费标准
                function set_price_standard_select_fx(prj_id)
                {
                    if( prj_id > 0 )
                    {   
                    	var scale_type = '';
                    	var case_type = 'fx';
                        $.ajax({
                            type: "GET",
                            url: "<?php echo U('Project/ajax_get_feescale');?>",
                            data:{'prj_id':prj_id, 'case_type': case_type, 'scale_type':scale_type },
                            dataType:"JSON",
                            success:function(data)
                            {
                                if(data[0]['ID'] == 0)
                                {
                                     
                                }
                                else if(data[0]['ID'] >= 1)
                                {   
                                    var output = [];//单套收费标准
									var output_after = [];//单套收费标准
                                    var output_a_reward = [];//中介佣金
									var output_a_reward_after = [];//中介佣金
									var output_a_deal_reward = [];//中介成交奖
                                    var output_p_reward = [];//置业顾问佣金
                                    var output_p_deal_reward = [];//置业顾问成交奖
                                   
 
                                    $.each(data, function(key, value)
                                    {  
                                        var dw = value['STYPE']==1  ? '%':'元';
										switch (value['SCALETYPE'])
                                        { 
                                            case '1' :
												if(value['MTYPE']==1){
													output_after.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT']+dw +'</option>');
												}else{
													output.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +dw+'</option>');
												}
                                                break;
                                            case '2' :
												if(value['MTYPE']==1){
													output_a_reward_after.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT']+dw +'</option>');
												}else{
													output_a_reward.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +dw+'</option>');
												}
                                                break;
                                            case '3' :
                                                output_p_reward.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +dw+'</option>');
                                                break;
                                            case '4' :
                                                output_a_deal_reward.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +dw+'</option>');
                                                break;
                                            case '5' :
                                                output_p_deal_reward.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT']+dw +'</option>');
                                                break;
                                        }
                                    });
                                    cancle_price_standard_select();
                                    $("select[name='TOTAL_PRICE']").append(output.join(''));
									$("select[name='TOTAL_PRICE_AFTER']").append(output_after.join(''));
                                    $("select[name='AGENCY_REWARD']").append(output_a_reward.join(''));
									$("select[name='AGENCY_REWARD_AFTER']").append(output_a_reward_after.join(''));
                                    $("select[name='AGENCY_DEAL_REWARD']").append(output_a_deal_reward.join(''));
                                    //$("select[name='PROPERTY_REWARD']").append(output_p_reward.join(''));
									$("select[name='OUT_REWARD']").append(output_p_reward.join(''));
                                    $("select[name='PROPERTY_DEAL_REWARD']").append(output_p_deal_reward.join(''));
                                }
                                else
                                {
                                     
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_price_standard_select();
                        layer.alert('项目信息异常!', {icon: 2});
                        return false;
                    }
                }
                
                //获取收费标准
                function set_price_standard_select(prj_id)
                {
                    if( prj_id > 0 )
                    {   
                    	var scale_type = '';
                    	var case_type = 'ds';
                        $.ajax({
                            type: "GET",
                            url: "<?php echo U('Project/ajax_get_feescale');?>",
                            data:{'prj_id':prj_id, 'case_type': case_type, 'scale_type':scale_type },
                            dataType:"JSON",
                            success:function(data)
                            {
                                if(data[0]['ID'] == 0)
                                {
                                    layer.alert('项目立项时未填写单套收费标准，请项目发起人及时变更立项填写单套收费标准', {icon: 2});
                                    cancle_price_standard_select();
                                }
                                else if(data[0]['ID'] >= 1)
                                {   
                                    var output = [];//单套收费标准
                                    var output_a_reward = [];//中介佣金
                                    var output_a_deal_reward = [];//中介成交奖
                                    var output_p_reward = [];//置业顾问佣金
                                    var output_p_deal_reward = [];//置业顾问成交奖

                                    $.each(data, function(key, value)
                                    {   
                                        switch (value['SCALETYPE'])
                                        {
                                            case '1' :
                                                output.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +'</option>');
                                                break;
                                            case '2' :
                                                output_a_reward.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +'</option>');
                                                break;
                                            case '3' :
                                                output_p_reward.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +'</option>');
                                                break;
                                            case '4' :
                                                output_a_deal_reward.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +'</option>');
                                                break;
                                            case '5' :
                                                output_p_deal_reward.push('<option value="'+ value['AMOUNT'] +'">'+ value['AMOUNT'] +'</option>');
                                                break;
                                        }
                                    });
                                    cancle_price_standard_select();
                                    $('.TOTAL_PRICE').append(output.join(''));
                                    $('.AGENCY_REWARD').append(output_a_reward.join(''));
                                    $('.AGENCY_DEAL_REWARD').append(output_a_deal_reward.join(''));
                                    $('.PROPERTY_REWARD').append(output_p_reward.join(''));
									$("select[name='OUT_REWARD']").append(output_p_reward.join(''));
                                    $('.PROPERTY_DEAL_REWARD').append(output_p_deal_reward.join(''));
                                }
                                else
                                {
                                    layer.alert('项目立项时未填写单套收费标准，请项目发起人及时变更立项填写单套收费标准', {icon: 2});
                                    cancle_price_standard_select();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_price_standard_select();
                        layer.alert('项目信息异常!', {icon: 2});
                        return false;
                    }
                }
                
                //取消收费标准下拉列表
                function cancle_price_standard_select()
                {   
                    var option_str = '<option value="">请选择</option>';
                    $('.TOTAL_PRICE').empty();
                    $('.TOTAL_PRICE').html(option_str);
					$('.OUT_REWARD').empty();
                    $('.OUT_REWARD').html(option_str);
					$('.TOTAL_PRICE_AFTER').empty();
                    $('.TOTAL_PRICE_AFTER').html(option_str);
                    $('.AGENCY_REWARD').empty();
                    $('.AGENCY_REWARD').html(option_str);
					$('.AGENCY_REWARD_AFTER').empty();
                    $('.AGENCY_REWARD_AFTER').html(option_str);
                    $('.AGENCY_DEAL_REWARD').empty();
                    $('.AGENCY_DEAL_REWARD').html(option_str);
                    $('.PROPERTY_REWARD').empty();
                    $('.PROPERTY_REWARD').html(option_str);
                    $('.PROPERTY_DEAL_REWARD').empty();
                    $('.PROPERTY_DEAL_REWARD').html(option_str);
					
                }
                
                //获取会员来源
                function get_user_source(prj_id)
                {
                    if( prj_id > 0 )
                    {   
                        $.ajax({
                            type: "GET",
                            url: "<?php echo U('Project/ajax_get_project_budget_sale_by_pid');?>",
                            data:{'prj_id':prj_id},
                            dataType:"JSON",
                            success:function(data)
                            {
                                if(data == null || data[0]['id'] == 0)
                                {
                                    layer.alert('项目目标分解，没有填写销售方式，无法添加会员！', {icon: 2});
                                    cancle_user_source();
                                }
                                else if(data[0]['id'] > 0)
                                {   
                                    var user_source = [];//会员来源
                                    $.each(data, function(key, value)
                                    {   
                                        user_source.push('<option value="'+ value['id'] +'">'+ value['name'] +'</option>');
                                    });
                                    
                                    cancle_user_source();
                                    $('.source').append(user_source.join(''));
                                }
                                else
                                {
                                    var msg = data.msg ? data.msg : '操作异常';
                                    layer.alert(msg, {icon: 2});
                                    cancle_user_source();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_user_source();
                        layer.alert('项目信息异常!', {icon: 2});
                        return false;
                    }
                }
                
                //取消会员来源下拉列表
                function cancle_user_source()
                {
                    var option_str = '<option value="">请选择</option>';
                    $('.source').empty();
                    $('.source').html(option_str);
                }
                
                //获取新房楼盘编号
                function set_pro_list_id(prj_id)
                {
                    if( prj_id > 0 )
                    {   
                    	var scale_type = '';
                        $.ajax({
                            type: "GET",
                            url: "<?php echo U('Project/ajax_get_houseinfo_by_pid');?>",
                            data:{'project_id':prj_id},
                            dataType:"JSON",
                            success:function(data)
                            {
                                if(data == null || data['ID'] == 0)
                                {
                                    layer.alert('项目立项时未绑定新房楼盘信息，为不影响到场确认操作，请项目发起人及时变更项目楼盘信息！', {icon: 2});
                                    cancle_pro_list_id();
                                }
                                else if(data['ID'] >= 1)
                                {   
                                	var list_id = data['PRO_LISTID'];
                                	if(list_id > 0)
                                	{
                                            var str_input_list_id = "<input type='hidden' name='LIST_ID' id='LIST_ID' value="+ list_id +">";
                                            $('.project_name').after(str_input_list_id);
                                	}
                                	else
                                	{
                                            layer.alert('项目立项时未绑定新房楼盘信息，为不影响到场确认操作，请项目发起人及时变更项目楼盘信息！', {icon: 2});
                                            cancle_pro_list_id();
                                	}
                                }
                                else
                                {
                                    var msg = data.msg ? data.msg : '操作异常';
                                    layer.alert(msg, {icon: 2});
                                    cancle_pro_list_id();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_pro_list_id();
                        layer.alert('项目信息异常!', {icon: 2});
                        return false;
                    }
                }
                
                //取消新房楼盘编号字段
                function cancle_pro_list_id()
                {
                    $('#LIST_ID').remove();
                }
                
                //申请开票
                $("#apply_invoice").click(function(){
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
                        type: "POST",
                        url: "<?php echo U('Member/apply_invoice');?>",
                        data:{'memberId':memberId},
                        dataType:"JSON",
                        success:function(data){
                            if(data.state == 0)
                            {
                                layer.alert(data.msg, {icon: 2}, 
                                    function(){window.location.reload();});
                            }
                            else if(data.state == 1)
                            {
                                layer.alert(data.msg, {icon: 1}, 
                                    function(){window.location.reload();});
                            }
                            else
                            {
                                var msg = data.msg ? data.msg : '操作异常';
                                layer.alert(msg, {icon: 2},
                                    function(){window.location.reload();});
                            }
                        }
                     })   
                })
                
                //中介费用报销申请
                $("#agency_reward_reim,#agency_deal_reward_reim,#property_deal_reward_reim,#out_reward_reim").click(function()
                {
                    var reim_type_str = $(this).attr('id');
                    var memberId = new Array();
                    var i = 0;
					var case_type = '<?php echo ($case_type); ?>';
					if(case_type=='fx')  reim_type_str = reim_type_str+'_fx';
                    $("input[name='checkedtd']:checkbox").each(function() 
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
                        type: "POST",
                        url: "<?php echo U('Reimbursement/apply_agency_reward_reim');?>",
                        data:{'memberId':memberId, 'reim_type_str':reim_type_str},
                        dataType:"JSON",
                        success:function(data){
                            if(data.state == 0)
                            {
                                layer.alert(data.msg, {icon: 2, closeBtn: false}, 
                                    function(){window.location.reload();});
                            }
                            else if(data.state == 1)
                            {
                                layer.alert(data.msg, {icon: 1, closeBtn: false}, 
                                    function(){window.location.reload();});
                            }
                            else
                            {
                                var msg = data.msg ? data.msg : '操作异常';
                                layer.alert(msg, {icon: 2, closeBtn: false},
                                  function(){window.location.reload();});
                            }
                        }
                     })   
                })
                
                //通过会员申请退款
                $("#refund_by_mid").click(function()
                {
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
                        url: "<?php echo U('MemberRefund/apply_refund');?>",
                        data:{'memberId':memberId,'refund_method':'mid'},
                        dataType:"JSON",
                        success:function(data){
                            if(data.state == 0)
                            {
                                layer.alert(data.msg, {icon: 2});
                            }
                            else if(data.state == 1)
                            {
                                layer.alert(data.msg, {icon: 1});
                            }
                            else
                            {
                                var msg = data.msg ? data.msg : '操作异常';
                                layer.alert(msg, {icon: 2});
                            }
                        }
                     })   
                })

                //批量转移项目
                $("#move_member_prj").click(function(){

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

                    if( i == 0 ) {
                        layer.alert('请至少选择一条记录!', {icon: 2});
                        return false;
                    }

                    //验证数据
                    $.ajax({
                        type: "GET",
                        async: false,
                        url: "<?php echo U('Member/moveProject');?>&memberIds=" + memberId + '&isCheck=1',
                        dataType:"JSON",
                        success:function(data)
                        {
                            if(data.status){
                                showWindow();
                            }else{
                                layer.alert(data.msg, {icon: 2});
                            }
                        },
                        error:function(){
                            layer.alert('操作异常，请联系管理员',{icon: 2});
                        }
                    });

                    function showWindow() {
                        var iframeMoveProject = layer.open({
                            type: 2,
                            title: '转移项目',
                            content: "<?php echo U('Member/moveProject');?>&showWindow=1&memberIds=" + memberId,
                            area: ['680px', '300px'],
                            btn: ['确认转移', '取消']
                            , yes: function (index) {
                                var toCaseId = $(".toCaseId", window.frames["layui-layer-iframe" + iframeMoveProject].document).val();
                                var fromCaseId = $(".fromCaseId", window.frames["layui-layer-iframe" + iframeMoveProject].document).val();

                                $.ajax({
                                    type: "GET",
                                    url: "<?php echo U('Member/moveProject');?>&memberIds=" + memberId,
                                    data: {
                                        'fromCaseId': fromCaseId,
                                        'toCaseId': toCaseId,
                                    },
                                    dataType: "JSON",
                                    success: function (data) {
                                        if (data.status) {
                                            layer.close(index);
                                            layer.alert(data.msg, {icon: 1}, function () {
                                                window.location.reload();
                                            });
                                        } else {
                                            layer.alert(data.msg, {icon: 2});
                                        }
                                    },
                                    error: function (data){
                                        alert('网络错误，请联系管理员！');
                                    },
                                })
                            }
                            , cancel: function (index) {
                                layer.close(index);
                            }
                        });
                    }

                });

                //批量修改状态
                $("#batch_change_status").click(function()
                {
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

                    //判断项目名称是否不同
                    var isReward = 1;
                    for(i = 0;i < memberId.length-1;i++){
                        var projectName = $("input[name='"+memberId[i]+"_PRJ_NAME_OLD']").val();
                        var projectNameNext =  $("input[name='"+memberId[i+1]+"_PRJ_NAME_OLD']").val();
                        if(projectName !== projectNameNext){
                            isReward = 0;
                            continue;
                        }
                    }
                    var iframe_batch_change_status = layer.open({
                        type : 2,
                        title : '批量修改状态',
                        content : "<?php echo U('Member/show_change_status_window');?>&isReward=" + isReward + "&memberId=" + memberId[0] ,
                        area : ['680px','270px'],
                        btn: ['确认修改', '取消']
                        ,yes: function(index)
                        {
                            var card_status = $(".CARDSTATUS", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            var invoice_status = $(".INVOICE_STATUS", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            var receipstatus = $(".RECEIPTSTATUS", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            var subscribetime = $(".SUBSCRIBETIME", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            var signtime = $(".SIGNTIME", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            var isleadtime =$(".LEAD_TIME", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).is(":visible");
                            if(isleadtime) {
                                var lead_time = $(".LEAD_TIME", window.frames["layui-layer-iframe" + iframe_batch_change_status].document).val();
                            }
                            var decoration_standard = $(".DECORATION_STANDARD", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            var property_deal_reward = $("select[name='PROPERTY_DEAL_REWARD']", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            var agency_deal_reward = $("select[name='AGENCY_DEAL_REWARD']", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            var out_reward = $("select[name='OUT_REWARD']", window.frames["layui-layer-iframe"+iframe_batch_change_status].document).val();
                            if(card_status == '' && invoice_status == '' && receipstatus == '' && property_deal_reward == '' && agency_deal_reward == ''
                            && out_reward == '')
                            {
                                layer.alert('至少选择其中一种', {icon: 2});
                                return false;
                            }
                            
                            //已办已认购
                            if(card_status == '2' && subscribetime == '')
                            {
                                layer.alert('办卡状态为已办已认购，认购日期日期必须填写', {icon: 2});
                                return false;
                            }
                            //已办已签约
                            else if(card_status == '3' && (signtime == '' || lead_time=='' || decoration_standard==''))
                            {
                                layer.alert('办卡状态为已办已签约，签约日期、交付时间、装修标准必须填写', {icon: 2});
                                return false;
                            }
                            
                            $.ajax({
                                type: "GET",
                                url: "<?php echo U('Member/batch_change_status');?>",
                                data:{
                                    'memberId':memberId, 
                                    'card_status':card_status, 
                                    'invoice_status':invoice_status, 
                                    'receipstatus': receipstatus,
                                    'subscribetime': subscribetime,
                                    'signtime': signtime,
                                    'lead_time': lead_time,
                                    'decoration_standard': decoration_standard,
                                    'property_deal_reward': property_deal_reward,
                                    'agency_deal_reward': agency_deal_reward ,
                                    'out_reward':out_reward
                                    },
                                dataType:"JSON",
                                success:function(data)
                                {
                                    if(data.state == 0)
                                    {   
                                        //layer.close(index);
                                        layer.alert(data.msg, {icon: 2});
                                    }
                                    else if(data.state == 1)
                                    {   
                                        layer.close(index);
                                        layer.alert(data.msg, {icon: 1},function(){window.location.reload();});
                                    }
                                    else
                                    {
                                        var msg = data.msg ? data.msg : '操作异常';
                                        layer.alert(msg, {icon: 2});
                                    }
                                }
                            })
                        }
                        ,cancel: function(index){ layer.close(index);} 
                    });
                })


				//申请退房
				$("#pro_refund").click(function(){
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
						data:{'memberId':memberId,'refund_method':'mid','re_type':'pro','STATUS':4},
						dataType:"JSON",
						success:function(data){
							if(data.state == 0)
							{
								layer.alert(data.msg, {icon: 2});
							}
							else if(data.state == 1)
							{
								//layer.alert(data.msg, {icon: 1});
								//window.location.reload();
								//layer.close(index);
								layer.alert(data.msg, {icon: 1},function(){window.location.reload();});
							}
							else
							{
								var msg = data.msg ? data.msg : '操作异常';
								layer.alert(msg, {icon: 2});
							}
						}
					});

				});
				//申请节佣
				$("#pro_post_commission").click(function(){
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
						url: "<?php echo U('Member/post_commission');?>",
						data:{'memberId':memberId,'refund_method':'mid','STATUS':2},
						dataType:"JSON",
						success:function(data){
							if(data.state == 0)
							{
								layer.alert(data.msg, {icon: 2});
							}
							else if(data.state == 1)
							{
								//layer.alert(data.msg, {icon: 1});
								//window.location.reload();
								layer.alert(data.msg, {icon: 1},function(){window.location.reload();});
							}
							else
							{
								var msg = data.msg ? data.msg : '操作异常';
								layer.alert(msg, {icon: 2});
							}
						}
					});

				});
                
                //导出会员                
                $("#download_member").click(function(){
                    layer.confirm(
                            '确定导出会员吗?', 
                            {icon: 3, title:'导出会员'}, 
                            function(index){
                                layer.close(index);
                                location.href = "<?php echo U('Member/export_member');?>&Filter_Sql=" + filter_sql + "&Sort_Sql=" + sort_sql+'&case_type=<?php echo ($case_type); ?>';
                            }
                    );
                })
                
                //导出会员                
                $("#import_member").click(function(){
                    var url = "<?php echo U('Member/import_member');?>";
                    url = url + '/showForm/3';
					url = url + '/case_type/'+case_type;
                    layer.open({
                        type : 2,
                        title : '会员导入',
                        content : url,
                        area : ['80%', '80%'],
                        cancel: function(index){ layer.close(index);} 
                    });
                })

                //锁定解锁
                $("#lock_member,#unlock_member").click(function(){
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
                        layer.alert('请至少选择一条记录!', {icon: 0});
                        return false;
                    }

                    var type = ($(this)).attr('data-id');
                    $.ajax({
                        type: "GET",
                        url: "<?php echo U('Member/lock_unlock');?>&type=" + type,
                        data:{'memberId':memberId},
                        dataType:"JSON",
                        success:function(data){
                            if(data.state == 0)
                            {
                                layer.alert(data.msg, {icon: 2});
                            }
                            else if(data.state == 1)
                            {
                                layer.alert(data.msg, {icon: 1},function(){window.location.reload();});
                            }
                            else
                            {
                                var msg = data.msg ? data.msg : '操作异常';
                                layer.alert(msg, {icon: 2});
                            }
                        }
                    });

                })
                /***申请查看完整信息***/
                $("#view_memberinfo").click(function()
                {   
                    //退款申请单明细
                    var memberId = new Array();
                    var i = 0;
                    $("input[name='checkedtd']:checkbox").each(function() 
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
                    
                    var url = "<?php echo U('Member/apply_view_memberinfo');?>";
                    url = url + '/memberid/'+memberId;
                    layer.open({
                        type : 2,
                        title : '申请查看会员信息【记录日志】',
                        content : url,
                        area : ['60%', '60%'],
                        cancel: function(index){ layer.close(index);} 
                    });
                })
                
                //根据号码获取会员来源
                $(".MOBILENO").blur(function(){
                    var prjid = $.trim($('#PRJ_ID').val());
                    var pro_listid = $('#LIST_ID').val();
                    var telno = $.trim($('.MOBILENO').val());
                    var action_type = 'ajax_userinfo_by_telno';
                    
                    if( prjid == 0 || telno.length != 11 ) 
                    {   
                        return false;
                    }

                    $.ajax({
                        url: "index.php?s=/Member/get_minfo_by_telno",
                        type: "POST",
                        dataType: "JSON",
                        data: {'action_type':action_type, 'project_id':prjid, 'pro_listid':pro_listid, 'telno':telno},
                        success: function(data) 
                        {   
                            if(data.result == 1)
                            {   
                                //只在CRM系统中匹配到用户信息
                                //已认证直接作为数据来源，未认证的作为数据来源并且到场确认
                                $('#is_from').val(data.is_from_crm);
                                //设置用户姓名
                                $('.realname').val(data.crm_user.truename);
                                //设置客户来源
                                $('.source').val(data.crm_user.usersource);
                                //设置验证码
                                $('#code').val(data.crm_user.code);
                                //客户ID
                                $('#customer_id').val(data.crm_user.customer_id);

                                if(data.crm_user.confirm_status == 0 && data.crm_user.confirm_status != null)
                                {   
                                    //设置CRM到场确认
                                    set_crm_cofirm();
                                    cancle_fgj_cofirm();
                                }
                                else
                                {   
                                    //取消CRM到场确认
                                    cancle_crm_cofirm();
                                }
                            }
                            else if(data.result == 2)
                            {   
                                //客源只在FGJ系统中匹配到用户信息
                                $('#is_from').val(data.is_from_fgj);

                                //是否还需要到场确认
                                if(data.is_need_confirm_fgj == 1)
                                {   
                                    var count_code_num = 0;
                                    //多个用户需要验证，则取验证码不为空的数据
                                    for(var i = 0; i < data.user_num_fgj; i++)
                                    {   
                                        if(data.fgj_user[i].code != null && data.fgj_user[i].code != '')
                                        {
                                            //设置用户姓名
                                            $('.realname').val(data.fgj_user[i].truename);
                                            //设置客户来源
                                            $('.source').val(data.fgj_user[i].usersource);
                                            //客户ID
                                            $('#customer_id').val(data.fgj_user[i].customer_id);
                                            //设置验证码
                                            $('#code').val(data.fgj_user[i].code);
                                            //经纪人id
                                            $('#ag_id').val(data.fgj_user[i].ag_id);
                                            //报备id
                                            $('#cp_id').val(data.fgj_user[i].cp_id);
                                            count_code_num ++;
                                            break;
                                        }
                                    }

                                    if(count_code_num > 0)
                                    {
                                        //$('.popup_comfirm').show();
                                        show_fgj_confirm();
                                        if(data.user_num_fgj > 1)
                                        {
                                            //多个用户需要验证，跳转到到场确认页面
                                            $('#multi_user_to_jump').val('jump');
                                        }
                                        else
                                        {
                                            $('#multi_user_to_jump').val('no_jump');
                                        }
                                    }
                                    else
                                    {   
                                        //取消房管家到场确认
                                        cancle_fgj_cofirm();
                                        $('#source').val(14);
                                    }
                                }
                                else
                                {   
                                    //取消房管家到场确认
                                    cancle_fgj_cofirm();
                                    //已经有验证过的用户则取验证过的用户作为数据来源
                                    for(i = 0; i < data.user_num_fgj; i++)
                                    {
                                        if(data.fgj_user[i].confirm_status == 0)
                                        {
                                            //设置用户姓名
                                            $('.realname').val(data.fgj_user[i].truename);
                                            //设置客户来源
                                            $('.source').val(data.fgj_user[i].usersource);
                                            //客户ID
                                            $('#customer_id').val(data.fgj_user[i].customer_id);
                                            //设置验证码
                                            $('#code').val(data.fgj_user[i].code);
                                            //经纪人id
                                            $('#ag_id').val(data.fgj_user[i].ag_id);
                                            //报备id
                                            $('#cp_id').val(data.fgj_user[i].cp_id);
                                            break;
                                        }
                                    }
                                }
                            }
                            else if(data.result == 3)
                            {   
                                //CRM和FGJ都有匹配到的信息

                                //判断CRM验证码是否为空
                                var crm_code_empty = false;
                                if(data.crm_user.code == '' || data.crm_user.code == null)
                                {
                                    crm_code_empty = true;
                                }

                                //判断FGJ验证码是否为空
                                var fgj_code_empty = true;
                                for(i = 0; i < data.user_num_fgj; i++)
                                {
                                    if(data.fgj_user[i].code != '' && data.fgj_user[i].code != null )
                                    {
                                        fgj_code_empty = false;
                                    }
                                }

                                //都没确认提醒是否需要跳转
                                if(data.crm_user.confirm_status == 0 && data.is_need_confirm_fgj == 1)
                                {
                                    show_all_confirm();
                                    $('#multi_from_to_jump').val('jump');
                                }
                                else if(data.crm_user.confirm_status == 1 && data.is_need_confirm_fgj == 1)
                                {   
                                    //CRM确认FGJ没确认直接去CRM数据填充
                                    $('#is_from').val(data.is_from_crm);
                                    //设置用户姓名
                                    $('.realname').val(data.crm_user.truename);
                                    //设置客户来源
                                    $('.source').val(data.crm_user.usersource);
                                    //设置验证码
                                    $('#code').val(data.crm_user.code);
                                    //客户ID
                                    $('#customer_id').val(data.crm_user.customer_id);

                                    //取消CRM到场确认
                                    cancle_crm_cofirm();
                                }
                                else if(data.crm_user.confirm_status == 0 && data.is_need_confirm_fgj == 0)
                                {
                                    //FGJ确认CRM没确认,直接取FGJ数据填充
                                    for(i = 0; i < data.user_num_fgj ; i++)
                                    {
                                        if(data.fgj_user[i].confirm_status == 0)
                                        {
                                            //设置用户姓名
                                            $('.realname').val(data.fgj_user[i].truename);
                                            //设置客户来源
                                            $('.source').val(data.fgj_user[i].usersource);
                                            //客户ID
                                            $('#customer_id').val(data.fgj_user[i].customer_id);
                                            //设置验证码
                                            $('#code').val(data.fgj_user[i].code);
                                            //经纪人id
                                            $('#ag_id').val(data.fgj_user[i].ag_id);
                                            //报备id
                                            $('#cp_id').val(data.fgj_user[i].cp_id);
                                            break;
                                        }
                                    }
                                }
                                else if(data.crm_user.confirm_status == 2)
                                {   
                                    //客源只在FGJ系统中匹配到用户信息
                                    $('#is_from').val(data.is_from_fgj);

                                    //是否还需要到场确认
                                    if(data.is_need_confirm_fgj == 1)
                                    {   
                                        var count_code_num = 0;
                                        //多个用户需要验证，则取验证码不为空的数据
                                        for(var i = 0; i < data.user_num_fgj; i++)
                                        {   
                                            if(data.fgj_user[i].code != null && data.fgj_user[i].code != '')
                                            {
                                                //设置用户姓名
                                                $('.realname').val(data.fgj_user[i].truename);
                                                //设置客户来源
                                                $('.source').val(data.fgj_user[i].usersource);
                                                //客户ID
                                                $('#customer_id').val(data.fgj_user[i].customer_id);
                                                //设置验证码
                                                $('#code').val(data.fgj_user[i].code);
                                                //经纪人id
                                                $('#ag_id').val(data.fgj_user[i].ag_id);
                                                //报备id
                                                $('#cp_id').val(data.fgj_user[i].cp_id);
                                                count_code_num ++;
                                                break;
                                            }
                                        }

                                        if(count_code_num > 0)
                                        {
                                            show_fgj_confirm();
                                            if(data.user_num_fgj > 1)
                                            {
                                                //多个用户需要验证，跳转到到场确认页面
                                                $('#multi_user_to_jump').val('jump');
                                            }
                                            else
                                            {
                                                $('#multi_user_to_jump').val('no_jump');
                                            }
                                        }
                                        else
                                        {   
                                            //取消房管家到场确认
                                            cancle_fgj_cofirm();
                                            $('#source').val(14);
                                        }
                                    }
                                    else
                                    {   
                                        //取消房管家到场确认
                                        cancle_fgj_cofirm();

                                        var fgj_confrimed_num = 0;
                                        //FGJ确认CRM没确认,直接取FGJ数据填充
                                        for(i = 0; i < data.user_num_fgj; i++)
                                        {
                                            if(data.fgj_user[i].confirm_status == 0)
                                            {
                                                //设置用户姓名
                                                $('.realname').val(data.fgj_user[i].truename);
                                                //设置客户来源
                                                $('.source').val(data.fgj_user[i].usersource);
                                                //客户ID
                                                $('#customer_id').val(data.fgj_user[i].customer_id);
                                                //设置验证码
                                                $('#code').val(data.fgj_user[i].code);
                                                //经纪人id
                                                $('#ag_id').val(data.fgj_user[i].ag_id);
                                                //报备id
                                                $('#cp_id').val(data.fgj_user[i].cp_id);
                                                fgj_confrimed_num ++;
                                                break;
                                            }
                                        }

                                        //房管家没有已到场确认用户，则取CRM数据
                                        if(fgj_confrimed_num == 0)
                                        {   
                                            $('#is_from').val(data.is_from_crm);
                                            //设置用户姓名
                                            $('.realname').val(data.crm_user.truename);
                                            //设置客户来源
                                            $('.source').val(data.crm_user.usersource);

                                            //取消CRM到场确认
                                            cancle_crm_cofirm();
                                        }
                                    }
                                }
                            }
                            else if (data.result == 0)
                            {
                                //如果不是两个系统的客户则作为自然到场客户处理
                                $('#ag_id').val(0);
                                $('#cp_id').val(0);
                                cancle_fgj_cofirm();
                                cancle_crm_cofirm();
                                set_free_customer();
                            }
                            else
                            {
                                //异常错误
                                var msg = data.msg ? data.msg : '操作异常';
                                layer.alert(msg, {icon: 2});
                            }
                        }           
                    });
                });
        
                //设置需要房管家到场确认
                function set_fgj_cofirm()
                {
                    $('#is_fgj_confirm').val(1);
                }

                //取消需要房管家到场确认
                function cancle_fgj_cofirm()
                {
                    $('#is_fgj_confirm').val(0);
                }

                //存在到需要到场确认数据，但选择不要房管家到场确认
                function cancle_fgj_confirm_by_user()
                {
                    cancle_fgj_cofirm();
                    set_free_customer();
                }

                //设置需要CRM到场确认
                function set_crm_cofirm()
                {
                    $('#is_crm_confirm').val(1);
                }

                //取消需要CRM到场确认
                function cancle_crm_cofirm()
                {
                    $('#is_crm_confirm').val(0);
                }

                //确认需要房管家到场确认
                function fgj_arrival_cofirm()
                {
                    set_fgj_cofirm();

                    var multi_user_to_jump = $('#multi_user_to_jump').val();
                    if(multi_user_to_jump == 'jump')
                    {
                        //跳转到确认页面
                        var confirm_url = "<?php echo U('Member/arrivalConfirm');?>";
                        window.location = confirm_url;
                    }
                }

                //取消到场确认
                function cancle_cofirm()
                {
                    set_free_customer();
                }

                //设置自然来客
                function set_free_customer()
                {
                    $('.source').val(14);
                }

                //确认因为多系统有数据需要到到场确认页面进行到场确认操作
                function jump_arrival_cofirm()
                {   
                    var multi_from_to_jump = $('#multi_from_to_jump').val();
                    if(multi_from_to_jump == 'jump')
                    {
                        //跳转到确认页面
                        var confirm_url = "<?php echo U('Member/arrivalConfirm');?>";
                        window.location = confirm_url;
                    }
                }
                
                //显示房管家到场确认提醒
                function show_fgj_confirm()
                {
                    layer.confirm
                    (
                        '&nbsp;房管家报备客户，未完成到场确认，是否确认到场?', 
                        {
                            icon: 3,
                            title: '房管家到场确认',
                            shadeClose: false,
                            closeBtn: false,
                            shade: 0.8,
                            btn: ['确 定', '取 消'],
                            area: ['400px', '150px']
                        },
                        function(index)
                        {   
                            fgj_arrival_cofirm();
                            layer.close(index);
                        },
                        function (index)
                        {   
                            cancle_fgj_confirm_by_user();
                            layer.close(index);
                        }
                    );
                }
                
                //显示房管家和CRM到场确认提醒
                function show_all_confirm()
                {
                    layer.confirm
                    (
                        '&nbsp;此用户在CRM和房管家均未完成到场确认，是否确认到场?', 
                        {
                            icon: 3,
                            title: '到场确认',
                            shadeClose: false,
                            closeBtn: false,
                            shade: 0.8,
                            btn: ['确 定', '取 消'],
                            area: ['400px', '180px']
                        },
                        function(index)
                        {   
                            jump_arrival_cofirm();
                            layer.close(index);
                        },
                        function (index)
                        {   
                            cancle_cofirm();
                            layer.close(index);
                        }
                    );
                }
            });
            
            //申请减免流程
            function discount(){
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
                    type: "POST",
                    url: "<?php echo U('MemberDiscount/add_member_discount_detail');?>",
                    data:{'memberId':memberId},
                    dataType:"JSON",
                    success:function(data){
                        if(data.state == 0)
                        {
                           layer.alert(data.msg, {icon: 2});
                        }
                        else if(data.state == 1)
                        {
                            layer.alert(data.msg, {icon: 1});
                            //var url = "<?php echo U('MemberDiscount/show_discount_detail',$paramUrl);?>";
                            //url = url + "/mid_str/"+data.mid_str+"/list_id/"+data.list_id;
                           // window.location.href = url;
                        }
                        else if(data.state == 2)
                        {
                            layer.alert(data.msg, {icon: 0});                            
                        }
                        else
                        {
                            var msg = data.msg ? data.msg : '操作异常';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                 })   
            }
            
            //申请退票流程
            function recycle_invoice(){
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
                    url:"<?php echo U('InvoiceRecycle/apply_invoice_recycle',$paramUrl);?>",
                    type:"post",
                    data:{'memberid':memberId},
                    dataType:"JSON",
                    success:function(data){
                        if(data.status == 0){
                            layer.alert(data.msg,{icon:2});
                            return ;
                        }else if(data.status == 1){
                            layer.alert(data.msg,{icon:1});
                            return ;
                        }else{
                            var msg = data.msg ? data.msg : '操作异常';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                })
            }

            //保存当前设置
            function save_cfg(){
                if(!confirm("亲，您确定要保存当前信息吗？"))
                    return false;
				var case_type = '<?php echo ($case_type); ?>';
				if(case_type=='fx'){
					var url = "<?php echo U('Member/DisRegMember',$paramUrl);?>";
				}else{
					var url = "<?php echo U('Member/RegMember',$paramUrl);?>";
				}
                $.ajax({
                    url:url,
                    type:"POST",
                    data: {
                        'act': 'savecfg',
                        'formdata': $('.registerform').serialize()
                    },
                    dataType: "JSON",
                    success:function(data){
                        if(data.status){
                            layer.alert(data.msg,{icon:1});
                            return ;
                        }else{
                            layer.alert('对不起，保存失败!',{icon:2});
                            return ;
                        }
                    },
                    error:function(data){
                        alert("操作异常，请重试~~")
                    },
                });
            }

            /**
             * 申请
             * @returns {boolean}
             */
            function recycle_invoice(){
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
                    url:"<?php echo U('InvoiceRecycle/apply_invoice_recycle',$paramUrl);?>",
                    type:"post",
                    data:{'memberid':memberId},
                    dataType:"JSON",
                    success:function(data){
                        if(data.status == 0){
                            layer.alert(data.msg,{icon:2});
                            return ;
                        }else if(data.status == 1){
                            layer.alert(data.msg,{icon:1});
                            return ;
                        }else{
                            var msg = data.msg ? data.msg : '操作异常';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                })
            }

            //申请换发票
            function change_invoice()
            {
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
                    layer.alert('请选择一条记录!', {icon: 2});
                    return false;
                }
                if( i > 1)
                {
                    layer.alert('每次只能选择一条记录!', {icon: 2});
                    return false;
                }
                
                //判断发票状态是否为已开未领或已开已领
                var id = memberId[0];
                var invoice_status = $("select[name='"+id+"_INVOICE_STATUS']").val();
                //alert(invoice_status);
                if(invoice_status != 2 && invoice_status != 3)
                {
                    layer.alert("申请失败，发票状态为已开未领或者已开已领的会员才能申请换票",{icon:2});
                    return false;
                }
               
                $.ajax({
                    url:"<?php echo U('ChangeInvoice/apply_change_invoice',$paramUrl);?>",
                    type:"post",
                    data:{'memberid':memberId},
                    dataType:"JSON",
                    success:function(data){
                        if(data.state == 0){
                            layer.alert(data.msg,{icon:2});
                            return ;
                        }else if(data.state == 1){
                            layer.alert(data.msg,{icon:1});
                            return ;
                        }else if(data.state == 2){
                            var url = "<?php echo U('ChangeInvoice/change_invoice_manage&memberid="+id+"');?>";
                            layer.alert(data.msg,{icon:0},function(index){                               
                                window.location.href = url; 
                                layer.close(index);
                            });                            
                            return ;
                            
                        }else{
                            var msg = data.msg ? data.msg : '操作异常';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                })
            }
        </script>
        <textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // 附带上上次搜索的结果
    });
</script>
    </body>
</html>