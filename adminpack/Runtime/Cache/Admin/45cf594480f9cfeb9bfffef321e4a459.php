<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
	<title>用户管理</title>
	<meta charset="GBK">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!--<link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>-->
	<!--<script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>-->
	<!--<script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>-->
	<!--<script type="text/javascript" src="./Public/validform/js/common.js"></script>-->
	<!--<script type="text/javascript" src="./Public/js/common.js"></script>-->
	<!--<script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>-->
		<!---->
	<!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>-->
	<!--<script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>-->

	<!--<link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all" />-->
	<!--<link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all" />-->
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
	<div class="containter">
		<div class="right fright j-right">
            <div class="handle-tab">
                <ul>
                    <?php switch($action): case "viewUserNew": ?><li class="selected"><a href="<?php echo U('User/viewUserNew');?>">用户管理</a></li><?php break;?>
                        <?php case "userinfoNew": ?><li class="selected"><a href="<?php echo U('User/userinfoNew', 'showForm=2');?>">用户信息</a></li><?php break; endswitch;?>
                </ul>
            </div>
			<?php echo ($form); ?>	 
		</div>
    </div>
<script>
	$(document).ready(function(){
        // 部门选择
        var deptOptions = '<?php echo ($deptOptions); ?>';
        if (deptOptions) {
            $('select[name="DEPTID"]')
                    .html(deptOptions)
                    .addClass('js-example-basic-single')
                    .val($('input[name="DEPTID_OLD"]').val())
                    .unbind('focus')
                    .select2({
                        allowClear: true,
                        noResults: '没有找到相关信息'
                    });

            // 权限选择

        }
		$('select[name="ROLEID"]').select2({allowClear: true});
		$(".contractinfo-table tbody tr").click(function () {
			$(this).siblings().removeClass("selected");
			$(this).addClass("selected");
		});
		
		$("input[name$='USERNAME']").bind('change',function(){
			var demo=$(".registerform").Validform();
			demo.addRule([
				{
					ele:"input[name$='USERNAME']",
					datatype:"*",
					ajaxurl:"<?php echo U('Api/check_User_Exist');?>"+"&USERNAME="+$("input[name$='USERNAME']").val()+"&ID=<?php echo ($ID); ?>",
					nullmsg:"请填写用户名！",
					errormsg:"请重新编辑用户名！"
				} 
			]);
		});
	});
	
	function baseInfo(obj){
		var ID = $(obj).parent().attr("fid");
        var referFrom = $('#refer_from').val();
		var URL = "<?php echo U('User/viewUserNew');?>"+"&showForm=1&ID="+ID;

		fedit(this,URL, referFrom);
	}

	function cityInfo(obj){
		var ID = $(obj).parent().attr("fid");
        var referFrom = $('#refer_from').val();
		var URL = "<?php echo U('User/viewUserNew');?>"+"&showForm=1&ISCITY=-1&ID="+ID;

		fedit(this,URL, referFrom);
	}
	
	function delInfo(obj){
		fdel(obj);
	}
</script>
</body>
</html>