<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>办卡客户</title>
        <meta http-equiv="content-type" content="text/html; charset=gbk"/>
        <meta name="apple-mobile-web-app-capable" content="yes"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,minimum-scale=1,user-scalable=no">
        <meta name="keywords" content="办卡客户">
        <meta name="description" content="办卡客户">
        <link rel="stylesheet" href="./Public/CSS/business.css">
        <link rel="stylesheet" href="./Public/CSS/styles.css"/>
        <script src="./PUBLIC/JS/datejs/jquery-1.9.1.min.js" type="text/javascript"></script>
        <script src="./PUBLIC/JS/valide.js" type="text/javascript"></script>
        <script src="./PUBLIC/JS/common.js" type="text/javascript"></script>
    </head>
    <body>
    <?php if($is_login_from_oa){ ?>
    <div class="returnIndex">
        <a class="returnBtn" href="####" onclick="back_to_oa_app_index()"></a>
        <p class="txt">办卡客户</p>
    </div>
    <?php } ?>

    <div class="wrap">
        <div class="nav">
            <div class="nav_cont clearfix">
                <a class="tab tabfirst" href="<?php echo U('Member/arrivalConfirm');?>">到场确认</a>
                <a class="tab on" href="<?php echo U('Member/RegMember');?>">办卡客户</a>
                <a class="tab" href="<?php echo U('Member/changeStatus');?>">状态变更</a>
                <a class="tab tablast" href="<?php echo U('Member/newMember');?>">自然来客</a>
            </div>
        </div>
        <form method="POST" action="" id="member_form">
            <div class="vail_cont_div clearfix">
                <div class="listDiv">
                    <label class="label_txt" for="PRJID"><em class="c-yel">*</em> 项目名称：</label>
                    <div class="inputDiv">
                        <select name="PRJID" class="demo-test-select input_arri" id="PRJID">
                            <option value="">--请选择--</option>
                            <?php foreach ($projects as $_pro){ ?>
                            <option <?php if($selected_project_id == $_pro['ID'] ) {?>selected<?php } ?> value="<?php echo $_pro['ID']; ?>" data_pro_listid ="<?php echo $_pro['REL_NEWHOUSEID']; ?>" ><?php echo $_pro['PROJECTNAME']; ?></option>
                            <?php } ?>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="MOBILENO"><em class="c-yel">*</em> 手机号码：</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="MOBILENO" name="MOBILENO" placeholder="输入手机号码">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv" >
                    <label class="label_txt longtxtwid" for="LOOKER_MOBILENO">看房者手机号码：</label>
                    <div class="inputDiv shortinputwid">
                        <input class="input_arri"  type="text" id="LOOKER_MOBILENO" name="LOOKER_MOBILENO" placeholder="看房者手机号码">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="REALNAME"><em class="c-yel">*</em> 会员姓名：</label>
                    <div class="inputDiv">
                        <input class="input_arri"  type="text" id="REALNAME" name="REALNAME" placeholder="会员姓名">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="CARDTIME"><em class="c-yel">*</em> 办卡日期：</label>
                    <div class="inputDiv">
                        <input class="input_arri demo-test-date" type="date" id="CARDTIME" name="CARDTIME" value="<?=$today?>">

                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="CERTIFICATE_TYPE"><em class="c-yel">*</em> 证件类型：</label>
                    <div class="inputDiv">
                        <select name="CERTIFICATE_TYPE" class="demo-test-select input_arri" id="CERTIFICATE_TYPE">
                            <?php
 if(is_array($certificate) && !empty($certificate) ) { foreach ($certificate as $key => $value){ ?>
                            <option value="<?=$key?>"><?=$value?></option>
                            <?php
 } } ?>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="IDCARDNO"><em class="c-yel">*</em> 证件号码：</label>
                    <div class="inputDiv">
                        <input class="input_arri"  type="text" id="IDCARDNO" name="IDCARDNO" placeholder="证件号码">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv" style="border:none;text-align:right;display:none" id = 'GET_IDCARDNO'>
                    <a class="getAccessionCode" onclick="openIdCard()">拍照获取身份证号</a>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="SOURCE"><em class="c-yel">*</em> 会员来源：</label>
                    <div class="inputDiv">
                        <select name="SOURCE" id="SOURCE" class="demo-test-select input_arri">
                            <option value="">--请选择--</option>
                            <?php if(!empty($member_source) && is_array($member_source)){?>
                            <?php foreach($member_source as $key=>$val){ ?>
                                <option value="<?=$key?>"><?=$val?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="TOTAL_PRICE"><em class="c-yel">*</em> 单套收费标准：</label>
                    <div class="inputDiv shortinputwid">
                        <select class="demo-test-select input_arri" name="TOTAL_PRICE" id="TOTAL_PRICE">
                            <option value="">--请选择--</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="AGENCY_REWARD"> 中介佣金：</label>
                    <div class="inputDiv shortinputwid">
                        <select class="demo-test-select input_arri" name="AGENCY_REWARD" id="AGENCY_REWARD">
                            <option value="">--请选择--</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="AGENCY_DEAL_REWARD"> 中介成交奖励：</label>
                    <div class="inputDiv shortinputwid">
                        <select class="demo-test-select input_arri" name="AGENCY_DEAL_REWARD" id="AGENCY_DEAL_REWARD">
                            <option value="">--请选择--</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="PROPERTY_DEAL_REWARD"> 置业顾问成交奖励：</label>
                    <div class="inputDiv shortinputwid">
                        <select class="demo-test-select input_arri" name="PROPERTY_DEAL_REWARD" id="PROPERTY_DEAL_REWARD">
                            <option value="">--请选择--</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="HOUSEAREA"> 房型面积：</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="HOUSEAREA" name="HOUSEAREA" placeholder="房型面积">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="HOUSETOTAL"> 房屋总价：</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="HOUSETOTAL" name="HOUSETOTAL" placeholder="房屋总价">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="ISTAKE">是否带看：</label>
                    <div class="inputDiv">
                        <select id="ISTAKE" class="demo-test-select input_arri" name="ISTAKE">
                            <option value="2">否</option>
                            <option value="1">是</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="ROOMNO">楼栋楼号：</label>
                    <div class="inputDiv">
                        <input class="input_arri"   type="text" id="ROOMNO" name="ROOMNO" placeholder="？栋？单元？室">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="CARDSTATUS"><em class="c-yel">*</em> 办卡状态：</label>
                    <div class="inputDiv">
                        <select id="CARDSTATUS" class="demo-test-select input_arri" name="CARDSTATUS">
                            <?php foreach ($card_status as $key => $_val){ ?>
                            <option value="<?=$key?>"><?=$_val?></option>
                            <?php } ?>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>

                <div class="listDiv" data-type="cardstatus_2" style="display:none;">
                    <label class="label_txt" for="SUBSCRIBETIME">认购时间：</label>
                    <div class="inputDiv">
                        <input class="input_arri demo-test-date" type="date" id="SUBSCRIBETIME" name="SUBSCRIBETIME" value="<?=$today?>">
                        <i class="drapjiantou"></i>
                    </div>
                </div>

                <div class="listDiv" data-type="cardstatus_3" style="display:none;">
                    <label class="label_txt" for="SIGNTIME"><em class="c-yel">*</em> 签约日期：</label>
                    <div class="inputDiv">
                        <input class="input_arri demo-test-date" type="date" id="SIGNTIME" name="SIGNTIME" value="<?=$today?>">
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv" data-type="cardstatus_3" style="display:none;">
                    <label class="label_txt" for="SIGNEDSUITE">签约套数：</label>
                    <div class="inputDiv">
                        <select class="input_arri demo-test-select" name="SIGNEDSUITE" id="SIGNEDSUITE">
                            <option value="0">--请选择--</option>
                            <option value="1">1套</option>
                            <option value="2">2套</option>
                            <option value="3">3套</option>
                            <option value="4">4套</option>
                            <option value="5">5套</option>
                            <option value="6">6套</option>
                            <option value="7">7套</option>
                            <option value="8">8套</option>
                            <option value="9">9套</option>
                            <option value="10">10套</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="RECEIPTSTATUS"><em class="c-yel">*</em> 收据状态：</label>
                    <div class="inputDiv">
                        <select id="RECEIPTSTATUS" class="demo-test-select input_arri" name="RECEIPTSTATUS">
                            <?php foreach($receipt_status as $key => $_val){ ?>
                            <option value="<?=$key?>"><?=$_val?></option>
                            <?php } ?>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="RECEIPTNO"><em class="c-yel">*</em> 收据编号：</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="RECEIPTNO" name="RECEIPTNO" placeholder="收据编号">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="INVOICESTATUS"><em class="c-yel">*</em> 发票状态：</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="INVOICESTATUS" name="INVOICESTATUS" value="未开"   disabled>
                        <i class="drapjiantou"></i>
                    </div>
                </div>

                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="IS_SMS">是否发送短信：</label>
                    <div class="inputDiv shortinputwid">
                        <select id="IS_SMS" class="demo-test-select input_arri" name="IS_SMS">
                            <option value="1">不发送</option>
                            <option value="2">发送</option>

                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="OPERATOR">经办人：</label>
                    <div class="inputDiv">
                        <input class="input_arri"   type="text" id="OPERATOR" name="OPERATOR" placeholder="" value="<?=$_SESSION['uinfo']['tname']?>">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="NOTE">备注：</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="NOTE" name="NOTE" placeholder="备注">
                        <i class="sanjiao"></i>
                    </div>
                </div>

                <!--付款明细添加-->
                <div class="pay_cont">
                    <div class="pay_title"></div>
                    <div class="dataplaylist">
                        <div class="smalltitle">
                            <span class="name">| 付款明细</span>
                            <i  class="sanjiao"></i>
                        </div>
                        <div class="detail_info_cont">
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="PAYTYPE[]"><em class="c-yel">*</em>付款方式：</label>
                                <div class="inputDiv shortinputwid">
                                    <div class="inputDiv">
                                        <select class="demo-test-select input_arri" name="PAYTYPE[]">
                                            <option value="">--请选择--</option>
                                            <option value="1">POS机</option>
                                            <option value="2">网银</option>
                                            <option value="3">现金</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="RETRIEVAL[]"><em class="c-yel">*</em>6位检索号：</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri" type="text" name="RETRIEVAL[]" placeholder="6位检索号">
                                </div>
                            </div>
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="CVV2[]"><em class="c-yel">*</em>卡号后四位：</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri"   type="text" name="CVV2[]" placeholder="卡号后四位">
                                </div>
                            </div>
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="TRADETIME[]"><em class="c-yel">*</em>原始交易时间：</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri demo-test-date" type="date" name="TRADETIME[]" value="<?=$today?>">
                                </div>
                            </div>
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="TRADEMONEY[]"><em class="c-yel">*</em>原始交易金额：</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri" type="text" name="TRADEMONEY[]" placeholder="原始交易金额">
                                </div>
                            </div>
                            <?php if(!empty($merchant_arr)){ ?>
                            <div class="payinput">
                                <label class="label_txt" for="MERCHANTNUMBER[]">
                                    <em class="c-yel">*</em>
                                    商户编号：
                                </label>
                                <div class="inputDiv">
                                    <select id="MERCHANTNUMBER" name="MERCHANTNUMBER[]" class="demo-test-select input_arri">
                                        <option value="">--请选择--</option>
                                        <?php foreach($merchant_arr as $key=>$val){ ?>
                                            <option value="<?php echo ($key); ?>"><?php echo ($val); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <i class="drapjiantou"></i>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="pay_cont_click">
                    <a href="javascript:void(0);" class="getAccessionCode payadd">新增一条付款</a>
                    <a href="javascript:void(0);" class="getAccessionCode paydel">删除一条付款</a>
                </div>
            </div>
            <div class="confirmDiv">
                <input type="hidden" name="ADD_ID" value="<?=$adduid?>" />
                <input type="hidden" name="cardstatus_old" value=""/>
                <input type="hidden" name="is_fgj_confirm" id="is_fgj_confirm" value="0">
                <input type="hidden" name="is_crm_confirm" id="is_crm_confirm" value="0">
                <input type="hidden" name="code" id="code" value="">
                <input type ="hidden" id = "ag_id" name = "ag_id" value="">
                <input type ="hidden" id = "cp_id" name = "cp_id" value="">
                <input type ="hidden" id = "is_from" name = "is_from" value="">
                <input type ="hidden" id = "customer_id" name = "customer_id" value="">
                <input type ="hidden" id = "multi_user_to_jump" name = "multi_user_to_jump" value="">
                <input type ="hidden" id = "multi_from_to_jump" name = "multi_from_to_jump" value="">
                <button class="comfirmbtn" type="button">确认</button>
            </div>
        </form>
    </div>

    <!--是否需要到场确认弹窗-->
    <div class="popup_comfirm" style="display: none;">
        <div class="popup_shadow"></div>
        <div class="popup_content">
            <p class="title">房管家报备客户，未完成到场确认，是否确认到场?</p>
            <div class="btngroup">
                <a class="btn first" onclick="cancle_fgj_confirm_by_user()">否</a><a class="btn" onclick="fgj_arrival_cofirm()">是</a>
            </div>
        </div>
    </div>

    <!--是否需要到场确认弹窗-->
    <div class="all_popup_comfirm" style="display: none;">
        <div class="popup_shadow"></div>
        <div class="popup_content">
            <p class="title">此用户在CRM和房管家均未完成到场确认，是否确认到场?</p>
            <div class="btngroup">
                <a class="btn first" onclick="cancle_cofirm()">否</a><a class="btn" onclick="jump_arrival_cofirm()">是</a>
            </div>
        </div>
    </div>

    <script type="text/javascript">
            $(function(){
                //预加载操作
                $(document).ready(function () {
                    //项目加载
                    $("#PRJID").change();
                });

                //苹果设备隐藏身份证号码拍照功能
                if(is_android_device())
                {
                    $('#GET_IDCARDNO').show();
                }

                //证件类型切换
                $("#certificate_type").change(function(){
                    if($("#certificate_type option:selected").val() != 1)
                    {
                        $("#GET_IDCARDNO").attr("style","display:none");
                    }
                    else
                    {
                        if(is_android_device())
                        {
                            $("#GET_IDCARDNO").attr("style","display:block;border:none");
                        }
                    }
                });


                //新增付款明细
                $(".payadd").click(function(){
                    var obj = $(".dataplaylist").last();
                    $(".pay_cont").append(obj.clone());
                });

                //删除付款明细
                $(".paydel").click(function(){
                    var len = $(".dataplaylist").length;
                    if(len==1){
                        alert("对不起，当前只有一条付款明细!");
                        return false;
                    }
                    var obj = $(".dataplaylist").last();
                    obj.remove();
                });

                //处理不同付款类型的表单的变化
                $("select[name='PAYTYPE[]']").live("change",function(){
                    var typeid = $(this).val();
                    var payinput = $(this).parent().parent().parent().siblings();

                    //pos机付款方式
                    if(typeid==1){
                        payinput.each(function(){
                            $(this).show();
                        });
                    }
                    //现金和网银
                    else if(typeid==2 || typeid==3){
                        payinput.each(function(i){
                            //隐藏6位检索号、卡号后四位、商户编号    三个INPUT框
                            if(i==0 || i==1 || i==4) {
                                $(this).hide();
                            }
                        });
                    }
                });

                //根据不同的办卡状态填写不同的时间
                $("#CARDSTATUS").change(function(){

                    //办卡状态值
                    var cardstatus = $(this).val();

                    //已办已认购
                    if(cardstatus == 2)
                    {
                        $("div[data-type='cardstatus_2']").show();
                    }
                    else
                    {
                        $("div[data-type='cardstatus_2']").hide();
                    }

                    //已办已签约
                    if(cardstatus == 3)
                    {
                        $("div[data-type='cardstatus_3']").show();
                    }
                    else
                    {
                        $("div[data-type='cardstatus_3']").hide();
                    }

                });

                //更改项目时根据项目来填充用户来源和单套收费标准
                $("#PRJID").change(function(){
                    var project_id = $("#PRJID").val();
                    if(project_id > 0)
                    {
                        //设置项目名称
                        set_pro_name();
                        //设置收费标准
                        set_price_standard_select(project_id);
                        //设置新房楼盘编号
                        set_pro_list_id(project_id);
                    }
                });

                //配置项目名称
                function set_pro_name(){
                    //先移除在赋值
                    $("#PRJ_NAME").remove();
                    var PRJ_NAME = $("#PRJID").find("option:selected").text();
                    var PRJ_NAME_INPUT = "<input type='hidden' name='PRJ_NAME' id='PRJ_NAME' value="+ PRJ_NAME +">";
                    $('#PRJID').after(PRJ_NAME_INPUT);
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
                            url: "index.php?s=/Project/ajax_get_feescale",
                            data:{'prj_id':prj_id, 'case_type': case_type, 'scale_type':scale_type },
                            dataType:"JSON",
                            success:function(data)
                            {
                                if(data[0]['ID'] == 0)
                                {
                                    alert('项目单套收费标准未填写');
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
                                    $('#TOTAL_PRICE').append(output.join(''));
                                    $('#AGENCY_REWARD').append(output_a_reward.join(''));
                                    $('#AGENCY_DEAL_REWARD').append(output_a_deal_reward.join(''));
                                    $('#PROPERTY_REWARD').append(output_p_reward.join(''));
                                    $('#PROPERTY_DEAL_REWARD').append(output_p_deal_reward.join(''));
                                }
                                else
                                {
                                    alert('项目单套收费标准异常');
                                    cancle_price_standard_select();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_price_standard_select();
                        alert('项目信息异常!');
                        return false;
                    }
                }
                
                //取消收费标准下拉列表
                function cancle_price_standard_select()
                {
                    var option_str = '<option value="">--请选择--</option>';
                    $('#TOTAL_PRICE').empty();
                    $('#TOTAL_PRICE').html(option_str);
                    $('#AGENCY_REWARD').empty();
                    $('#AGENCY_REWARD').html(option_str);
                    $('#AGENCY_DEAL_REWARD').empty();
                    $('#AGENCY_DEAL_REWARD').html(option_str);
                    $('#PROPERTY_REWARD').empty();
                    $('#PROPERTY_REWARD').html(option_str);
                    $('#PROPERTY_DEAL_REWARD').empty();
                    $('#PROPERTY_DEAL_REWARD').html(option_str);
                }

                //打开安卓身份证扫描
                function openIdCard()
                {
                    window.house365js.scanIDCard();
                }

                //安卓回调函数 身份证号、姓名
                function idCardCallback(idcardno,realname)
                {
                    var card_index = 1;
                    $('#certificate_type').val(card_index);

                    $("#idcardno").val(idcardno);
                    $("#realname").val(realname);
                }
                
                //获取新房楼盘编号
                function set_pro_list_id(prj_id)
                {
                    if( prj_id > 0 )
                    {   
                    	var scale_type = '';
                        $.ajax({
                            type: "GET",
                            url: "index.php?s=/Project/ajax_get_houseinfo_by_pid",
                            data:{'project_id':prj_id},
                            dataType:"JSON",
                            success:function(data)
                            {
                                console.log(data);
                                if(data == null || data['ID'] == 0)
                                {
                                    alert('项目未绑定新房楼盘信息，无法进行到场确认！');
                                    cancle_pro_list_id();
                                }
                                else if(data['ID'] >= 1)
                                {   
                                	var list_id = data['REL_NEWHOUSEID'];
                                	if(list_id > 0)
                                	{
                                        //先取消原先的楼盘ID
                                        cancle_pro_list_id();
                                    	var str_input_list_id = "<input type='hidden' name='LIST_ID' id='LIST_ID' value="+ list_id +">";
                                    	$('#PRJID').after(str_input_list_id);
                                	}
                                	else
                                	{
                                        alert('项目未绑定新房楼盘信息，无法进行到场确认！');
                                        cancle_pro_list_id();
                                	}
                                }
                                else
                                {
                                    alert('操作异常');
                                    cancle_pro_list_id();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_pro_list_id();
                        alert('项目信息异常!');
                        return false;
                    }
                }
                
                //取消新房楼盘编号字段
                function cancle_pro_list_id()
                {
                    $('#LIST_ID').remove();
                }
                

                //根据号码获取会员来源
                $("#MOBILENO").blur(function(){
                    var prjid = $.trim($('#PRJID').val());
                    var pro_listid = $('#LIST_ID').val();
                    var telno = $.trim($('#MOBILENO').val());
                    var action_type = 'ajax_userinfo_by_telno';
                    
                    if( prjid == 0 || telno.length != 11 ) 
                    {   
                        return false;
                    }

                    //根据手机号获取信息
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
                                //设置用户姓名
                                $('#REALNAME').val(data.crm_user.truename);
                                //设置客户来源
                                $('#SOURCE').val(data.crm_user.usersource);
                                //已认证直接作为数据来源，未认证的作为数据来源并且到场确认
                                $('#is_from').val(data.is_from_crm);
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
                                            $('#REALNAME').val(data.fgj_user[i].truename);
                                            //设置客户来源
                                            $('#SOURCE').val(data.fgj_user[i].usersource);
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
                                        $('.popup_comfirm').show();
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
                                        $('#source').val(6);
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
                                            $('#REALNAME').val(data.fgj_user[i].truename);
                                            //设置客户来源
                                            $('#SOURCE').val(data.fgj_user[i].usersource);
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
                            //CRM和FGJ都有匹配到的信息
                            else if(data.result == 3)
                            {
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
                                    $('.all_popup_comfirm').show();
                                    $('#multi_from_to_jump').val('jump');
                                }
                                else if(data.crm_user.confirm_status == 1 && data.is_need_confirm_fgj == 1)
                                {   
                                    //CRM确认FGJ没确认直接去CRM数据填充
                                    $('#is_from').val(data.is_from_crm);
                                    //设置用户姓名
                                    $('#REALNAME').val(data.crm_user.truename);
                                    //设置客户来源
                                    $('#SOURCE').val(data.crm_user.usersource);
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
                                            $('#REALNAME').val(data.fgj_user[i].truename);
                                            //设置客户来源
                                            $('#SOURCE').val(data.fgj_user[i].usersource);
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
                                                $('#REALNAME').val(data.fgj_user[i].truename);
                                                //设置客户来源
                                                $('#SOURCE').val(data.fgj_user[i].usersource);
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
                                            $('#SOURCE').val(6);
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
                                                $('#REALNAME').val(data.fgj_user[i].truename);
                                                //设置客户来源
                                                $('#SOURCE').val(data.fgj_user[i].usersource);
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
                                            $('#REALNAME').val(data.crm_user.truename);
                                            //设置客户来源
                                            $('#SOURCE').val(data.crm_user.usersource);

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
                                alert('操作异常');
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
                        var confirm_url = "index.php?s=Member/arrivalConfirm";
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
                    $('#SOURCE').val(6);
                }

                //确认因为多系统有数据需要到到场确认页面进行到场确认操作
                function jump_arrival_cofirm()
                {   
                    var multi_from_to_jump = $('#multi_from_to_jump').val();
                    if(multi_from_to_jump == 'jump')
                    {
                        //跳转到确认页面
                        var confirm_url = "index.php?s=Member/arrivalConfirm";
                        window.location = confirm_url;
                    }
                }


                /***
                 * 数据验证
                 * 数据提交
                 */
                 $(".comfirmbtn").click(function(){

                     /**基础信息数据验证**/

                     //项目ID
                     var PRJID = $('#PRJID').val();
                     //购房人电话
                     var MOBILENO = $('#MOBILENO').val();
                     //看房者电话
                     var LOOKER_MOBILENO = $('#LOOKER_MOBILENO').val();
                     //真实姓名
                     var REALNAME = $('#REALNAME').val();
                     //办卡时间
                     var CARDTIME = $('#CARDTIME').val();
                     //证件类型
                     var CERTIFICATE_TYPE = $('#CERTIFICATE_TYPE').val();
                     //证件号码
                     var IDCARDNO = $('#IDCARDNO').val();
                     //房屋来源
                     var SOURCE = $('#SOURCE').val();
                     //单套收费标准
                     var TOTAL_PRICE = $('#TOTAL_PRICE').val();
                     //中介佣金
                     var AGENCY_REWARD = $('#AGENCY_REWARD').val();
                     //中介成交奖励
                     var AGENCY_DEAL_REWARD = $('#AGENCY_DEAL_REWARD').val();
                     //置业顾问成交奖励
                     var PROPERTY_DEAL_REWARD = $('#PROPERTY_DEAL_REWARD').val();
                     //房屋面积
                     var HOUSEAREA = $('#HOUSEAREA').val();
                     //房屋总价
                     var HOUSETOTAL = $('#HOUSETOTAL').val();
                     //是否带看
                     var ISTAKE = $('#ISTAKE').val();
                     //楼栋号
                     var ROOMNO = $('#ROOMNO').val();
                     //办卡状态
                     var CARDSTATUS = $('#CARDSTATUS').val();
                     //认购时间
                     var SUBSCRIBETIME = $('#SUBSCRIBETIME').val();
                     //签约时间
                     var SIGNTIME = $('#SIGNTIME').val();
                     //签约套数
                     var SIGNEDSUITE = $('#SIGNEDSUITE').val();
                     //收据状态
                     var RECEIPTSTATUS = $('#RECEIPTSTATUS').val();
                     //发票状态
                     var INVOICESTATUS = $('#INVOICESTATUS').val();
                     //短信
                     var IS_SMS = $('#IS_SMS').val();
                     //经办人
                     var OPERATOR = $('#OPERATOR').val();
                     //备注
                     var NOTE = $('#NOTE').val();

                     //电话正则
                     var mobileReg = /^(13[0-9]{1}|145|147|15[0-9]{1}|18[0-9]{1}|17[0-9]{1})[0-9]{8}$/;
                     //数字正则
                     var decmalReg = /^[1-9]\d*.\d*|0.\d*[1-9]\d*|0?.0+|0$/;

                     if(PRJID == ''){
                         alert('请选择会员项目');
                         return false;
                     }

                     if(MOBILENO == '' || !mobileReg.test(MOBILENO)){
                         alert('请填入正确的手机号！');
                         return false;
                     }

                     if(LOOKER_MOBILENO != '' && !mobileReg.test(MOBILENO)){
                         alert('请填入正确的看房人手机号！');
                         return false;
                     }

                     if(REALNAME == ''){
                         alert('请填写会员姓名！');
                         return false;
                     }

                     if(CERTIFICATE_TYPE == 1 && typeof isCardID(IDCARDNO)=='string'){
                     	alert('请填写正确的身份证号码');
                     	return false;
                     }

                     if(SOURCE == '' || SOURCE == 0){
                         alert('请选择会员来源！');
                         return false;
                     }


                     if(TOTAL_PRICE == '' || TOTAL_PRICE == 0){
                         alert('请选填写房型价格！');
                         return false;
                     }

                     if(ISTAKE == '' || ISTAKE == 0){
                         alert('请选择中介是否带看！');
                         return false;
                     }

                     if(CARDSTATUS == '' || CARDSTATUS == 0){
                         alert('请选择办卡状态！');
                         return false;
                     }

                     if(RECEIPTSTATUS == '' || RECEIPTSTATUS == 0){
                         alert('请选择收据状态！');
                         return false;
                     }

                     //提交操作
                     $.ajax({
                         type: "POST",
                         url: "index.php?s=/Member/RegMember",
                         data:$('#member_form').serialize(),
                         async: false,
                         dataType:"JSON",
                         success:function(data)
                         {
                             if(data.status){
                                 alert("添加办卡用户成功!");
                                 location.href='index.php?s=/Member/RegMember';
                             }
                             else
                             {
                                 alert(data.msg);
                             }
                         },
                         error: function(request) {
                             alert("网络错误,请重试~~");
                         },
                     });
                 });
            });

            //验证身份证
            function isCardID(sId){
                var iSum=0 ;
                var info="" ;
                if(!/^\d{17}(\d|x)$/i.test(sId)) return "身份证长度或格式错误";
                sId=sId.replace(/x$/i,"a");
                if(aCity[parseInt(sId.substr(0,2))]==null) return "身份证地区非法";
                sBirthday=sId.substr(6,4)+"-"+Number(sId.substr(10,2))+"-"+Number(sId.substr(12,2));
                var d=new Date(sBirthday.replace(/-/g,"/")) ;
                if(sBirthday!=(d.getFullYear()+"-"+ (d.getMonth()+1) + "-" + d.getDate()))return "身份证上的出生日期非法";
                for(var i = 17;i>=0;i --) iSum += (Math.pow(2,i) % 11) * parseInt(sId.charAt(17 - i),11) ;
                if(iSum%11!=1) return "身份证号非法";
                return true;//aCity[parseInt(sId.substr(0,2))]+","+sBirthday+","+(sId.substr(16,1)%2?"男":"女")
            }

    </script>
    </body>
</html>