<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>采购明细</title>
    <meta charset="GBK">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="text/javascript" src="./Public/js/template.js"></script>
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
		.select2-dropdown{ z-index:999999999!important;
		}
    </style>
    <script>
        var appUrl;
    </script>
</head>
<body>
<div class="containter">
    <?php echo ($form); ?>  
    <!--相关中介佣金信息-->
    <?php if(count($total_pro) > 0): ?><div class="containter before-registerform">
            <div class="contractinfo-table">
                <table style="width: auto;">
                    <thead>
                    <tr>
                        <th style="width: 300px">项目名称</th>
                        <th style="width: 200px;">总条数</th>
                        <th style="width: 200px;">总额</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($total_pro)): $i = 0; $__LIST__ = $total_pro;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$statItem): $mod = ($i % 2 );++$i;?><tr>
                            <td><?php echo ($statItem['PRJ_NAME']); ?></td>
                            <td><?php echo ($statItem['AGENCY_COUNT']); ?></td>
                            <td><?php echo ($statItem['AGENCY_REWARD']); ?></td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div><?php endif; ?>
    <textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
</div>
 
<!--
    *1 =>    '采购',
    *2 =>    '预算外其他费用',
    *3 =>    '电商会员中介佣金',
    *4 =>    '电商会员中介成交奖励',
    *5 =>    '电商会员置业顾问佣金',
    *6 =>    '电商会员置业顾问成交奖励',
    *7 =>    '现金带看奖',
    *8 =>    '带看奖',
    *9 =>    '分销会员中介佣金',
    *10 =>   '分销会员中介成交奖励',
    *11 =>   '分销会员置业顾问佣金',
    *12 =>   '分销会员置业顾问成交奖励',
    *14 =>   '大宗采购',
    *15 =>   '小蜜蜂采购',
    *16 =>   '支付第三方费用'
    *17 =>   '分销后佣中介佣金'
    *21 =>   '电商外部成交奖励'
    *22 =>   '分销会员中介成交奖励'
    *23 =>   '分销会员置业顾问成交奖励'
    *24 =>   '分销会员外部成交奖励'
    *25 =>   '分销会员外部成交奖励前佣'
 
-->

<script id="test" type="text/html">  
    <div class="contractinfo-table">
            <table>
                <thead>
                    <tr>
                        <td>编号</td>
                        <td>项目名称</td>
                        <td>
                            {{if (reimType == 1 || reimType == 14 )}}供应商
                            {{else if (reimType == 3 || reimType == 4 || reimType == 5  ||reimType == 6 || reimType == 21)}}会员姓名
                            {{else if (reimType == 7)}}发放数量
                            {{else if (reimType == 16)}}合同号
                            {{else if (reimType == 17)}}会员编号
                            {{else if (reimType == 2)}}申请金额
                            {{else if (reimType == 15)}}报销类型
                            {{else if (reimType == 9 || reimType == 10 || reimType == 11 || reimType == 12 || reimType == 25)}}会员姓名
                            {{else if (reimType == 21 || reimType == 22 || reimType == 23 || reimType == 24)}}客户姓名
                            {{/if}}
                        </td>
                        <td>
                            {{if (reimType == 1 || reimType == 14 )}}品名
                            {{else if (reimType == 3 || reimType == 4 || reimType == 5 || reimType == 6 || reimType == 21)}} 房号
                            {{else if (reimType == 7)}}发放现金
                            {{else if (reimType == 16)}}申请金额
                            {{else if (reimType == 17)}}会员姓名
                            {{else if (reimType == 2)}}申请人
                            {{else if (reimType == 15)}}报销状态
                            {{else if (reimType == 9 || reimType == 10 || reimType == 11 || reimType == 12 || reimType == 25 )}}房号
                            {{else if (reimType == 21 || reimType == 22 || reimType == 23 || reimType == 24)}}房号
                            {{/if}}
                        </td>
                        <td>
                            {{if (reimType == 1 || reimType == 14 || reimType == 3 || reimType == 4 || reimType == 5 || reimType == 6 ||reimType == 7 || reimType == 21)}}报销金额
                            {{else if (reimType == 16)}}申请日期
                            {{else if (reimType == 15)}}小蜜蜂采购ID
                            {{else if (reimType == 17 || reimType == 2)}}报销状态
                            {{else if (reimType == 9 || reimType == 10 || reimType == 11 || reimType == 12  || reimType == 25)}}报销金额
                            {{else if (reimType == 21 || reimType == 22 || reimType == 23 || reimType == 24 )}}后佣收费标准
                            {{/if}}
                        </td>
                        <td>进项税</td>
                        
                    </tr>
                </thead>
                <tbody>
                    
                    {{each list as value i}} 
                    <tr>                  
                        <td>{{value.ID}}</td>
                        <td>{{value.PRJNAME}}</td>
                        <td>{{value.REALNAME}}</td>
                        <td>{{value.ROOMNO}}</td>
                        <td>{{value.MONEY}}</td>
                        <td><input type="text"  name="{{value.ID}}_INPUT_TAX" value="{{value.INPUTTAX}}"></td>                  
                    </tr>
                    {{/each}}
               
                </tbody>
            </table>
                
    </div>
</script>
<script id="test2" type="text/html">  
    <div class="contractinfo-table">
            <table>
                <thead>
                    <tr>
                        <td>编号</td>
                        <td>项目名称</td>
                        <td>
                           费用类别
                        </td>
                        <td>
                           金额
                        </td>
                        <td>
                            是否扣非
                        </td>
                        <td>部门</td>
						 {{if (reimType == 1 || reimType == 14 )}}
						<td>NC费用类型</td>
						{{/if}}

                        
                    </tr>
                </thead>
                <tbody>
                    
                    {{each list as value i}} 
                    <tr>                  
                        <td>{{value.ID}}</td>
						 <td>{{value.PRJNAME}}</td>
                        <td><select name="FEEID" id="FEEID_{{value.ID}}"></select></td>
                        <td> <input name="money" id="MONEY_{{value.ID}}" value="{{value.MONEY}}" /></td>
                        <td> <select name="ISKF" id="ISKF_{{value.ID}}"></select></td>
                        <td> <select name="DEPTID" id="DEPTID_{{value.ID}}"></select></td>
						 {{if (reimType == 1 || reimType == 14 )}}
                        <td><select name="NCTYPE" id="NCTYPE_{{value.ID}}"></select></td> 
						{{/if}}
                    </tr>
                    {{/each}}
               
                </tbody>
            </table>
                
    </div>
</script>
<script>
    var url;
 
	$(function(){
		$('.J-export-file').click(function(){
				var id = $(this).parent().attr('fid');
				var file = $(this).attr('data-file');
				location.href = "index.php?s=/Purchasing/export_bee_file/reimId/"+id+'/file/'+file;
		});
        
        //点击进项税窗口弹出页面
       /* $('.checkedtd').click(function(){
               var fid = $(this).val(); 
               if($(this).prop("checked")  )
               {
                   //$("input[name="+fid+"_INPUT_TAX]").parent().hide().siblings('span').show().siblings('.info').remove();
					var url2 = '__APP__/Financial/reimDetail/jinxianshui/1/ID/'+fid;
				    layer.open({
                                type: 2,
                                title: '进项税编辑',
                                shadeClose: true,
                                shade: 0.8,
                                area: ['80%', '70%'],
                                content: url2 //iframe的url
                    });
               }                     
 
        });*/

        $("#edit_input_tax").click(function () {
            var reimListType = $("input[name='reimListType']").val();
            //alert(reimListType)
            var i = 0;
            var arr = new Array();
            //电商
            var dataList = new Array();
            var reimDetailId = new Array();
            var prjName = new Array();
            var realName = new Array();
            var roomNo = new Array();
            var money = new Array();
            var inputTax = new Array();
            $('.checkedtd').each(function () {
                if ($(this).prop("checked") == true) {
                    
                    //电商报销类型3,4,5,6,21 分销前佣9,10,11,12，25
                    if(reimListType == 3 || reimListType == 4 || reimListType == 5 || reimListType == 6 || reimListType == 21|| 
                    reimListType == 9 || reimListType == 10 || reimListType == 11 || reimListType == 12 || reimListType == 25){
                        reimDetailId[i] = $(this).val();
                        prjName[i] = $("input[name='"+reimDetailId[i]+"_PRJ_NAME_OLD']").val();
                        realName[i] = $("input[name='"+reimDetailId[i]+"_REALNAME_OLD']").val();
                        roomNo[i] = $("input[name='"+reimDetailId[i]+"_ROOMNO_OLD']").val();
                        money[i] = $("input[name='"+reimDetailId[i]+"_MONEY_OLD']").val();
                        inputTax[i] = $("input[name='"+reimDetailId[i]+"_INPUT_TAX_OLD']").val();
                        
                     //分销后佣报销
                    }else if( reimListType == 22 || reimListType == 23 || reimListType == 24){
                        reimDetailId[i] = $(this).val();
                        prjName[i] = $("input[name='"+reimDetailId[i]+"_PROJECTNAME_OLD']").val();
                        realName[i] = $("input[name='"+reimDetailId[i]+"_REALNAME_OLD']").val();
                        roomNo[i] = $("input[name='"+reimDetailId[i]+"_ROOMNO_OLD']").val();
                        money[i] = $("input[name='"+reimDetailId[i]+"_TOTAL_PRICE_OLD']").prev().prev().html();
                        inputTax[i] = $("input[name='"+reimDetailId[i]+"_INPUT_TAX_OLD']").val();                        
                    }
                    //采购类型1,14
                    else if(reimListType == 1 || reimListType == 14 ){
                        reimDetailId[i] = $(this).val();
                        prjName[i] = $("input[name='"+reimDetailId[i]+"_PROJECTNAME_OLD']").val();
                        realName[i] = $("input[name='"+reimDetailId[i]+"_SUPPLIER_NAME_OLD']").val();
                        roomNo[i] = $("input[name='"+reimDetailId[i]+"_PRODUCT_NAME_OLD']").val();
                        money[i] = $("input[name='"+reimDetailId[i]+"_MONEY_OLD']").val();
                        inputTax[i] = $("input[name='"+reimDetailId[i]+"_INPUT_TAX_OLD']").val();
                    }
                    //支付第三方费用
                    else if(reimListType ==16 ){
                        reimDetailId[i] = $(this).val();
                        prjName[i] = $("input[name='"+reimDetailId[i]+"_PROJECT_NAME_OLD']").val();
                        realName[i] = $("input[name='"+reimDetailId[i]+"_CONTRACT_OLD']").val();
                        roomNo[i] = $("input[name='"+reimDetailId[i]+"_AMOUNT_OLD']").val();
                        money[i] = $("input[name='"+reimDetailId[i]+"_ADDTIME_OLD']").val();
                        inputTax[i] = $("input[name='"+reimDetailId[i]+"_INPUT_TAX_OLD']").val();
                    }
                    //现金带看奖
                    else if(reimListType ==7 ){
                        reimDetailId[i] = $(this).val();
                        prjName[i] = $("input[name='"+reimDetailId[i]+"_PRJ_ID_OLD']").prev().prev().html();
                        realName[i] = $("input[name='"+reimDetailId[i]+"_NUM_OLD']").val();
                        roomNo[i] = $("input[name='"+reimDetailId[i]+"_FF_MONEY_OLD']").val();
                        money[i] = $("input[name='"+reimDetailId[i]+"_MONEY_OLD']").val();
                        inputTax[i] = $("input[name='"+reimDetailId[i]+"_INPUT_TAX_OLD']").val();
                    }
                    
                    //分销会员后佣
                    else if(reimListType ==17 ){
                        var comReimId = new Array();
                        comReimId[i] = $(this).val();
                        reimDetailId[i] = $("input[name='"+comReimId[i]+"_REIM_DETAIL_ID_OLD']").val();
                        prjName[i] = $("input[name='"+comReimId[i]+"_PRJ_NAME_OLD']").val();
                        realName[i] = $("input[name='"+comReimId[i]+"_MEMBER_ID_OLD']").val();
                        roomNo[i] = $("input[name='"+comReimId[i]+"_REALNAME_OLD']").val();
                        money[i] = $("input[name='"+comReimId[i]+"_STATUS_OLD']").prev().prev().html();
                        inputTax[i] = $("input[name='"+comReimId[i]+"_INPUT_TAX_OLD']").val();
                    }
                    //预算外其他费用
                    else if(reimListType ==2 ){
                        reimDetailId[i] = $(this).val();
                        prjName[i] = $("input[name='"+reimDetailId[i]+"_PROJECT_NAME_OLD']").val();
                        realName[i] = $("input[name='"+reimDetailId[i]+"_AMOUNT_OLD']").val();
                        roomNo[i] = $("input[name='"+reimDetailId[i]+"_NAME_OLD']").prev().prev().html();
                        money[i] = $("input[name='"+reimDetailId[i]+"_ISCOST_OLD']").prev().prev().html();
                        inputTax[i] = $("input[name='"+reimDetailId[i]+"_INPUT_TAX_OLD']").val();
                    }
                    //小蜜蜂采购
                    else if(reimListType ==15 ){
                        reimDetailId[i] = $(this).val();
                        prjName[i] = $("input[name='"+reimDetailId[i]+"_PROJECTNAME_OLD']").val();
                        realName[i] = $("input[name='"+reimDetailId[i]+"_TYPE_OLD']").prev().prev().html();
                        roomNo[i] = $("input[name='"+reimDetailId[i]+"_MONEY_OLD']").val();
                        money[i] = $("input[name='"+reimDetailId[i]+"_BUSINESS_ID_OLD']").val();
                        inputTax[i] = $("input[name='"+reimDetailId[i]+"_INPUT_TAX_OLD']").val();
                    }
                    i++;
                }

            });
            if (reimDetailId.length <= 0) {
                layer.alert("请选择要编辑的明细记录", {icon: 0});
                return false;
            }
            else {
                
                for (var j = 0, len = reimDetailId.length; j < len; j++) {
                    dataList = [{"ID":reimDetailId[j],"PRJNAME":prjName[j],"REALNAME":realName[j],"ROOMNO":roomNo[j],"MONEY":money[j],"INPUTTAX":inputTax[j]}];
                    arr = arr.concat(dataList);
                   
                }
                var data = {
                    list: arr ,
                    reimType: reimListType
                };
                // todo                
                layer.open({              
                                type: 1,
                                title: '进项税编辑',
                                btn:['确定','取消'],
                                shadeClose: true,
                                shade: 0.8,
                                area: ['80%', '80%'],
                                content: template('test', data),
                                yes: function(index, layero){
                                    var input_tax = new Array();
                                    for(var n = 0, size = reimDetailId.length ; n < size ; n ++){
                                        inputTax[n] = $("input[name=" + reimDetailId[n] + "_INPUT_TAX]", '.layui-layer-content').val();
                                    }
                                    $.ajax({
                                        type: "post",
                                        url: "<?php echo U('Financial/save_input_tax');?>",
                                        data: {fid:reimDetailId , input_tax: inputTax},
                                        dataType: "JSON",
                                        success: function (data) {
                                            if (data.state == 1) {
                                                layer.alert(data.msg, {icon: 1},
                                                        function (index) {
                                                            window.location.reload();
                                                            layer.close(index);
                                                        });

                                            } else if (data.state == 0) {
                                                layer.alert(data.msg, {icon: 2})
                                            }
                                        }
                                    })
                                }
                });
                
            }
                
            
                /*$('input[name*="_INPUT_TAX"]').css('width', '100px');  // 控制进项税的输入框
                for (var j = 0, len = reimDetailId.length; j < len; j++) {
                    $("input[name=" + reimDetailId[j] + "_INPUT_TAX]").parent().show().siblings('span').hide();
                }
            }*/
        });
		
	
		$("#save_input_reimDetail").click(function () {
            var reimListType = $("input[name='reimListType']").val();
            //alert(reimListType)
            var i = 0;
			var ii = 0;
            var arr = new Array();
            //电商
            var dataList = new Array();
            var reimDetailId = new Array();
			var reimId = new Array();
            var prjName = new Array();
            var realName = new Array();
            var roomNo = new Array();
            var money = new Array();
            var inputTax = new Array();
			var ISKF =  new Array();
			var NCTYPE =  new Array();
			var deptid =  new Array();
			var FEEID =  new Array();
		
			$('.checkedtd').each(function () {
				if ($(this).prop("checked") == true) {
                    
                   reimId[ii] = $(this).val();
				   ii++;
				}

			});
			//alert(ii);
		console.log('prjName',prjName);
		
			$.ajax({
				type: "post",
				url: "<?php echo U('Financial/save_input_reimDetail&action=getReimDtail');?>",
				data: {fid:reimId },
				dataType: "JSON",
				success: function (data) {
					console.log('data',data);
					var j = 0 ;
					for(i in data){ 
						reimDetailId[j] = data[i]['RID'];
						//电商报销类型3,4,5,6,21 分销前佣9,10,11,12，25
						if(reimListType == 3 || reimListType == 4 || reimListType == 5 || reimListType == 6 || reimListType == 21|| 
						reimListType == 9 || reimListType == 10 || reimListType == 11 || reimListType == 12 || reimListType == 25){
							 
							prjName[j] = $("input[name='"+reimDetailId[j]+"_PRJ_NAME_OLD']").val();
						 
							
						 //分销后佣报销
						}else if( reimListType == 22 || reimListType == 23 || reimListType == 24){
						 
							prjName[j] = $("input[name='"+reimDetailId[j]+"_PROJECTNAME_OLD']").val();
						                        
						}
						//采购类型1,14
						else if(reimListType == 1 || reimListType == 14 ){
						 
							prjName[j] = $("input[name='"+reimDetailId[j]+"_PROJECTNAME_OLD']").val();
						 
						}
						//支付第三方费用
						else if(reimListType ==16 ){
							 
							prjName[j] = $("input[name='"+reimDetailId[j]+"_PROJECT_NAME_OLD']").val();
						 
						}
						//现金带看奖
						else if(reimListType ==7 ){
							 
							prjName[j] = $("input[name='"+reimDetailId[j]+"_PRJ_ID_OLD']").prev().prev().html();
							 
						}
						
						//分销会员后佣
						else if(reimListType ==17 ){
							 
							prjName[j] = $("input[name='"+comReimId[j]+"_PRJ_NAME_OLD']").val();
							 
						}
						//预算外其他费用
						else if(reimListType ==2 ){
							 
							prjName[j] = $("input[name='"+reimDetailId[j]+"_PROJECT_NAME_OLD']").val();
							 
						}
						//小蜜蜂采购
						else if(reimListType ==15 ){
							 
							prjName[j] = $("input[name='"+reimDetailId[j]+"_PROJECTNAME_OLD']").val();
							 
						}
						
						//prjName[j] = $("input[name='"+reimDetailId[j]+"_PRJ_NAME_OLD']").val();
						money[j] = data[i]['MONEY'];
						ISKF[j] = data[i]['ISKF'];
						NCTYPE[j] = data[i]['NCTYPE'];
						deptid[j] = data[i]['DEPT_ID'];
						FEEID[j] = data[i]['FEE_ID'];
						 
						
						console.log('data[i][RID]', data[i]['RID']);
						
						j++;
					}

					if (reimDetailId.length <= 0  ) {
					  console.log('reimDetailId.length',reimDetailId.length);
						layer.alert("请选择要编辑的明细记录", {icon: 0});
						return false;
					}else {
						
						for (var j = 0, len = reimDetailId.length; j < len; j++) {
							dataList = [{"ID":reimDetailId[j],"PRJNAME":prjName[j],"ISKF":ISKF[j],"NCTYPE":NCTYPE[j],"MONEY":money[j],"DEPT_ID":deptid[j],"FEEID":FEEID[j]}];
							arr = arr.concat(dataList);
						   
						}
						console.log('dataList',dataList);
						var data = {
							list: arr ,
							reimType: reimListType
						};
						
						// todo                
						layer.open({              
										type: 1,
										title: '报销明细编辑',
										btn:['确定','取消'],
										shadeClose: true,
										shade: 0.8,
										area: ['80%', '80%'],
										content: template('test2', data),
										yes: function(index, layero){
											var DEPTID = new Array();
											var FEEID = new Array();
											var NCTYPE = new Array();
											var ISKF = new Array();
											var MONEY = new Array();
											 
											for(var n = 0, size = reimDetailId.length ; n < size ; n ++){
												DEPTID[n] = $("#DEPTID_" + reimDetailId[n], '.layui-layer-content').val();
												FEEID[n] = $("#FEEID_" + reimDetailId[n], '.layui-layer-content').val();
												NCTYPE[n] = $("#NCTYPE_" + reimDetailId[n], '.layui-layer-content').val();
												ISKF[n] = $("#ISKF_" + reimDetailId[n]).val();
												MONEY[n] = $("#MONEY_" + reimDetailId[n]).val();
											}
											$.ajax({
												type: "post",
												url: "<?php echo U('Financial/save_input_reimDetail');?>",
												data: {fid:reimDetailId,DEPTID:DEPTID,FEEID:FEEID,NCTYPE:NCTYPE,ISKF:ISKF,MONEY:MONEY},
												dataType: "JSON",
												success: function (data) {
													if (data.state == 1) {
														layer.alert(data.msg, {icon: 1},
																function (index) {
																	window.location.reload();
																	layer.close(index);
																});

													} else if (data.state == 0) {
														layer.alert(data.msg, {icon: 2})
													}
												}
											})
										}
						});
						var deptOptions = '<?php echo ($deptOptions); ?>';
						var feeOptions= '<?php echo ($feeOptions); ?>';
						var iskfOptions = '<option value="1">是</option><option value="0">否</option>';
						var nctyeOptions = '<?php echo ($nctyeOptions); ?>'
						for (var j = 0, len = reimDetailId.length; j < len; j++) {

							
							if (deptOptions) {
								$('#DEPTID_'+reimDetailId[j]+'')
										.html(deptOptions)
										.addClass('js-example-basic-single')
										.val(deptid[j])
										.unbind('focus')
										.select2({
											allowClear: true,
											noResults: '没有找到相关信息'
										});

								// 权限选择

							}
							if (feeOptions) {
								$('#FEEID_'+reimDetailId[j]+'')
										.html(feeOptions)
										.addClass('js-example-basic-single')
										.val(FEEID[j])
										.unbind('focus')
										.select2({
											allowClear: true,
											noResults: '没有找到相关信息'
										});

								// 权限选择

							}
							if (nctyeOptions) {
								$('#NCTYPE_'+reimDetailId[j]+'')
										.html(nctyeOptions)
										.addClass('js-example-basic-single')
										.val(NCTYPE[j])
										.unbind('focus')
										.select2({
											allowClear: true,
											noResults: '没有找到相关信息'
										});

								// 权限选择

							}
							$('#ISKF_'+reimDetailId[j]+'').html(iskfOptions).val(ISKF[j]);
						}

						
					}
					 
 
				}
			});
			
			//console.log('money',money);
			
			//console.log('money.length',money.length);	
            /*$('.checkedtd').each(function () {
                if ($(this).prop("checked") == true) {
                    
                    reimDetailId[i] = $(this).val();
					prjName[i] = $("input[name='"+reimDetailId[i]+"_PRJ_NAME_OLD']").val();
					realName[i] = $("input[name='"+reimDetailId[i]+"_REALNAME_OLD']").val();
					roomNo[i] = $("input[name='"+reimDetailId[i]+"_ROOMNO_OLD']").val();
					money[i] = $("input[name='"+reimDetailId[i]+"_MONEY_OLD']").val();
					inputTax[i] = $("input[name='"+reimDetailId[i]+"_INPUT_TAX_OLD']").val();
                    i++;
                }

            });*/
			//console.log('reimDetailId', reimDetailId);
            
                
            
                /*$('input[name*="_INPUT_TAX"]').css('width', '100px');  // 控制进项税的输入框
                for (var j = 0, len = reimDetailId.length; j < len; j++) {
                    $("input[name=" + reimDetailId[j] + "_INPUT_TAX]").parent().show().siblings('span').hide();
                }
            }*/
        });


    });

    $("#save_input_tax").click(function () {
        var fid = new Array();
        var input_tax = new Array();
        var i = 0;
        $('.checkedtd').each(function () {
            if ($(this).prop("checked") == true) {
                fid[i] = $(this).val();
                input_tax[i] = $("input[name=" + fid[i] + "_INPUT_TAX]").val();
                i++;
            }
        });

        if (fid.length == 0 || input_tax.length == 0) {
            layer.alert("非法操作，请先对进项税进行编辑！", {icon: 2});
            return false;
        }
        $.ajax({
            type: "post",
            url: "<?php echo U('Financial/save_input_tax');?>",
            data: {fid: fid, input_tax: input_tax},
            dataType: "JSON",
            success: function (data) {
                if (data.state == 1) {
                    layer.alert(data.msg, {icon: 1},
                            function (index) {
                                window.location.reload();
                                layer.close(index);
                            });

                } else if (data.state == 0) {
                    layer.alert(data.msg, {icon: 2})
                }

            }
        })
    })
</script>
<input type="hidden" name="reimListType" value="<?php echo ($reim_list_type); ?>">
</body>
</html>