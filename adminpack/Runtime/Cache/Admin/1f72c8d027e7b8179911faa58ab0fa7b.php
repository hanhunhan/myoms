<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>借款申请</title>
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
        <div class="handle-tab">
            <?php if($ischildren != 1 && $layer != 1): ?><ul>
                <li class="selected"><a href="<?php echo U('Loan/loan_application',$paramUrl);?>">借款申请</a></li>
                <!--<li ><a href="<?php echo U('Loan/opinionFlow',$paramUrl);?>">审批工作流</a></li>-->
                </ul><?php endif; ?>
        </div>
        <?php echo ($form); ?>
      <div>   
</div>
<script>
    $(function(){
        $(".contractinfo-table tbody tr").click(function () 
        {
            $(this).siblings().removeClass("selected");
            $(this).addClass("selected");
        });

        var proOptions = '<?php echo ($proOptions); ?>';
        if (proOptions) {
            $('select[name="PID"]')
                    .html(proOptions)
                    .addClass('js-example-basic-single')
                    .val($('input[name="PID_OLD"]').val())
                    .unbind('focus')
                    .select2({
                        allowClear: true,
                        noResults: '没有找到相关信息'
                    });

            //更新数据
            $('select[name="PID"]').on("change", function (e) {
                var pId = $('select[name="PID"]').val();
                $.ajax({
                    type: "GET",
                    url: "index.php?s=/Loan/loan_application",
                    data: {'pId': pId,'act': 'getContract'},
                    dataType: "JSON",
                    success: function (data) {
                        if(data.status){
                            $('input[name="CONTRACT"]').parent().prev().html(data.data.contract);
                        }
                    },
                    error: function(data){

                    },
                })
            });
        }

        function checkboxevent(obj)
        {
            var fid = $(obj).val(); 

            if( $(obj).is(':checked') )
            {
                if(!$("#selecttr"+fid).length )$(obj).after('<input name="selecttr[]" id="selecttr'+fid+'"  class="selecttr" value="'+fid+'" type="hidden">');
            }
            else
            {
                $("#selecttr"+fid).remove();
            }

            var idsarr = [];
            $(".selecttr").each(function()
            {
                idsarr.push($(this).val() );
            });

            var ids = idsarr.join(',');

            $("input[name=loanapplicationIds]",window.parent.document).val(ids);
        }

        $('.checkedtd').each(function()
        {
            $(this).click(function()
            {
               checkboxevent(this);
            });
        });

        $("#checkall").click( 
            function()
            { 
                if(this.checked)
                { 
                    $("input[name='checkedtd']").each(function()
                    { 
                        this.checked = true;
                        checkboxevent(this);
                    }); 
                }
                else
                { 
                    $("input[name='checkedtd']").each(function()
                    {
                        this.checked = false;
                        checkboxevent(this);
                    }); 
                } 
            } 
        );
    }) ;

    //状态编辑
    function statusEdit(){
        var i = 0,fid = 0;
        //获取ID
        $("input[name='checkedtd']").each(function(){
            if($(this).prop("checked") == true)
            {
                fid = $(this).val();
                i++;
            }
        });

        if(i>1 || i==0){
            layer.alert("对不起，请选择其中一条记录进行借款状态编辑!",{icon:2});
            return false;
        }

        //状态判断
        var status = $("input[name='"+ fid +"_STATUS_OLD']").val();
        if(status != 2 && status != 6){
            layer.alert('对不起，只能将"已审核"和"部分关联报销"调整至"已关联报销"状态！',{icon:2});
            return false;
        }

        //确认提示
        layer.confirm("您确定要将该条记录借款状态调整为’已关联报销‘状态吗？",
            {
                btn: ['确认','取消'] //按钮
            },function(){
                    //数据操作
                    $.ajax({
                        type: "GET",
                        url: "index.php?s=/Loan/loan_application",
                        data: {'loanId': fid,'act': 'updateStatus'},
                        dataType: "JSON",
                        success: function (data) {
                            if(data.status){
                                layer.alert(data.msg,{icon:1});
                                window.location.reload();
                            }else{
                                layer.alert(data.msg,{icon:2});
                            }
                        },
                        error: function(data){
                        },
                    });
            }, function(index){
                layer.close(index);
                return false;
        });
    }

    //提交借款申请
    function addflow()
    {
        var fid = new Array();
        var i = 0;
        $("input[name='checkedtd']").each(function(){
            if($(this).prop("checked") == true)
            {
                fid[i++] = $(this).val();
            }
        })
               
        if(fid.length != 1)
        {
            layer.alert("请选择一条借款记录",{icon:0});
            return false;
        }
        
        var status = $('select[name='+fid[0]+'_STATUS]').val();
        if(status == 0)
        {
//            var url = '<?php echo U("Loan/opinionFlow");?>'+'&FLOWTYPE=jiekuanshenqing&RECORDID='+fid[0];
            var url = '<?php echo U("Touch/Loan/process");?>'+'&FLOWTYPE=jiekuanshenqing&RECORDID='+fid[0];
            window.location.href = url;
        }
        else
        {
            layer.alert('不允许重复提交！', {icon: 2});
        }
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