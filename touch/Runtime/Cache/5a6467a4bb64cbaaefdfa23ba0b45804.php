<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>�쿨�ͻ�</title>
        <meta http-equiv="content-type" content="text/html; charset=gbk"/>
        <meta name="apple-mobile-web-app-capable" content="yes"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,minimum-scale=1,user-scalable=no">
        <meta name="keywords" content="�쿨�ͻ�">
        <meta name="description" content="�쿨�ͻ�">
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
        <p class="txt">�쿨�ͻ�</p>
    </div>
    <?php } ?>

    <div class="wrap">
        <div class="nav">
            <div class="nav_cont clearfix">
                <a class="tab tabfirst" href="<?php echo U('Member/arrivalConfirm');?>">����ȷ��</a>
                <a class="tab on" href="<?php echo U('Member/RegMember');?>">�쿨�ͻ�</a>
                <a class="tab" href="<?php echo U('Member/changeStatus');?>">״̬���</a>
                <a class="tab tablast" href="<?php echo U('Member/newMember');?>">��Ȼ����</a>
            </div>
        </div>
        <form method="POST" action="" id="member_form">
            <div class="vail_cont_div clearfix">
                <div class="listDiv">
                    <label class="label_txt" for="PRJID"><em class="c-yel">*</em> ��Ŀ���ƣ�</label>
                    <div class="inputDiv">
                        <select name="PRJID" class="demo-test-select input_arri" id="PRJID">
                            <option value="">--��ѡ��--</option>
                            <?php foreach ($projects as $_pro){ ?>
                            <option <?php if($selected_project_id == $_pro['ID'] ) {?>selected<?php } ?> value="<?php echo $_pro['ID']; ?>" data_pro_listid ="<?php echo $_pro['REL_NEWHOUSEID']; ?>" ><?php echo $_pro['PROJECTNAME']; ?></option>
                            <?php } ?>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="MOBILENO"><em class="c-yel">*</em> �ֻ����룺</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="MOBILENO" name="MOBILENO" placeholder="�����ֻ�����">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv" >
                    <label class="label_txt longtxtwid" for="LOOKER_MOBILENO">�������ֻ����룺</label>
                    <div class="inputDiv shortinputwid">
                        <input class="input_arri"  type="text" id="LOOKER_MOBILENO" name="LOOKER_MOBILENO" placeholder="�������ֻ�����">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="REALNAME"><em class="c-yel">*</em> ��Ա������</label>
                    <div class="inputDiv">
                        <input class="input_arri"  type="text" id="REALNAME" name="REALNAME" placeholder="��Ա����">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="CARDTIME"><em class="c-yel">*</em> �쿨���ڣ�</label>
                    <div class="inputDiv">
                        <input class="input_arri demo-test-date" type="date" id="CARDTIME" name="CARDTIME" value="<?=$today?>">

                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="CERTIFICATE_TYPE"><em class="c-yel">*</em> ֤�����ͣ�</label>
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
                    <label class="label_txt" for="IDCARDNO"><em class="c-yel">*</em> ֤�����룺</label>
                    <div class="inputDiv">
                        <input class="input_arri"  type="text" id="IDCARDNO" name="IDCARDNO" placeholder="֤������">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv" style="border:none;text-align:right;display:none" id = 'GET_IDCARDNO'>
                    <a class="getAccessionCode" onclick="openIdCard()">���ջ�ȡ����֤��</a>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="SOURCE"><em class="c-yel">*</em> ��Ա��Դ��</label>
                    <div class="inputDiv">
                        <select name="SOURCE" id="SOURCE" class="demo-test-select input_arri">
                            <option value="">--��ѡ��--</option>
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
                    <label class="label_txt longtxtwid" for="TOTAL_PRICE"><em class="c-yel">*</em> �����շѱ�׼��</label>
                    <div class="inputDiv shortinputwid">
                        <select class="demo-test-select input_arri" name="TOTAL_PRICE" id="TOTAL_PRICE">
                            <option value="">--��ѡ��--</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="AGENCY_REWARD"> �н�Ӷ��</label>
                    <div class="inputDiv shortinputwid">
                        <select class="demo-test-select input_arri" name="AGENCY_REWARD" id="AGENCY_REWARD">
                            <option value="">--��ѡ��--</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="AGENCY_DEAL_REWARD"> �н�ɽ�������</label>
                    <div class="inputDiv shortinputwid">
                        <select class="demo-test-select input_arri" name="AGENCY_DEAL_REWARD" id="AGENCY_DEAL_REWARD">
                            <option value="">--��ѡ��--</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="PROPERTY_DEAL_REWARD"> ��ҵ���ʳɽ�������</label>
                    <div class="inputDiv shortinputwid">
                        <select class="demo-test-select input_arri" name="PROPERTY_DEAL_REWARD" id="PROPERTY_DEAL_REWARD">
                            <option value="">--��ѡ��--</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="HOUSEAREA"> ���������</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="HOUSEAREA" name="HOUSEAREA" placeholder="�������">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="HOUSETOTAL"> �����ܼۣ�</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="HOUSETOTAL" name="HOUSETOTAL" placeholder="�����ܼ�">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="ISTAKE">�Ƿ������</label>
                    <div class="inputDiv">
                        <select id="ISTAKE" class="demo-test-select input_arri" name="ISTAKE">
                            <option value="2">��</option>
                            <option value="1">��</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="ROOMNO">¥��¥�ţ�</label>
                    <div class="inputDiv">
                        <input class="input_arri"   type="text" id="ROOMNO" name="ROOMNO" placeholder="��������Ԫ����">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="CARDSTATUS"><em class="c-yel">*</em> �쿨״̬��</label>
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
                    <label class="label_txt" for="SUBSCRIBETIME">�Ϲ�ʱ�䣺</label>
                    <div class="inputDiv">
                        <input class="input_arri demo-test-date" type="date" id="SUBSCRIBETIME" name="SUBSCRIBETIME" value="<?=$today?>">
                        <i class="drapjiantou"></i>
                    </div>
                </div>

                <div class="listDiv" data-type="cardstatus_3" style="display:none;">
                    <label class="label_txt" for="SIGNTIME"><em class="c-yel">*</em> ǩԼ���ڣ�</label>
                    <div class="inputDiv">
                        <input class="input_arri demo-test-date" type="date" id="SIGNTIME" name="SIGNTIME" value="<?=$today?>">
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv" data-type="cardstatus_3" style="display:none;">
                    <label class="label_txt" for="SIGNEDSUITE">ǩԼ������</label>
                    <div class="inputDiv">
                        <select class="input_arri demo-test-select" name="SIGNEDSUITE" id="SIGNEDSUITE">
                            <option value="0">--��ѡ��--</option>
                            <option value="1">1��</option>
                            <option value="2">2��</option>
                            <option value="3">3��</option>
                            <option value="4">4��</option>
                            <option value="5">5��</option>
                            <option value="6">6��</option>
                            <option value="7">7��</option>
                            <option value="8">8��</option>
                            <option value="9">9��</option>
                            <option value="10">10��</option>
                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="RECEIPTSTATUS"><em class="c-yel">*</em> �վ�״̬��</label>
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
                    <label class="label_txt" for="RECEIPTNO"><em class="c-yel">*</em> �վݱ�ţ�</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="RECEIPTNO" name="RECEIPTNO" placeholder="�վݱ��">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="INVOICESTATUS"><em class="c-yel">*</em> ��Ʊ״̬��</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="INVOICESTATUS" name="INVOICESTATUS" value="δ��"   disabled>
                        <i class="drapjiantou"></i>
                    </div>
                </div>

                <div class="listDiv">
                    <label class="label_txt longtxtwid" for="IS_SMS">�Ƿ��Ͷ��ţ�</label>
                    <div class="inputDiv shortinputwid">
                        <select id="IS_SMS" class="demo-test-select input_arri" name="IS_SMS">
                            <option value="1">������</option>
                            <option value="2">����</option>

                        </select>
                        <i class="drapjiantou"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="OPERATOR">�����ˣ�</label>
                    <div class="inputDiv">
                        <input class="input_arri"   type="text" id="OPERATOR" name="OPERATOR" placeholder="" value="<?=$_SESSION['uinfo']['tname']?>">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="NOTE">��ע��</label>
                    <div class="inputDiv">
                        <input class="input_arri" type="text" id="NOTE" name="NOTE" placeholder="��ע">
                        <i class="sanjiao"></i>
                    </div>
                </div>

                <!--������ϸ����-->
                <div class="pay_cont">
                    <div class="pay_title"></div>
                    <div class="dataplaylist">
                        <div class="smalltitle">
                            <span class="name">| ������ϸ</span>
                            <i  class="sanjiao"></i>
                        </div>
                        <div class="detail_info_cont">
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="PAYTYPE[]"><em class="c-yel">*</em>���ʽ��</label>
                                <div class="inputDiv shortinputwid">
                                    <div class="inputDiv">
                                        <select class="demo-test-select input_arri" name="PAYTYPE[]">
                                            <option value="">--��ѡ��--</option>
                                            <option value="1">POS��</option>
                                            <option value="2">����</option>
                                            <option value="3">�ֽ�</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="RETRIEVAL[]"><em class="c-yel">*</em>6λ�����ţ�</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri" type="text" name="RETRIEVAL[]" placeholder="6λ������">
                                </div>
                            </div>
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="CVV2[]"><em class="c-yel">*</em>���ź���λ��</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri"   type="text" name="CVV2[]" placeholder="���ź���λ">
                                </div>
                            </div>
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="TRADETIME[]"><em class="c-yel">*</em>ԭʼ����ʱ�䣺</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri demo-test-date" type="date" name="TRADETIME[]" value="<?=$today?>">
                                </div>
                            </div>
                            <div class="payinput">
                                <label class="label_txt longtxtwid" for="TRADEMONEY[]"><em class="c-yel">*</em>ԭʼ���׽�</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri" type="text" name="TRADEMONEY[]" placeholder="ԭʼ���׽��">
                                </div>
                            </div>
                            <?php if(!empty($merchant_arr)){ ?>
                            <div class="payinput">
                                <label class="label_txt" for="MERCHANTNUMBER[]">
                                    <em class="c-yel">*</em>
                                    �̻���ţ�
                                </label>
                                <div class="inputDiv">
                                    <select id="MERCHANTNUMBER" name="MERCHANTNUMBER[]" class="demo-test-select input_arri">
                                        <option value="">--��ѡ��--</option>
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
                    <a href="javascript:void(0);" class="getAccessionCode payadd">����һ������</a>
                    <a href="javascript:void(0);" class="getAccessionCode paydel">ɾ��һ������</a>
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
                <button class="comfirmbtn" type="button">ȷ��</button>
            </div>
        </form>
    </div>

    <!--�Ƿ���Ҫ����ȷ�ϵ���-->
    <div class="popup_comfirm" style="display: none;">
        <div class="popup_shadow"></div>
        <div class="popup_content">
            <p class="title">���ܼұ����ͻ���δ��ɵ���ȷ�ϣ��Ƿ�ȷ�ϵ���?</p>
            <div class="btngroup">
                <a class="btn first" onclick="cancle_fgj_confirm_by_user()">��</a><a class="btn" onclick="fgj_arrival_cofirm()">��</a>
            </div>
        </div>
    </div>

    <!--�Ƿ���Ҫ����ȷ�ϵ���-->
    <div class="all_popup_comfirm" style="display: none;">
        <div class="popup_shadow"></div>
        <div class="popup_content">
            <p class="title">���û���CRM�ͷ��ܼҾ�δ��ɵ���ȷ�ϣ��Ƿ�ȷ�ϵ���?</p>
            <div class="btngroup">
                <a class="btn first" onclick="cancle_cofirm()">��</a><a class="btn" onclick="jump_arrival_cofirm()">��</a>
            </div>
        </div>
    </div>

    <script type="text/javascript">
            $(function(){
                //Ԥ���ز���
                $(document).ready(function () {
                    //��Ŀ����
                    $("#PRJID").change();
                });

                //ƻ���豸��������֤�������չ���
                if(is_android_device())
                {
                    $('#GET_IDCARDNO').show();
                }

                //֤�������л�
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


                //����������ϸ
                $(".payadd").click(function(){
                    var obj = $(".dataplaylist").last();
                    $(".pay_cont").append(obj.clone());
                });

                //ɾ��������ϸ
                $(".paydel").click(function(){
                    var len = $(".dataplaylist").length;
                    if(len==1){
                        alert("�Բ��𣬵�ǰֻ��һ��������ϸ!");
                        return false;
                    }
                    var obj = $(".dataplaylist").last();
                    obj.remove();
                });

                //������ͬ�������͵ı����ı仯
                $("select[name='PAYTYPE[]']").live("change",function(){
                    var typeid = $(this).val();
                    var payinput = $(this).parent().parent().parent().siblings();

                    //pos�����ʽ
                    if(typeid==1){
                        payinput.each(function(){
                            $(this).show();
                        });
                    }
                    //�ֽ������
                    else if(typeid==2 || typeid==3){
                        payinput.each(function(i){
                            //����6λ�����š����ź���λ���̻����    ����INPUT��
                            if(i==0 || i==1 || i==4) {
                                $(this).hide();
                            }
                        });
                    }
                });

                //���ݲ�ͬ�İ쿨״̬��д��ͬ��ʱ��
                $("#CARDSTATUS").change(function(){

                    //�쿨״ֵ̬
                    var cardstatus = $(this).val();

                    //�Ѱ����Ϲ�
                    if(cardstatus == 2)
                    {
                        $("div[data-type='cardstatus_2']").show();
                    }
                    else
                    {
                        $("div[data-type='cardstatus_2']").hide();
                    }

                    //�Ѱ���ǩԼ
                    if(cardstatus == 3)
                    {
                        $("div[data-type='cardstatus_3']").show();
                    }
                    else
                    {
                        $("div[data-type='cardstatus_3']").hide();
                    }

                });

                //������Ŀʱ������Ŀ������û���Դ�͵����շѱ�׼
                $("#PRJID").change(function(){
                    var project_id = $("#PRJID").val();
                    if(project_id > 0)
                    {
                        //������Ŀ����
                        set_pro_name();
                        //�����շѱ�׼
                        set_price_standard_select(project_id);
                        //�����·�¥�̱��
                        set_pro_list_id(project_id);
                    }
                });

                //������Ŀ����
                function set_pro_name(){
                    //���Ƴ��ڸ�ֵ
                    $("#PRJ_NAME").remove();
                    var PRJ_NAME = $("#PRJID").find("option:selected").text();
                    var PRJ_NAME_INPUT = "<input type='hidden' name='PRJ_NAME' id='PRJ_NAME' value="+ PRJ_NAME +">";
                    $('#PRJID').after(PRJ_NAME_INPUT);
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
                            url: "index.php?s=/Project/ajax_get_feescale",
                            data:{'prj_id':prj_id, 'case_type': case_type, 'scale_type':scale_type },
                            dataType:"JSON",
                            success:function(data)
                            {
                                if(data[0]['ID'] == 0)
                                {
                                    alert('��Ŀ�����շѱ�׼δ��д');
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
                                    $('#TOTAL_PRICE').append(output.join(''));
                                    $('#AGENCY_REWARD').append(output_a_reward.join(''));
                                    $('#AGENCY_DEAL_REWARD').append(output_a_deal_reward.join(''));
                                    $('#PROPERTY_REWARD').append(output_p_reward.join(''));
                                    $('#PROPERTY_DEAL_REWARD').append(output_p_deal_reward.join(''));
                                }
                                else
                                {
                                    alert('��Ŀ�����շѱ�׼�쳣');
                                    cancle_price_standard_select();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_price_standard_select();
                        alert('��Ŀ��Ϣ�쳣!');
                        return false;
                    }
                }
                
                //ȡ���շѱ�׼�����б�
                function cancle_price_standard_select()
                {
                    var option_str = '<option value="">--��ѡ��--</option>';
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

                //�򿪰�׿����֤ɨ��
                function openIdCard()
                {
                    window.house365js.scanIDCard();
                }

                //��׿�ص����� ����֤�š�����
                function idCardCallback(idcardno,realname)
                {
                    var card_index = 1;
                    $('#certificate_type').val(card_index);

                    $("#idcardno").val(idcardno);
                    $("#realname").val(realname);
                }
                
                //��ȡ�·�¥�̱��
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
                                    alert('��Ŀδ���·�¥����Ϣ���޷����е���ȷ�ϣ�');
                                    cancle_pro_list_id();
                                }
                                else if(data['ID'] >= 1)
                                {   
                                	var list_id = data['REL_NEWHOUSEID'];
                                	if(list_id > 0)
                                	{
                                        //��ȡ��ԭ�ȵ�¥��ID
                                        cancle_pro_list_id();
                                    	var str_input_list_id = "<input type='hidden' name='LIST_ID' id='LIST_ID' value="+ list_id +">";
                                    	$('#PRJID').after(str_input_list_id);
                                	}
                                	else
                                	{
                                        alert('��Ŀδ���·�¥����Ϣ���޷����е���ȷ�ϣ�');
                                        cancle_pro_list_id();
                                	}
                                }
                                else
                                {
                                    alert('�����쳣');
                                    cancle_pro_list_id();
                                }
                            }
                        })
                    }
                    else
                    {	
                    	cancle_pro_list_id();
                        alert('��Ŀ��Ϣ�쳣!');
                        return false;
                    }
                }
                
                //ȡ���·�¥�̱���ֶ�
                function cancle_pro_list_id()
                {
                    $('#LIST_ID').remove();
                }
                

                //���ݺ����ȡ��Ա��Դ
                $("#MOBILENO").blur(function(){
                    var prjid = $.trim($('#PRJID').val());
                    var pro_listid = $('#LIST_ID').val();
                    var telno = $.trim($('#MOBILENO').val());
                    var action_type = 'ajax_userinfo_by_telno';
                    
                    if( prjid == 0 || telno.length != 11 ) 
                    {   
                        return false;
                    }

                    //�����ֻ��Ż�ȡ��Ϣ
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
                                //�����û�����
                                $('#REALNAME').val(data.crm_user.truename);
                                //���ÿͻ���Դ
                                $('#SOURCE').val(data.crm_user.usersource);
                                //����ֱ֤����Ϊ������Դ��δ��֤����Ϊ������Դ���ҵ���ȷ��
                                $('#is_from').val(data.is_from_crm);
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
                                            $('#REALNAME').val(data.fgj_user[i].truename);
                                            //���ÿͻ���Դ
                                            $('#SOURCE').val(data.fgj_user[i].usersource);
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
                                        $('.popup_comfirm').show();
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
                                        $('#source').val(6);
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
                                            $('#REALNAME').val(data.fgj_user[i].truename);
                                            //���ÿͻ���Դ
                                            $('#SOURCE').val(data.fgj_user[i].usersource);
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
                            //CRM��FGJ����ƥ�䵽����Ϣ
                            else if(data.result == 3)
                            {
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
                                    $('.all_popup_comfirm').show();
                                    $('#multi_from_to_jump').val('jump');
                                }
                                else if(data.crm_user.confirm_status == 1 && data.is_need_confirm_fgj == 1)
                                {   
                                    //CRMȷ��FGJûȷ��ֱ��ȥCRM�������
                                    $('#is_from').val(data.is_from_crm);
                                    //�����û�����
                                    $('#REALNAME').val(data.crm_user.truename);
                                    //���ÿͻ���Դ
                                    $('#SOURCE').val(data.crm_user.usersource);
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
                                            $('#REALNAME').val(data.fgj_user[i].truename);
                                            //���ÿͻ���Դ
                                            $('#SOURCE').val(data.fgj_user[i].usersource);
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
                                                $('#REALNAME').val(data.fgj_user[i].truename);
                                                //���ÿͻ���Դ
                                                $('#SOURCE').val(data.fgj_user[i].usersource);
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
                                            $('#SOURCE').val(6);
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
                                                $('#REALNAME').val(data.fgj_user[i].truename);
                                                //���ÿͻ���Դ
                                                $('#SOURCE').val(data.fgj_user[i].usersource);
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
                                            $('#REALNAME').val(data.crm_user.truename);
                                            //���ÿͻ���Դ
                                            $('#SOURCE').val(data.crm_user.usersource);

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
                                alert('�����쳣');
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
                        var confirm_url = "index.php?s=Member/arrivalConfirm";
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
                    $('#SOURCE').val(6);
                }

                //ȷ����Ϊ��ϵͳ��������Ҫ������ȷ��ҳ����е���ȷ�ϲ���
                function jump_arrival_cofirm()
                {   
                    var multi_from_to_jump = $('#multi_from_to_jump').val();
                    if(multi_from_to_jump == 'jump')
                    {
                        //��ת��ȷ��ҳ��
                        var confirm_url = "index.php?s=Member/arrivalConfirm";
                        window.location = confirm_url;
                    }
                }


                /***
                 * ������֤
                 * �����ύ
                 */
                 $(".comfirmbtn").click(function(){

                     /**������Ϣ������֤**/

                     //��ĿID
                     var PRJID = $('#PRJID').val();
                     //�����˵绰
                     var MOBILENO = $('#MOBILENO').val();
                     //�����ߵ绰
                     var LOOKER_MOBILENO = $('#LOOKER_MOBILENO').val();
                     //��ʵ����
                     var REALNAME = $('#REALNAME').val();
                     //�쿨ʱ��
                     var CARDTIME = $('#CARDTIME').val();
                     //֤������
                     var CERTIFICATE_TYPE = $('#CERTIFICATE_TYPE').val();
                     //֤������
                     var IDCARDNO = $('#IDCARDNO').val();
                     //������Դ
                     var SOURCE = $('#SOURCE').val();
                     //�����շѱ�׼
                     var TOTAL_PRICE = $('#TOTAL_PRICE').val();
                     //�н�Ӷ��
                     var AGENCY_REWARD = $('#AGENCY_REWARD').val();
                     //�н�ɽ�����
                     var AGENCY_DEAL_REWARD = $('#AGENCY_DEAL_REWARD').val();
                     //��ҵ���ʳɽ�����
                     var PROPERTY_DEAL_REWARD = $('#PROPERTY_DEAL_REWARD').val();
                     //�������
                     var HOUSEAREA = $('#HOUSEAREA').val();
                     //�����ܼ�
                     var HOUSETOTAL = $('#HOUSETOTAL').val();
                     //�Ƿ����
                     var ISTAKE = $('#ISTAKE').val();
                     //¥����
                     var ROOMNO = $('#ROOMNO').val();
                     //�쿨״̬
                     var CARDSTATUS = $('#CARDSTATUS').val();
                     //�Ϲ�ʱ��
                     var SUBSCRIBETIME = $('#SUBSCRIBETIME').val();
                     //ǩԼʱ��
                     var SIGNTIME = $('#SIGNTIME').val();
                     //ǩԼ����
                     var SIGNEDSUITE = $('#SIGNEDSUITE').val();
                     //�վ�״̬
                     var RECEIPTSTATUS = $('#RECEIPTSTATUS').val();
                     //��Ʊ״̬
                     var INVOICESTATUS = $('#INVOICESTATUS').val();
                     //����
                     var IS_SMS = $('#IS_SMS').val();
                     //������
                     var OPERATOR = $('#OPERATOR').val();
                     //��ע
                     var NOTE = $('#NOTE').val();

                     //�绰����
                     var mobileReg = /^(13[0-9]{1}|145|147|15[0-9]{1}|18[0-9]{1}|17[0-9]{1})[0-9]{8}$/;
                     //��������
                     var decmalReg = /^[1-9]\d*.\d*|0.\d*[1-9]\d*|0?.0+|0$/;

                     if(PRJID == ''){
                         alert('��ѡ���Ա��Ŀ');
                         return false;
                     }

                     if(MOBILENO == '' || !mobileReg.test(MOBILENO)){
                         alert('��������ȷ���ֻ��ţ�');
                         return false;
                     }

                     if(LOOKER_MOBILENO != '' && !mobileReg.test(MOBILENO)){
                         alert('��������ȷ�Ŀ������ֻ��ţ�');
                         return false;
                     }

                     if(REALNAME == ''){
                         alert('����д��Ա������');
                         return false;
                     }

                     if(CERTIFICATE_TYPE == 1 && typeof isCardID(IDCARDNO)=='string'){
                     	alert('����д��ȷ������֤����');
                     	return false;
                     }

                     if(SOURCE == '' || SOURCE == 0){
                         alert('��ѡ���Ա��Դ��');
                         return false;
                     }


                     if(TOTAL_PRICE == '' || TOTAL_PRICE == 0){
                         alert('��ѡ��д���ͼ۸�');
                         return false;
                     }

                     if(ISTAKE == '' || ISTAKE == 0){
                         alert('��ѡ���н��Ƿ������');
                         return false;
                     }

                     if(CARDSTATUS == '' || CARDSTATUS == 0){
                         alert('��ѡ��쿨״̬��');
                         return false;
                     }

                     if(RECEIPTSTATUS == '' || RECEIPTSTATUS == 0){
                         alert('��ѡ���վ�״̬��');
                         return false;
                     }

                     //�ύ����
                     $.ajax({
                         type: "POST",
                         url: "index.php?s=/Member/RegMember",
                         data:$('#member_form').serialize(),
                         async: false,
                         dataType:"JSON",
                         success:function(data)
                         {
                             if(data.status){
                                 alert("���Ӱ쿨�û��ɹ�!");
                                 location.href='index.php?s=/Member/RegMember';
                             }
                             else
                             {
                                 alert(data.msg);
                             }
                         },
                         error: function(request) {
                             alert("�������,������~~");
                         },
                     });
                 });
            });

            //��֤����֤
            function isCardID(sId){
                var iSum=0 ;
                var info="" ;
                if(!/^\d{17}(\d|x)$/i.test(sId)) return "����֤���Ȼ��ʽ����";
                sId=sId.replace(/x$/i,"a");
                if(aCity[parseInt(sId.substr(0,2))]==null) return "����֤�����Ƿ�";
                sBirthday=sId.substr(6,4)+"-"+Number(sId.substr(10,2))+"-"+Number(sId.substr(12,2));
                var d=new Date(sBirthday.replace(/-/g,"/")) ;
                if(sBirthday!=(d.getFullYear()+"-"+ (d.getMonth()+1) + "-" + d.getDate()))return "����֤�ϵĳ������ڷǷ�";
                for(var i = 17;i>=0;i --) iSum += (Math.pow(2,i) % 11) * parseInt(sId.charAt(17 - i),11) ;
                if(iSum%11!=1) return "����֤�ŷǷ�";
                return true;//aCity[parseInt(sId.substr(0,2))]+","+sBirthday+","+(sId.substr(16,1)%2?"��":"Ů")
            }

    </script>
    </body>
</html>