<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>会员支付明细</title>
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

    </head>
    <body>
        <div><?php echo ($form); ?></div>
        <textarea id="last_filter_text" style="display: none"><?php echo ($lastFilter); ?></textarea>
        <script type="text/javascript">
            //通过付款详情退款
            $("#refund_by_details").click(function(){
                var pay_id = new Array();
                var i = 0;
                $("input[name='checkedtd']:checkbox").each(function() 
                {   
                    if ($(this).prop("checked") == true) 
                    {  
                       pay_id[i] = $(this).val();  
                       i += 1;
                    }
                }); 

                if( i == 0 )
                {   
                    layer.alert('请至少选择一条付款明细', {icon: 2});
                    return false;
                }

                $.ajax({
                    type: "GET",
                    url: "<?php echo U('MemberRefund/apply_refund');?>",
                    data:{'pay_id':pay_id,'refund_method':'pay_details'},
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
                 })   
            })
      </script>
    </body>
</html>