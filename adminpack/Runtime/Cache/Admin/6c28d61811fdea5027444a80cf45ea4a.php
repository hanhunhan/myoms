<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>��Ա�˿��б�</title>
        <meta charset="GBK">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
        <link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>
        <script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>
        <script type="text/javascript" src="./Public/validform/js/common.js"></script>
        <script type="text/javascript" src="./Public/js/common.js"></script>
        <script language="javascript" type="text/javascript" src="./Public/layer/layer.js"></script>

        <link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all" />
        <link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all" />
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
        //������˵�
        $("#add_to_audit_list").click(function()
        {
            var refundId = new Array();
            var i = 0;
            $("input[name= 'checkedtd']:checkbox").each(function() 
            {   
                if ($(this).prop("checked") == true) 
                {  
                   refundId[i] = $(this).val();  
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
                url: '<?php echo U("MemberRefund/add_to_audit_list");?>',
                data:{'refundId':refundId},
                dataType:"JSON",
                success:function(data){
                    
                    if(data.status == 'noauth')
                    {
                        layer.alert(data.msg, {icon: 2, closeBtn: false},function(){layer.closeAll();});
                        return false;
                    }
                            
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
        
        //�����˿�����
        $(".cancel_from_details").click(function()
        {   
            var reund_details_id = $(this).parent().filter('.fedit').attr('fid');
             
            if(reund_details_id > 0)
            {   
                layer.confirm('ȷ��Ҫ�����˿����룿', {
                    btn: ['ȷ��', 'ȡ��'],
                    title: '�Ƿ����˿�����?',
                    closeBtn: false
                },
                function(index, layero){
                    //ȷ�ϲ���
                    $.ajax({
                        type: "POST",
                        url:'<?php echo U("MemberRefund/delete_from_details");?>',
                        data:{'reund_details_id' : reund_details_id},
                        dataType:"JSON",
                        success:function(data){
                            
                            if(data.status == 'noauth')
                            {
                                layer.alert(data.msg, {icon: 2, closeBtn: false},function(){layer.closeAll();});
                                return false;
                            }
                        
                            if(data.state == 0)
                            {   
                                layer.close(index);
                                layer.alert(data.msg, {icon: 2, closeBtn: false}, 
                                            function(){window.location.reload();});
                            }
                            else if(data.state == 1)
                            {   
                                layer.close(index);
                                layer.alert(data.msg, {icon: 1, closeBtn: false}, 
                                            function(){window.location.reload();});
                            }
                            else
                            {   
                                layer.close(index);
                                var msg = data.msg ? data.msg : '�����쳣';
                                layer.alert(msg, {icon: 2, closeBtn: false},
                                            function(){window.location.reload();});
                            }
                        }
                    })
                },
                function(index){
                   layer.close(index);
                });
            }
            else
            {
                var msg = data.msg ? data.msg : '�����쳣��ɾ��ʧ��';
                layer.alert(msg, {icon: 2, closeBtn: false},
                    function(){window.location.reload();});
            }
        })
        
        
        //�鿴��˵�
        $("#view_audit_list").click(function()
        {
            layer.open({
                type : 2,
                title : '�˿�������˵�',
                content : '<?php echo U("MemberRefund/refund_audit_list");?>',
                area : ['95%', '80%'],
                cancel: function(index){ window.location.reload();} 
            });
        })
        </script>
        <textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
<script>
    $(function() {
        $('#last_filter_result').text($('#last_filter_text').text());  // �������ϴ������Ľ��
    });
</script>
    </body>
</html>