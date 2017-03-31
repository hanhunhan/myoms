<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <title>自然来客</title>
    <meta http-equiv="content-type" content="text/html; charset=gbk"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,minimum-scale=1,user-scalable=no">
    <meta name="keywords" content="自然来客">
    <link rel="stylesheet" href="./Public/CSS/business.css">
    <link rel="stylesheet" href="./Public/CSS/styles.css"/>
    <script src="./PUBLIC/JS/datejs/jquery-1.9.1.min.js" type="text/javascript"></script>
    <script src="./Public/JS/valide.js" type="text/javascript"></script>
    <script src="./Public/JS/common.js" type="text/javascript"></script>
</head>
<body>

<?php if($is_login_from_oa){ ?>
<div class="returnIndex">
    <a class="returnBtn" href="####" onclick="back_to_oa_app_index()"></a>
    <p class="txt">自然来客</p>
</div>
<?php } ?>

<form name="member_form" id="member_form" method="post" action="">
<div class="wrap">
    <div class="nav">
        <div class="nav_cont clearfix">
            <a class="tab tabfirst" href="<?php echo U('Member/arrivalConfirm');?>">到场确认</a>
            <a class="tab" href="<?php echo U('Member/RegMember');?>">办卡客户</a>
            <a class="tab" href="<?php echo U('Member/changeStatus');?>">状态变更</a>
            <a class="tab tablast on" href="<?php echo U('Member/newMember');?>">自然来客</a>
        </div>
    </div>

    <div class="vail_cont_div clearfix">
        <div class="listDiv">
            <label class="label_txt" for="">项目名称：</label>
            <div class="inputDiv">
                <select name="project_id" class="demo-test-select input_arri" id="project_id">
                    <option value="">--请选择--</option>
                    <?php foreach ($projects as $_pro){ ?>
                    <option <?php if($selected_project_id == $_pro['ID'] ) {?>selected<?php } ?> value="<?php echo $_pro['ID']; ?>" data_pro_listid ="<?php echo $_pro['REL_NEWHOUSEID']; ?>" ><?php echo $_pro['PROJECTNAME']; ?></option>
                    <?php } ?>
                </select>
                <i class="drapjiantou"></i>
            </div>

        </div>
        <div class="listDiv">
            <label class="label_txt" for="cusname">客户姓名：</label>
            <div class="inputDiv">
                <input class="input_arri" onblur="" type="text" id="cusname" name="cusname" placeholder="客户姓名">
                <i class="sanjiao"></i>
            </div>
        </div>
        <div class="listDiv">
            <label class="label_txt" for="telno">手机号：</label>
            <div class="inputDiv">
                <input class="input_arri" onblur="" type="text" id="telno" name="telno" placeholder="客户手机号">
                <i class="sanjiao"></i>
            </div>
        </div>

    </div>
    <div class="confirmDiv">
        <button class="comfirmbtn" type="button">确认</button>
    </div>
</div>
</form>

<script type="text/javascript">
    /* ajax确认数据提交 */

    $(".comfirmbtn").live("click",function(){
        /**基础信息数据验证**/

        var project_id = $("#project_id").val();
        var cusname = $("#cusname").val();
        var telno = $("#telno").val();

        if(!project_id || !cusname || !telno){
            alert("请保证所有的信息都已填写！");
            return false;
        }

        //电话正则
        var mobileReg = /^(13[0-9]{1}|145|147|15[0-9]{1}|18[0-9]{1}|17[0-9]{1})[0-9]{8}$/;
        if(telno == '' || !mobileReg.test(telno)){
            alert('请填入正确的手机号！');
            return false;
        }

        //提交操作
        $.ajax({
            type: "POST",
            url: "index.php?s=/Member/newMember",
            data:$('#member_form').serialize(),
            async: false,
            dataType:"JSON",
            success:function(data)
            {
                if(data.status)
                {
                    alert("新用户已经成功录入!");
                    location.href = 'index.php?s=/Member/newMember';
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
</script>
</body>
</html>