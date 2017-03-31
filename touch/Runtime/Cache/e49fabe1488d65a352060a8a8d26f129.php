<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <title>����ȷ��</title>
    <meta http-equiv="content-type" content="text/html; charset=gbk"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,minimum-scale=1,user-scalable=no">
    <meta name="keywords" content="����ȷ��">
    <meta name="description" content="����ȷ��">
    <link rel="stylesheet" type="text/css" href="./PUBLIC/CSS/business.css">
    <link rel="stylesheet" href="./PUBLIC/CSS/styles.css"/>
    <link href="./PUBLIC/JS/datejs/common.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="./PUBLIC/JS/datejs/jquery-1.9.1.min.js" ></script>
    <script type="text/javascript" src="./PUBLIC/JS/datejs/date.js" ></script>
    <script type="text/javascript" src="./PUBLIC/JS/datejs/select.js" ></script>
    <script type="text/javascript" src="./PUBLIC/JS/datejs/iscroll.js" ></script>
    <script type="text/javascript" src="./PUBLIC/JS/common.js"></script>
    <script type="text/javascript">
        $(function(){
            //�������֤�밴ť
            $(".noCodeBtn").click(function(){
                //��ĿID
                var project_id = $("#project_id").val();
                if(project_id > 0)
                {
                    $("#noCode").show();
                }
                else
                {
                    alert('��Ŀ���Ʊ���ѡ��!');
                    return false;
                }
            });

            $("#cancel").click(function(){
                $("#noCode").fadeOut();
            });

            //ҳ�������Ŀʱ������Ŀ������û���Դ�͵����շѱ�׼
            $("#project_id").change(function(){
                var project_id = $("#project_id").val();
                if(project_id > 0)
                {
                    //����¥������
                    set_pro_name()
                    //��ȡ�·�¥��ID
                    set_pro_list_id(project_id);
                }
            });
        });

        function set_pro_name(){
            //��ֵ��Ŀ����
            $("#project_name").remove();

            var prj_name = $("#project_id").find("option:selected").text();
            var prj_name_input = "<input type='hidden' name='project_name' id='project_name' value="+ prj_name +">";
            $('#project_id').after(prj_name_input);
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
                                $('#project_listid').val(list_id);
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
            $('#project_listid').val('');
        }

        /*�����û�CODE��ȡ�û���Ϣ*/
        function get_userinfo_by_code()
        {
            //�ύ��ʽ
            var action_type = 'ajax_userinfo_by_code';
            //������֤��
            var code = $('#code').val();
            //¥��ID
            var project_listid = $("#project_listid").val();

            if($.trim(code) == '')
                return false;

            $.ajax({
                url: "index.php?s=/Member/arrivalConfirm",
                type: "POST",
                dataType: "JSON",
                data: {'action_type':action_type,'code':code,'project_listid':project_listid},
                success: function(data)
                {
                    if(data.result == 1)
                    {

                        if(data.truename == '' || data.truename == null){
                            alert('û�в鵽�����������û���Ϣ');
                            return false;
                        }

                        $('#truename').val(data.truename);
                        $('#telno').val(data.telno);
                        $('#customer_id').val(data.customer_id);
                        $('#is_from').val(data.is_from);
                        $('#user_project_id').val(data.projectid);

                        if(data.is_from == 2)
                        {
                            $('#ag_id').val(data.ag_id);
                            $('#cp_id').val(data.cp_id);
                        }
                    }
                    else if (data.result == 0)
                    {
                        //û�в�ѯ����������������
                        alert('û�в鵽�����������û���Ϣ');
                    }
                    else
                    {
                        //�쳣����
                        alert('�쳣����');
                    }
                }
            });
        }


        /*�����ֻ������ȡ�û���Ϣ*/
        function get_userinfo_by_telno()
        {
            //�ύ��ʽ
            var action_type = 'ajax_userinfo_by_telno';
            //�ͻ��ֻ���
            var customer_telno = $("#customer_telno").val();
            //�������ֻ���
            var agent_telno = $("#agent_telno").val();
            //��ĿID
            var project_id = $("#project_id").val();
            //�·�¥��ID
            var project_listid = $("#project_listid").val();

            if(customer_telno == '')
            {
                alert('�ͻ��ֻ����벻��Ϊ��');
                return false;
            }

            $("#noCode").fadeOut();

            if(project_id > 0)
            {
                $.ajax({
                    url: "index.php?s=/Member/arrivalConfirm",
                    type: "POST",
                    dataType: "JSON",
                    data: {'action_type':action_type,'customer_telno':customer_telno,
                        'agent_telno':agent_telno,'project_id':project_id,'project_listid':project_listid},
                    success: function(data)
                    {
                        if(data.result == 1)
                        {
                            if(data.code == null || data.code == '')
                            {
                                alert('û�в鵽�û���֤����Ϣ');
                                return false;
                            }

                            if(data.truename == '' || data.truename == null){
                                alert('û�в鵽�����������û���Ϣ');
                                return false;
                            }

                            $('#truename').val(data.truename);
                            $('#customer_telno').val(customer_telno);
                            $('#code').val(data.code);
                            $('#ag_id').val(data.ag_id);
                            $('#cp_id').val(data.cp_id);
                            $('#customer_id').val(data.cm_id);
                            $('#is_from').val(data.is_from);
                            $('#telno').val(customer_telno);
                            if(data.is_from == 1)
                            {
                                $('#user_project_id').val(project_id);
                            }
                            else if(data.is_from == 2)
                            {
                                $('#user_project_id').val(project_listid);
                            }
                        }
                        else if (data.result == 0)
                        {
                            //û�в�ѯ����������������
                            alert('û�в鵽�����������û���Ϣ');
                        }
                        else
                        {
                            //�쳣����
                            alert('�쳣����');
                        }
                    }
                });
            }
            else
            {
                alert('��Ŀ���Ʊ���ѡ��');
                return false;
            }
        }


        /* ajaxȷ�������ύ */
        $(".comfirmbtn").live("click",function(){
            /**������Ϣ������֤**/

            var project_id = $("#project_id").val();
            var truename = $("#truename").val();
            var telno = $("#telno").val();

            if(!project_id || !truename || !telno){
                alert("�뱣֤���е���Ϣ������д��");
                return false;
            }

            //�绰����
            var mobileReg = /^(13[0-9]{1}|145|147|15[0-9]{1}|18[0-9]{1}|17[0-9]{1})[0-9]{8}$/;
            if(telno == '' || !mobileReg.test(telno)){
                alert('��������ȷ���ֻ��ţ�');
                return false;
            }

            //�ύ����
            $.ajax({
                type: "POST",
                url: "index.php?s=/Member/arrivalConfirm",
                data:$('#confirm_form').serialize(),
                async: false,
                dataType:"JSON",
                success:function(data)
                {
                    alert(data.msg);
                    if(data.status)
                    {
                        location.href='index.php?s=/Member/arrivalConfirm';
                    }
                },
                error: function(request) {
                    alert("�������,������~~");
                },
            });
        });

    </script>
</head>
<body>
<?php if($is_login_from_oa){ ?>
<div class="returnIndex">
    <a class="returnBtn" href="####" onclick="back_to_oa_app_index()"></a>
    <p class="txt">����ȷ��</p>
</div>
<?php } ?>

<form name="confirm_form" id="confirm_form" method="post" action="">
    <div class="wrap">
        <div class="nav">
            <div class="nav_cont clearfix">
                <a class="tab tabfirst on" href="<?php echo U('Member/arrivalConfirm');?>">����ȷ��</a>
                <a class="tab" href="<?php echo U('Member/RegMember');?>">�쿨�ͻ�</a>
                <a class="tab" href="<?php echo U('Member/changeStatus');?>">״̬���</a>
                <a class="tab tablast" href="<?php echo U('Member/newMember');?>">��Ȼ����</a>
            </div>
        </div>
        <div class="vail_cont_div clearfix">
            <div class="listDiv">
                <label class="label_txt" for="project_id">��Ŀ���ƣ�</label>
                <div class="inputDiv">
                    <select name="project_id" class="demo-test-select input_arri" id="project_id">
                        <option value="">--��ѡ��--</option>
                        <?php foreach ($projects as $_pro){ ?>
                        <option <?php if($selected_project_id == $_pro['ID'] ) {?>selected<?php } ?> value="<?php echo $_pro['ID']; ?>" data_pro_listid ="<?php echo $_pro['REL_NEWHOUSEID']; ?>" ><?php echo $_pro['PROJECTNAME']; ?></option>
                        <?php } ?>
                    </select>
                    <i class="drapjiantou"></i>
                </div>
            </div>

            <div class="listDiv">
                <label class="label_txt" for="code">��֤�룺</label>
                <div class="inputDiv addNoCode">
                    <input class="input_arri" onblur="get_userinfo_by_code()" type="text" id="code" name="code" placeholder="������֤��">
                    <button class="noCodeBtn" type="button">����֤��</button>
                </div>
            </div>
            <div class="listDiv">
                <label class="label_txt" for="truename">������</label>
                <div class="inputDiv">
                    <input class="input_arri" onblur="" type="text" id="truename" name="truename" placeholder="����">
                    <i class="sanjiao"></i>
                </div>
            </div>
            <div class="listDiv" id = "no_telno">
                <label class="label_txt" for="telno">��ϵ��ʽ��</label>
                <div class="inputDiv">
                    <input class="input_arri" onblur="" type="text" id="telno" name="telno" placeholder="�ֻ�����">
                    <i class="sanjiao"></i>
                </div>
            </div>
        </div>
        <div class="confirmDiv"  id = "no_show">
            <button class="comfirmbtn" type="button">ȷ��</button>
            <input type="hidden" name="action_type" value="arrive_confirm">
            <input type="hidden" name="authcode_key" value="<?=$form_sub_auth_key?>">
            <input type ="hidden" id = "project_listid" name = "project_listid" value="<?=$selected_pro_listid?>">
            <input type ="hidden" id = "user_project_id" name = "user_project_id" value="">
            <input type ="hidden" id = "is_from" name = "is_from" value="">
            <input type ="hidden" id = "customer_id" name = "customer_id" value="">
            <input type ="hidden" id = "ag_id" name = "ag_id" value="">
            <input type ="hidden" id = "cp_id" name = "cp_id" value="">
        </div>
    </div>

    <!--����-->
    <div id="datePlugin"></div>
    <div id="selectPlugin"></div>
</form>

<div id="noCode" class="popup_comfirm" style="display: none;">
    <div class="popup_shadow"></div>
    <div class="popup_content">
        <div class="vail_cont_div clearfix">
            <div class="listDiv">
                <label class="label_txt" for="customer_telno">�ͻ��ֻ����룺</label>
                <div class="inputDiv">
                    <input class="input_arri" type="text" id="customer_telno" name="customer_telno" placeholder="�ͻ��ֻ�����">
                    <i class="sanjiao"></i>
                </div>
            </div>
            <div class="listDiv lastDiv">
                <label class="label_txt" for="agent_telno">�������ֻ����룺</label>
                <div class="inputDiv">
                    <input class="input_arri" type="text" id="agent_telno" name="agent_telno" placeholder="�������ֻ�����">
                    <i class="sanjiao"></i>
                </div>
            </div>
        </div>
        <div class="btngroup">
            <a class="btn first" id = "cancel">ȡ��</a><a class="btn" id="confirm" style = "width:49%" onclick="get_userinfo_by_telno()">ȷ��</a>
        </div>
    </div>
</div>
</body>
</html>