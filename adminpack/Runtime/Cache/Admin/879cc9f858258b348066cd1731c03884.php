<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>�쿨��Ա</title>
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

        <!--��������汾autocomplete��ͻ-->
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

//                $('html').niceScroll();  // ����������
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
								//�жϷ��������Ƿ�Ϊ�գ���Ϊ�շ������ݡ�
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
                            
                            //�����շѱ�׼
							if(case_type=='ds') set_price_standard_select(project_id);
							if(case_type=='fx') set_price_standard_select_fx(project_id);
                            //�����·�¥�̱��
                            set_pro_list_id(project_id);
                            //���û�Ա��Դ
                            get_user_source(project_id);
                            removeinput = 2;
							//����ǰ��Ӷ�����
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
                            //����շѱ�׼�����б�
                            cancle_price_standard_select();
                            //����·�¥��INPUT
                            cancle_pro_list_id();
                            //��ջ�Ա��Դ�����б�
                            cancle_user_source();
                        }
                    }
                });
                //ֱ����Ա
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
                //�ж��շѱ�׼ �����������ʾ
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
				//��ȡ�շѱ�׼
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
                                    var output = [];//�����շѱ�׼
									var output_after = [];//�����շѱ�׼
                                    var output_a_reward = [];//�н�Ӷ��
									var output_a_reward_after = [];//�н�Ӷ��
									var output_a_deal_reward = [];//�н�ɽ���
                                    var output_p_reward = [];//��ҵ����Ӷ��
                                    var output_p_deal_reward = [];//��ҵ���ʳɽ���
                                   
 
                                    $.each(data, function(key, value)
                                    {  
                                        var dw = value['STYPE']==1  ? '%':'Ԫ';
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
                        layer.alert('��Ŀ��Ϣ�쳣!', {icon: 2});
                        return false;
                    }
                }
                
                //��ȡ�շѱ�׼
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
                                    layer.alert('��Ŀ����ʱδ��д�����շѱ�׼������Ŀ�����˼�ʱ���������д�����շѱ�׼', {icon: 2});
                                    cancle_price_standard_select();
                                }
                                else if(data[0]['ID'] >= 1)
                                {   
                                    var output = [];//�����շѱ�׼
                                    var output_a_reward = [];//�н�Ӷ��
                                    var output_a_deal_reward = [];//�н�ɽ���
                                    var output_p_reward = [];//��ҵ����Ӷ��
                                    var output_p_deal_reward = [];//��ҵ���ʳɽ���

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
                                    layer.alert('��Ŀ����ʱδ��д�����շѱ�׼������Ŀ�����˼�ʱ���������д�����շѱ�׼', {icon: 2});
                                    cancle_price_standard_select();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_price_standard_select();
                        layer.alert('��Ŀ��Ϣ�쳣!', {icon: 2});
                        return false;
                    }
                }
                
                //ȡ���շѱ�׼�����б�
                function cancle_price_standard_select()
                {   
                    var option_str = '<option value="">��ѡ��</option>';
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
                
                //��ȡ��Ա��Դ
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
                                    layer.alert('��ĿĿ��ֽ⣬û����д���۷�ʽ���޷����ӻ�Ա��', {icon: 2});
                                    cancle_user_source();
                                }
                                else if(data[0]['id'] > 0)
                                {   
                                    var user_source = [];//��Ա��Դ
                                    $.each(data, function(key, value)
                                    {   
                                        user_source.push('<option value="'+ value['id'] +'">'+ value['name'] +'</option>');
                                    });
                                    
                                    cancle_user_source();
                                    $('.source').append(user_source.join(''));
                                }
                                else
                                {
                                    var msg = data.msg ? data.msg : '�����쳣';
                                    layer.alert(msg, {icon: 2});
                                    cancle_user_source();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_user_source();
                        layer.alert('��Ŀ��Ϣ�쳣!', {icon: 2});
                        return false;
                    }
                }
                
                //ȡ����Ա��Դ�����б�
                function cancle_user_source()
                {
                    var option_str = '<option value="">��ѡ��</option>';
                    $('.source').empty();
                    $('.source').html(option_str);
                }
                
                //��ȡ�·�¥�̱��
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
                                    layer.alert('��Ŀ����ʱδ���·�¥����Ϣ��Ϊ��Ӱ�쵽��ȷ�ϲ���������Ŀ�����˼�ʱ�����Ŀ¥����Ϣ��', {icon: 2});
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
                                            layer.alert('��Ŀ����ʱδ���·�¥����Ϣ��Ϊ��Ӱ�쵽��ȷ�ϲ���������Ŀ�����˼�ʱ�����Ŀ¥����Ϣ��', {icon: 2});
                                            cancle_pro_list_id();
                                	}
                                }
                                else
                                {
                                    var msg = data.msg ? data.msg : '�����쳣';
                                    layer.alert(msg, {icon: 2});
                                    cancle_pro_list_id();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_pro_list_id();
                        layer.alert('��Ŀ��Ϣ�쳣!', {icon: 2});
                        return false;
                    }
                }
                
                //ȡ���·�¥�̱���ֶ�
                function cancle_pro_list_id()
                {
                    $('#LIST_ID').remove();
                }
                
                //���뿪Ʊ
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
                        layer.alert('������ѡ��һ����¼!', {icon: 2});
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
                                var msg = data.msg ? data.msg : '�����쳣';
                                layer.alert(msg, {icon: 2},
                                    function(){window.location.reload();});
                            }
                        }
                     })   
                })
                
                //�н���ñ�������
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
                        layer.alert('������ѡ��һ����¼!', {icon: 2});
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
                                var msg = data.msg ? data.msg : '�����쳣';
                                layer.alert(msg, {icon: 2, closeBtn: false},
                                  function(){window.location.reload();});
                            }
                        }
                     })   
                })
                
                //ͨ����Ա�����˿�
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
                        layer.alert('������ѡ��һ����¼!', {icon: 2});
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
                                var msg = data.msg ? data.msg : '�����쳣';
                                layer.alert(msg, {icon: 2});
                            }
                        }
                     })   
                })

                //����ת����Ŀ
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
                        layer.alert('������ѡ��һ����¼!', {icon: 2});
                        return false;
                    }

                    //��֤����
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
                            layer.alert('�����쳣������ϵ����Ա',{icon: 2});
                        }
                    });

                    function showWindow() {
                        var iframeMoveProject = layer.open({
                            type: 2,
                            title: 'ת����Ŀ',
                            content: "<?php echo U('Member/moveProject');?>&showWindow=1&memberIds=" + memberId,
                            area: ['680px', '300px'],
                            btn: ['ȷ��ת��', 'ȡ��']
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
                                        alert('�����������ϵ����Ա��');
                                    },
                                })
                            }
                            , cancel: function (index) {
                                layer.close(index);
                            }
                        });
                    }

                });

                //�����޸�״̬
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
                        layer.alert('������ѡ��һ����¼!', {icon: 2});
                        return false;
                    }

                    //�ж���Ŀ�����Ƿ�ͬ
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
                        title : '�����޸�״̬',
                        content : "<?php echo U('Member/show_change_status_window');?>&isReward=" + isReward + "&memberId=" + memberId[0] ,
                        area : ['680px','270px'],
                        btn: ['ȷ���޸�', 'ȡ��']
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
                                layer.alert('����ѡ������һ��', {icon: 2});
                                return false;
                            }
                            
                            //�Ѱ����Ϲ�
                            if(card_status == '2' && subscribetime == '')
                            {
                                layer.alert('�쿨״̬Ϊ�Ѱ����Ϲ����Ϲ��������ڱ�����д', {icon: 2});
                                return false;
                            }
                            //�Ѱ���ǩԼ
                            else if(card_status == '3' && (signtime == '' || lead_time=='' || decoration_standard==''))
                            {
                                layer.alert('�쿨״̬Ϊ�Ѱ���ǩԼ��ǩԼ���ڡ�����ʱ�䡢װ�ޱ�׼������д', {icon: 2});
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
                                        var msg = data.msg ? data.msg : '�����쳣';
                                        layer.alert(msg, {icon: 2});
                                    }
                                }
                            })
                        }
                        ,cancel: function(index){ layer.close(index);} 
                    });
                })


				//�����˷�
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
						layer.alert('������ѡ��һ����¼!', {icon: 2});
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
								var msg = data.msg ? data.msg : '�����쳣';
								layer.alert(msg, {icon: 2});
							}
						}
					});

				});
				//�����Ӷ
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
						layer.alert('������ѡ��һ����¼!', {icon: 2});
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
								var msg = data.msg ? data.msg : '�����쳣';
								layer.alert(msg, {icon: 2});
							}
						}
					});

				});
                
                //������Ա                
                $("#download_member").click(function(){
                    layer.confirm(
                            'ȷ��������Ա��?', 
                            {icon: 3, title:'������Ա'}, 
                            function(index){
                                layer.close(index);
                                location.href = "<?php echo U('Member/export_member');?>&Filter_Sql=" + filter_sql + "&Sort_Sql=" + sort_sql+'&case_type=<?php echo ($case_type); ?>';
                            }
                    );
                })
                
                //������Ա                
                $("#import_member").click(function(){
                    var url = "<?php echo U('Member/import_member');?>";
                    url = url + '/showForm/3';
					url = url + '/case_type/'+case_type;
                    layer.open({
                        type : 2,
                        title : '��Ա����',
                        content : url,
                        area : ['80%', '80%'],
                        cancel: function(index){ layer.close(index);} 
                    });
                })

                //��������
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
                        layer.alert('������ѡ��һ����¼!', {icon: 0});
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
                                var msg = data.msg ? data.msg : '�����쳣';
                                layer.alert(msg, {icon: 2});
                            }
                        }
                    });

                })
                /***����鿴������Ϣ***/
                $("#view_memberinfo").click(function()
                {   
                    //�˿����뵥��ϸ
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
                        layer.alert('������ѡ��һ����¼!', {icon: 2});
                        return false;
                    }
                    
                    var url = "<?php echo U('Member/apply_view_memberinfo');?>";
                    url = url + '/memberid/'+memberId;
                    layer.open({
                        type : 2,
                        title : '����鿴��Ա��Ϣ����¼��־��',
                        content : url,
                        area : ['60%', '60%'],
                        cancel: function(index){ layer.close(index);} 
                    });
                })
                
                //���ݺ����ȡ��Ա��Դ
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
                                //ֻ��CRMϵͳ��ƥ�䵽�û���Ϣ
                                //����ֱ֤����Ϊ������Դ��δ��֤����Ϊ������Դ���ҵ���ȷ��
                                $('#is_from').val(data.is_from_crm);
                                //�����û�����
                                $('.realname').val(data.crm_user.truename);
                                //���ÿͻ���Դ
                                $('.source').val(data.crm_user.usersource);
                                //������֤��
                                $('#code').val(data.crm_user.code);
                                //�ͻ�ID
                                $('#customer_id').val(data.crm_user.customer_id);

                                if(data.crm_user.confirm_status == 0 && data.crm_user.confirm_status != null)
                                {   
                                    //����CRM����ȷ��
                                    set_crm_cofirm();
                                    cancle_fgj_cofirm();
                                }
                                else
                                {   
                                    //ȡ��CRM����ȷ��
                                    cancle_crm_cofirm();
                                }
                            }
                            else if(data.result == 2)
                            {   
                                //��Դֻ��FGJϵͳ��ƥ�䵽�û���Ϣ
                                $('#is_from').val(data.is_from_fgj);

                                //�Ƿ���Ҫ����ȷ��
                                if(data.is_need_confirm_fgj == 1)
                                {   
                                    var count_code_num = 0;
                                    //����û���Ҫ��֤����ȡ��֤�벻Ϊ�յ�����
                                    for(var i = 0; i < data.user_num_fgj; i++)
                                    {   
                                        if(data.fgj_user[i].code != null && data.fgj_user[i].code != '')
                                        {
                                            //�����û�����
                                            $('.realname').val(data.fgj_user[i].truename);
                                            //���ÿͻ���Դ
                                            $('.source').val(data.fgj_user[i].usersource);
                                            //�ͻ�ID
                                            $('#customer_id').val(data.fgj_user[i].customer_id);
                                            //������֤��
                                            $('#code').val(data.fgj_user[i].code);
                                            //������id
                                            $('#ag_id').val(data.fgj_user[i].ag_id);
                                            //����id
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
                                            //����û���Ҫ��֤����ת������ȷ��ҳ��
                                            $('#multi_user_to_jump').val('jump');
                                        }
                                        else
                                        {
                                            $('#multi_user_to_jump').val('no_jump');
                                        }
                                    }
                                    else
                                    {   
                                        //ȡ�����ܼҵ���ȷ��
                                        cancle_fgj_cofirm();
                                        $('#source').val(14);
                                    }
                                }
                                else
                                {   
                                    //ȡ�����ܼҵ���ȷ��
                                    cancle_fgj_cofirm();
                                    //�Ѿ�����֤�����û���ȡ��֤�����û���Ϊ������Դ
                                    for(i = 0; i < data.user_num_fgj; i++)
                                    {
                                        if(data.fgj_user[i].confirm_status == 0)
                                        {
                                            //�����û�����
                                            $('.realname').val(data.fgj_user[i].truename);
                                            //���ÿͻ���Դ
                                            $('.source').val(data.fgj_user[i].usersource);
                                            //�ͻ�ID
                                            $('#customer_id').val(data.fgj_user[i].customer_id);
                                            //������֤��
                                            $('#code').val(data.fgj_user[i].code);
                                            //������id
                                            $('#ag_id').val(data.fgj_user[i].ag_id);
                                            //����id
                                            $('#cp_id').val(data.fgj_user[i].cp_id);
                                            break;
                                        }
                                    }
                                }
                            }
                            else if(data.result == 3)
                            {   
                                //CRM��FGJ����ƥ�䵽����Ϣ

                                //�ж�CRM��֤���Ƿ�Ϊ��
                                var crm_code_empty = false;
                                if(data.crm_user.code == '' || data.crm_user.code == null)
                                {
                                    crm_code_empty = true;
                                }

                                //�ж�FGJ��֤���Ƿ�Ϊ��
                                var fgj_code_empty = true;
                                for(i = 0; i < data.user_num_fgj; i++)
                                {
                                    if(data.fgj_user[i].code != '' && data.fgj_user[i].code != null )
                                    {
                                        fgj_code_empty = false;
                                    }
                                }

                                //��ûȷ�������Ƿ���Ҫ��ת
                                if(data.crm_user.confirm_status == 0 && data.is_need_confirm_fgj == 1)
                                {
                                    show_all_confirm();
                                    $('#multi_from_to_jump').val('jump');
                                }
                                else if(data.crm_user.confirm_status == 1 && data.is_need_confirm_fgj == 1)
                                {   
                                    //CRMȷ��FGJûȷ��ֱ��ȥCRM�������
                                    $('#is_from').val(data.is_from_crm);
                                    //�����û�����
                                    $('.realname').val(data.crm_user.truename);
                                    //���ÿͻ���Դ
                                    $('.source').val(data.crm_user.usersource);
                                    //������֤��
                                    $('#code').val(data.crm_user.code);
                                    //�ͻ�ID
                                    $('#customer_id').val(data.crm_user.customer_id);

                                    //ȡ��CRM����ȷ��
                                    cancle_crm_cofirm();
                                }
                                else if(data.crm_user.confirm_status == 0 && data.is_need_confirm_fgj == 0)
                                {
                                    //FGJȷ��CRMûȷ��,ֱ��ȡFGJ�������
                                    for(i = 0; i < data.user_num_fgj ; i++)
                                    {
                                        if(data.fgj_user[i].confirm_status == 0)
                                        {
                                            //�����û�����
                                            $('.realname').val(data.fgj_user[i].truename);
                                            //���ÿͻ���Դ
                                            $('.source').val(data.fgj_user[i].usersource);
                                            //�ͻ�ID
                                            $('#customer_id').val(data.fgj_user[i].customer_id);
                                            //������֤��
                                            $('#code').val(data.fgj_user[i].code);
                                            //������id
                                            $('#ag_id').val(data.fgj_user[i].ag_id);
                                            //����id
                                            $('#cp_id').val(data.fgj_user[i].cp_id);
                                            break;
                                        }
                                    }
                                }
                                else if(data.crm_user.confirm_status == 2)
                                {   
                                    //��Դֻ��FGJϵͳ��ƥ�䵽�û���Ϣ
                                    $('#is_from').val(data.is_from_fgj);

                                    //�Ƿ���Ҫ����ȷ��
                                    if(data.is_need_confirm_fgj == 1)
                                    {   
                                        var count_code_num = 0;
                                        //����û���Ҫ��֤����ȡ��֤�벻Ϊ�յ�����
                                        for(var i = 0; i < data.user_num_fgj; i++)
                                        {   
                                            if(data.fgj_user[i].code != null && data.fgj_user[i].code != '')
                                            {
                                                //�����û�����
                                                $('.realname').val(data.fgj_user[i].truename);
                                                //���ÿͻ���Դ
                                                $('.source').val(data.fgj_user[i].usersource);
                                                //�ͻ�ID
                                                $('#customer_id').val(data.fgj_user[i].customer_id);
                                                //������֤��
                                                $('#code').val(data.fgj_user[i].code);
                                                //������id
                                                $('#ag_id').val(data.fgj_user[i].ag_id);
                                                //����id
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
                                                //����û���Ҫ��֤����ת������ȷ��ҳ��
                                                $('#multi_user_to_jump').val('jump');
                                            }
                                            else
                                            {
                                                $('#multi_user_to_jump').val('no_jump');
                                            }
                                        }
                                        else
                                        {   
                                            //ȡ�����ܼҵ���ȷ��
                                            cancle_fgj_cofirm();
                                            $('#source').val(14);
                                        }
                                    }
                                    else
                                    {   
                                        //ȡ�����ܼҵ���ȷ��
                                        cancle_fgj_cofirm();

                                        var fgj_confrimed_num = 0;
                                        //FGJȷ��CRMûȷ��,ֱ��ȡFGJ�������
                                        for(i = 0; i < data.user_num_fgj; i++)
                                        {
                                            if(data.fgj_user[i].confirm_status == 0)
                                            {
                                                //�����û�����
                                                $('.realname').val(data.fgj_user[i].truename);
                                                //���ÿͻ���Դ
                                                $('.source').val(data.fgj_user[i].usersource);
                                                //�ͻ�ID
                                                $('#customer_id').val(data.fgj_user[i].customer_id);
                                                //������֤��
                                                $('#code').val(data.fgj_user[i].code);
                                                //������id
                                                $('#ag_id').val(data.fgj_user[i].ag_id);
                                                //����id
                                                $('#cp_id').val(data.fgj_user[i].cp_id);
                                                fgj_confrimed_num ++;
                                                break;
                                            }
                                        }

                                        //���ܼ�û���ѵ���ȷ���û�����ȡCRM����
                                        if(fgj_confrimed_num == 0)
                                        {   
                                            $('#is_from').val(data.is_from_crm);
                                            //�����û�����
                                            $('.realname').val(data.crm_user.truename);
                                            //���ÿͻ���Դ
                                            $('.source').val(data.crm_user.usersource);

                                            //ȡ��CRM����ȷ��
                                            cancle_crm_cofirm();
                                        }
                                    }
                                }
                            }
                            else if (data.result == 0)
                            {
                                //�����������ϵͳ�Ŀͻ�����Ϊ��Ȼ�����ͻ�����
                                $('#ag_id').val(0);
                                $('#cp_id').val(0);
                                cancle_fgj_cofirm();
                                cancle_crm_cofirm();
                                set_free_customer();
                            }
                            else
                            {
                                //�쳣����
                                var msg = data.msg ? data.msg : '�����쳣';
                                layer.alert(msg, {icon: 2});
                            }
                        }           
                    });
                });
        
                //������Ҫ���ܼҵ���ȷ��
                function set_fgj_cofirm()
                {
                    $('#is_fgj_confirm').val(1);
                }

                //ȡ����Ҫ���ܼҵ���ȷ��
                function cancle_fgj_cofirm()
                {
                    $('#is_fgj_confirm').val(0);
                }

                //���ڵ���Ҫ����ȷ�����ݣ���ѡ��Ҫ���ܼҵ���ȷ��
                function cancle_fgj_confirm_by_user()
                {
                    cancle_fgj_cofirm();
                    set_free_customer();
                }

                //������ҪCRM����ȷ��
                function set_crm_cofirm()
                {
                    $('#is_crm_confirm').val(1);
                }

                //ȡ����ҪCRM����ȷ��
                function cancle_crm_cofirm()
                {
                    $('#is_crm_confirm').val(0);
                }

                //ȷ����Ҫ���ܼҵ���ȷ��
                function fgj_arrival_cofirm()
                {
                    set_fgj_cofirm();

                    var multi_user_to_jump = $('#multi_user_to_jump').val();
                    if(multi_user_to_jump == 'jump')
                    {
                        //��ת��ȷ��ҳ��
                        var confirm_url = "<?php echo U('Member/arrivalConfirm');?>";
                        window.location = confirm_url;
                    }
                }

                //ȡ������ȷ��
                function cancle_cofirm()
                {
                    set_free_customer();
                }

                //������Ȼ����
                function set_free_customer()
                {
                    $('.source').val(14);
                }

                //ȷ����Ϊ��ϵͳ��������Ҫ������ȷ��ҳ����е���ȷ�ϲ���
                function jump_arrival_cofirm()
                {   
                    var multi_from_to_jump = $('#multi_from_to_jump').val();
                    if(multi_from_to_jump == 'jump')
                    {
                        //��ת��ȷ��ҳ��
                        var confirm_url = "<?php echo U('Member/arrivalConfirm');?>";
                        window.location = confirm_url;
                    }
                }
                
                //��ʾ���ܼҵ���ȷ������
                function show_fgj_confirm()
                {
                    layer.confirm
                    (
                        '&nbsp;���ܼұ����ͻ���δ��ɵ���ȷ�ϣ��Ƿ�ȷ�ϵ���?', 
                        {
                            icon: 3,
                            title: '���ܼҵ���ȷ��',
                            shadeClose: false,
                            closeBtn: false,
                            shade: 0.8,
                            btn: ['ȷ ��', 'ȡ ��'],
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
                
                //��ʾ���ܼҺ�CRM����ȷ������
                function show_all_confirm()
                {
                    layer.confirm
                    (
                        '&nbsp;���û���CRM�ͷ��ܼҾ�δ��ɵ���ȷ�ϣ��Ƿ�ȷ�ϵ���?', 
                        {
                            icon: 3,
                            title: '����ȷ��',
                            shadeClose: false,
                            closeBtn: false,
                            shade: 0.8,
                            btn: ['ȷ ��', 'ȡ ��'],
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
            
            //�����������
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
                    layer.alert('������ѡ��һ����¼!', {icon: 2});
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
                            var msg = data.msg ? data.msg : '�����쳣';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                 })   
            }
            
            //������Ʊ����
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
                    layer.alert('������ѡ��һ����¼!', {icon: 2});
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
                            var msg = data.msg ? data.msg : '�����쳣';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                })
            }

            //���浱ǰ����
            function save_cfg(){
                if(!confirm("�ף���ȷ��Ҫ���浱ǰ��Ϣ��"))
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
                            layer.alert('�Բ��𣬱���ʧ��!',{icon:2});
                            return ;
                        }
                    },
                    error:function(data){
                        alert("�����쳣��������~~")
                    },
                });
            }

            /**
             * ����
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
                    layer.alert('������ѡ��һ����¼!', {icon: 2});
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
                            var msg = data.msg ? data.msg : '�����쳣';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                })
            }

            //���뻻��Ʊ
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
                    layer.alert('��ѡ��һ����¼!', {icon: 2});
                    return false;
                }
                if( i > 1)
                {
                    layer.alert('ÿ��ֻ��ѡ��һ����¼!', {icon: 2});
                    return false;
                }
                
                //�жϷ�Ʊ״̬�Ƿ�Ϊ�ѿ�δ����ѿ�����
                var id = memberId[0];
                var invoice_status = $("select[name='"+id+"_INVOICE_STATUS']").val();
                //alert(invoice_status);
                if(invoice_status != 2 && invoice_status != 3)
                {
                    layer.alert("����ʧ�ܣ���Ʊ״̬Ϊ�ѿ�δ������ѿ�����Ļ�Ա�������뻻Ʊ",{icon:2});
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
                            var msg = data.msg ? data.msg : '�����쳣';
                            layer.alert(msg, {icon: 2});
                        }
                    }
                })
            }
        </script>
        <textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // �������ϴ������Ľ��
    });
</script>
    </body>
</html>