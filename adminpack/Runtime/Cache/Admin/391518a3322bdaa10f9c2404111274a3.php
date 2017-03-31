<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312"/>
    <title></title>
    <script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
    <link type="text/css" rel="stylesheet" href="./Public/css/reset.css">
    <link type="text/css" rel="stylesheet" href="./Public/css/right.css">
    <script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>
    <script type="text/javascript" src="./Public/js/jquery-ui.js"></script>
    <link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all"/>
    <link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all"/>
    <link type="text/css" rel="stylesheet" href="./Public/css/style2.css">
    <link href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <style>
        td{
            font-family: "Microsoft Yahei",\5B8B\4F53,Arial, Helvetica, sans-serif;
        }
        .tishi {
            width: 230px;
            height: 30px;
            background-color: #993333;
            float: left;
            margin: 30px;
            font-size: 20px;
            padding: 20px;
            font-weight: bold;
            color: #FFF;
        }

        .tscontent {
            margin: 0 auto;
            width: 1000px;
            margin-bottom: 50px;
        }

        .lxts {
            width: 100px;
            height: 20px;
            background-color: #FF6666;
            float: left;
            margin: 10px;
            font-size: 20px;
            padding: 10px;
            font-weight: bold;
            color: #FFF;
        }

        .orifont {
            color: #f00;
            font-size: 16px;
        }

        .fred {
            color: red;
        }

        .readonly {
            background-color: #F1F1F1;
            border: none;
        }
    </style>
    <script>
        var appUrl = '';
        var estimate_money = '<?php echo ($estimate_money); ?>';
        //console.log(estimate_money);
        $(document).ready(function () {
            $('input[name=96_AMOUNT]').attr("readonly", 'readonly').addClass('readonly');
            $('input[name=101_AMOUNT]').attr("readonly", 'readonly').addClass('readonly');
            $('input[name=102_AMOUNT]').attr("readonly", 'readonly').addClass('readonly');
            $('input[name=103_AMOUNT]').attr("readonly", 'readonly').addClass('readonly').after('%');
            $('input[name=106_AMOUNT]').attr("readonly", 'readonly').addClass('readonly');
            $('input[name=107_AMOUNT]').attr("readonly", 'readonly').addClass('readonly').after('%');
            $('input[name=108_AMOUNT]').attr("readonly", 'readonly').addClass('readonly');
            $('input[name=109_AMOUNT]').attr("readonly", 'readonly').addClass('readonly');
            $('input[name=110_AMOUNT]').attr("readonly", 'readonly').addClass('readonly').after('%');
            $('.AMOUNT').blur(function () {
                countt();
            });

            var countt = function () {
                var totalAmount = 0;
                var onlinetotalAmount = 0;
                var pro_taxes = 0;//除资金池外项目税金
                var pro_taxes_profit = 0;//税后项目利润
                var pro_taxes_profitp = 0;//税后项目利润率
                var ONLINE_COST = 0;//扣除线下+线上支出利润
                var ONLINE_COST_RATE = 0;//扣除线下+线上支出利润率
                var taxes = 0;
                var OFFLINE_COST_SUM = 0;//付现成本
                var OFFLINE_COST_SUM_PROFIT = 0;//付现利润
                var OFFLINE_COST_SUM_PROFIT_RATE = 0;//付现利润率

                $('.AMOUNT').each(function () {
                    if ($(this).attr('isonline') == 0) {
                        taxes = Number($('input[name=80_AMOUNT]').val()) * 0.1;
                       if(taxes>0) taxes = taxes.toFixed(2);
                        $('input[name=96_AMOUNT]').val(taxes);
                        totalAmount += Number($(this).val());
                    } else {
                        if ($(this).attr('fid') == 98)
                            onlinetotalAmount += Number($(this).val());
                    }
                });  // console.log(totalAmount); console.log(onlinetotalAmount);

                $('.RATIO').each(function () {

                    var fid = $(this).attr('fid');
                    var amount = $('input[name=' + fid + '_AMOUNT]').val();
                    if ($(this).attr('isonline') == 0 && totalAmount) {

                        var ratio = amount / totalAmount * 100;
                        ratio = ratio.toFixed(2);
                    } else if ($(this).attr('isonline') == 1 && onlinetotalAmount) {
                        //var ratio = amount/onlinetotalAmount*100;
                        //ratio = ratio.toFixed(2);
                    } else {
                        ratio = 0;
                    }
                    if ($(this).attr('fid') != 98 && $(this).attr('fid') != 99)
                        $(this).val(ratio).siblings('span').eq(0).html(ratio);
                    else $(this).val(ratio).parent().html('');
                });

                if (estimate_money) {
                    pro_taxes = (estimate_money * 0.1 - taxes );
                    pro_taxes = pro_taxes.toFixed(2);
                   // console.log(totalAmount);
                    $('input[name=101_AMOUNT]').val(pro_taxes);
                    $('#101_AMOUNT').html(pro_taxes);
                    pro_taxes_profit = estimate_money - totalAmount - pro_taxes;
                   // console.log(pro_taxes);
                    pro_taxes_profit = pro_taxes_profit.toFixed(2);
                    //$('#102_AMOUNT').html(pro_taxes_profit);
                    $('input[name=102_AMOUNT]').val(pro_taxes_profit);
                    $('#102_AMOUNT').html(pro_taxes_profit);
                    pro_taxes_profitp = (estimate_money - totalAmount - pro_taxes) / estimate_money * 100;
                    pro_taxes_profitp = pro_taxes_profitp.toFixed(2);
                    $('input[name=103_AMOUNT]').val(pro_taxes_profitp);
                    $('#103_AMOUNT').html(pro_taxes_profitp + '%');
                    ONLINE_COST = estimate_money - totalAmount - Number($('input[name=98_AMOUNT]').val());

                    ONLINE_COST_RATE = ONLINE_COST / estimate_money * 100;
                    ONLINE_COST_RATE = ONLINE_COST_RATE.toFixed(2);
                    $('input[name=107_AMOUNT]').val(ONLINE_COST_RATE);
                    $('#107_AMOUNT').html(ONLINE_COST_RATE + '%');

                    ONLINE_COST = ONLINE_COST.toFixed(2);
                    $('input[name=106_AMOUNT]').val(ONLINE_COST);
                    $('#106_AMOUNT').html(ONLINE_COST);

                    OFFLINE_COST_SUM = totalAmount;
                    OFFLINE_COST_SUM.toFixed(2);
                    $('input[name=108_AMOUNT]').val(OFFLINE_COST_SUM);
                    $('#108_AMOUNT').html(OFFLINE_COST_SUM);
                    OFFLINE_COST_SUM_PROFIT = estimate_money - totalAmount;
                    OFFLINE_COST_SUM_PROFIT = OFFLINE_COST_SUM_PROFIT.toFixed(2);
                    $('input[name=109_AMOUNT]').val(OFFLINE_COST_SUM_PROFIT);
                    $('#109_AMOUNT').html(OFFLINE_COST_SUM_PROFIT);
                    OFFLINE_COST_SUM_PROFIT_RATE = (estimate_money - totalAmount) / estimate_money * 100;
                    OFFLINE_COST_SUM_PROFIT_RATE = OFFLINE_COST_SUM_PROFIT_RATE.toFixed(2);
                    $('input[name=110_AMOUNT]').val(OFFLINE_COST_SUM_PROFIT_RATE);
                    $('#110_AMOUNT').html(OFFLINE_COST_SUM_PROFIT_RATE + '%');

                }
            };
            countt();
            $(".registerform").Validform({
                tiptype: function (msg, o, cssctl) {
                    //msg：提示信息;
                    //o:{obj:*,type:*,curform:*}, obj指向的是当前验证的表单元素（或表单对象），type指示提示的状态，值为1、2、3、4， 1：正在检测/提交数据，2：通过验证，3：验证失败，4：提示ignore状态, curform为当前form对象;
                    //cssctl:内置的提示信息样式控制函数，该函数需传入两个参数：显示提示信息的对象 和 当前提示的状态（既形参o中的type）;

                    if (!o.obj.is("form")) {  //验证表单元素时o.obj为该表单元素，全部验证通过提交表单时o.obj为该表单对象;
                        o.obj.parents("td").append('<div class="info"><span class="Validform_checktip"> </span><span class="dec"><s class="dec1">&#9670;</s><s class="dec2">&#9670;</s></span></div>');

                        var objtip = o.obj.parents("td").find(".Validform_checktip");
                        cssctl(objtip, o.type);
                        objtip.text(msg);

                        var infoObj = o.obj.parents("td").find(".info");
                        if (o.type == 2) {
                            infoObj.fadeOut(200);
                        } else {
                            if (infoObj.is(":visible")) {
                                return;
                            }
                            var left = o.obj.offset().left,
                                    top = o.obj.offset().top;

                            infoObj.css({
                                left: left + 20,
                                top: top - 45
                            }).show().animate({
                                top: top - 35
                            }, 200);
                        }
                    }
                },
                ignoreHidden: true,
                ajaxPost: false,
                beforeSubmit: function (curform) {
                    if (estimate_money <= 0) {
                        alert('请填写预估总收入');
                        return false;
                    }
                },
                beforeCheck: function () {
                },
                callback: function (data) {
                    alert('保存成功！');
                },
                usePlugin: {}
            });
        });
    </script>
</head>
<body>
<div class="kctjcon" align="center" style="line-height: 18px;">
    <div class="table-bg">
        <br/>

        <form class='registerform' action="" method="post">
            <?php echo ($html); ?>
            <?php if($showbutton == -1): ?><div style="padding:10px;margin-top:30px;"><input class="btn btn-bg btn-primary" type='submit' name='' value='保存'></div><?php endif; ?>
        </form>
        <br/>
    </div>
</div>
<script>
   $(function () {
        var showBtn = "<?php echo ($showbutton); ?>";
        var url = "<?php echo ($url); ?>";
		var reflash = '<?php echo ($reflash); ?>';
        if (showBtn == -1) {
            var data = {};
            $('form input').each(function (index, elem) {
                if ($(elem).attr('name').trim()) {
                    data[$(elem).attr('name')] = $(elem).val();
                }
            });
            data['postfee'] = 'save';
            data['is_ajax'] = 1;  // 异步调用
            $.post(url, data, function(resp, status) {
                resp = JSON.parse(resp);
                for(var key in resp.data) {
                    $("input[name='" + key +"_ID']").val(resp.data[key]);
                }
				//if(resp.data>0){
					
					if(reflash == 1){}
					else{ 
						window.location.href=window.location.href+'&reflash=1';
					}
					//setInterval(function(){window.location.href=window.location.href+'&reflash=1';},5000);
				//}
            });
        }
    });
</script>
</body>
</html>