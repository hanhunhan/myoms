<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <title>状态变更</title>
    <meta http-equiv="content-type" content="text/html; charset=gbk"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,minimum-scale=1,user-scalable=no">
    <meta name="keywords" content="状态变更">
    <link rel="stylesheet" type="text/css" href="./Public/CSS/business.css">
    <link rel="stylesheet" href="./Public/CSS/styles.css"/>
    <link href="./PUBLIC/JS/datejs/common.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="./PUBLIC/JS/datejs/jquery-1.9.1.min.js" ></script>
    <script type="text/javascript" src="./PUBLIC/JS/datejs/date.js" ></script>
    <script type="text/javascript" src="./PUBLIC/JS/datejs/select.js" ></script>
    <script type="text/javascript" src="./PUBLIC/JS/datejs/iscroll.js" ></script>
    <script src="./PUBLIC/JS/common.js" type="text/javascript"></script>

    <!--如果是展现搜索列表-->
    <?php if($action_type == 'serach_user_list'){ ?>
    <script type="text/javascript">
        var myScroll,pullDownEl, pullDownOffset,pullUpEl, pullUpOffset, generatedCount = 0;
        function pullDownAction ()
        {
            myScroll.refresh();
            return false;
        }

        function pullUpAction ()
        {
            setTimeout(function () {
                var el, li, i;
                el = document.getElementById('listUI');

                //ajax获取用户数据
                var page = parseInt($('#page').val());
                var next_page = page + 1;
                var perpage_num = 2;
                var action_type = $('#action_type').val();
                var authcode_key = $('#authcode_key').val();
                var truename = $('#truename').val();
                var telno = $('#telno').val();

                $.ajax({
                    url: "index.php?s=/Member/changeStatus",
                    type: "POST",
                    dataType: "JSON",
                    data: {'action_type':action_type , 'next_page':next_page ,
                        'perpage_num':perpage_num ,'authcode_key':authcode_key,
                        'truename':truename,'telno':telno},
                    success: function(data)
                    {

                        console.log(data);
                        if(data.status)
                        {
                            //页码赋值
                            $('#page').val(next_page);
                            for (i = 0; i < data.data.user_list.length ; i++)
                            {
                                li = document.createElement('li');

                                var first_mt = '';
                                if(i == 0 )
                                {
                                    first_mt = 'firstinfo_mt';
                                }

                                li.innerHTML = "<div class='userInfo add_border_b "+first_mt+" '>"+
                                        "<a style='display: block;' href='index.php?s=/Member/changeStatus&action_type=get_userinfo&authcode_key="+data.data.authcode_key+"&uid="+data.data.user_list[i].id+"'>"+
                                        "<dl class='infodl clearfix'>"+
                                        "<dt class='headerdt'>"+
                                        "<img class='headimg' src='./PUBLIC/IMAGES/header_portrait.png'/></dt>"+
                                        "<dd class='infodd'>客户姓名："+data.data.user_list[i].realname+"[<font style='color:red;'>"+data.data.user_list[i].cityname+"</font>]</dd>"+
                                        "<dd class='infodd'>手机号："+data.data.user_list[i].mobileno+"</dd>"+
                                        "<dd class='infodd' style='margin-bottom: 0px'>项目名称："+data.data.user_list[i].projectname+"</dd></dl></div>";
                                el.appendChild(li, el.childNodes[0]);
                            }
                        }
                        else
                        {
                            if($("#no_more_data_tip_up").length <= 0)
                            {
                                //没有查询到符合条件的数据
                                li = document.createElement('li');
                                li.innerHTML = "<div class='nosercherInfo' id='no_more_data_tip_up'>"+
                                        "<p class='info'> <span class='tanhao'></span>抱歉，没有更多符合条件的信息</p>"+
                                        "</div>";
                                el.appendChild(li, el.childNodes[0]);
                                //$('#no_more_data_tip_up').parent().prev().remove();
                                $('#pullUp').remove();
                            }
                        }
                    },
                    error: function(){
                      alert("网络异常!");
                    },
                });
                myScroll.refresh();
            }, 1000);
        }

        function loaded() {
            pullDownEl = document.getElementById('pullDown');
            pullDownOffset = pullDownEl.offsetHeight;
            pullUpEl = document.getElementById('pullUp');
            pullUpOffset = pullUpEl.offsetHeight;
            myScroll = new iScroll('wrapper', {
                useTransition: true,
                topOffset: pullDownOffset,
                onRefresh: function () {
                    if (pullDownEl.className.match('loading')) {
                        pullDownEl.className = '';
                        pullDownEl.querySelector('.pullDownLabel').innerHTML = '';
                    } else if (pullUpEl.className.match('loading')) {
                        pullUpEl.className = '';
                        pullUpEl.querySelector('.pullUpLabel').innerHTML = '';
                    }
                },
                onScrollMove: function () {
                    if (this.y > 5 && !pullDownEl.className.match('flip')) {
                        pullDownEl.className = 'flip';
                        pullDownEl.querySelector('.pullDownLabel').innerHTML = '';
                        this.minScrollY = 0;
                    } else if (this.y < 5 && pullDownEl.className.match('flip')) {
                        pullDownEl.className = '';
                        pullDownEl.querySelector('.pullDownLabel').innerHTML = '';
                        this.minScrollY = -pullDownOffset;
                    } else if (this.y < (this.maxScrollY - 5) && !pullUpEl.className.match('flip')) {
                        pullUpEl.className = 'flip';
                        pullUpEl.querySelector('.pullUpLabel').innerHTML = '';
                        this.maxScrollY = this.maxScrollY;
                    } else if (this.y > (this.maxScrollY + 5) && pullUpEl.className.match('flip')) {
                        pullUpEl.className = '';
                        pullUpEl.querySelector('.pullUpLabel').innerHTML = '';
                        this.maxScrollY = pullUpOffset;
                    }
                },
                onScrollEnd: function () {
                    if (pullDownEl.className.match('flip')) {
                        pullDownEl.className = 'loading';
                        pullDownEl.querySelector('.pullDownLabel').innerHTML = 'Loading...';
                        pullDownAction();	// Execute custom function (ajax call?)
                    } else if (pullUpEl.className.match('flip')) {
                        pullUpEl.className = 'loading';
                        pullUpEl.querySelector('.pullUpLabel').innerHTML = 'Loading...';
                        pullUpAction();	// Execute custom function (ajax call?)
                    }
                }
            });
            setTimeout(function () { document.getElementById('wrapper').style.left = '0'; }, 800);
        }
        document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
        document.addEventListener('DOMContentLoaded', function () { setTimeout(loaded, 200); }, false);
    </script>

    <style type="text/css" media="all">
        #wrapper {
            position:absolute; z-index:1;
            top:30px; bottom:65px; left:-9999px;
            width:100%;
            overflow:auto;
            background: #f2f2f2;
        }

        #scroller {
            position:absolute; z-index:1;
            -webkit-tap-highlight-color:rgba(0,0,0,0);
            width:100%;
            padding:0;
        }
        #pullDown, #pullUp {
            background:#f2f2f2;
            height:40px;
            line-height:40px;
            padding:5px 10px;
            border-bottom:1px solid #ccc;
            font-weight:bold;
            font-size:14px;
            color:#888;
        }
        #pullDown .pullDownIcon, #pullUp .pullUpIcon  {
            display:block; float:left;
            width:40px; height:40px;
        //   background:url(images/workflow_c.png) 0 0 no-repeat;
            -webkit-background-size:40px 80px; background-size:40px 80px;
            -webkit-transition-property:-webkit-transform;
            -webkit-transition-duration:250ms;
        }
        #pullDown .pullDownIcon {
            -webkit-transform:rotate(0deg) translateZ(0);
        }
        #pullUp .pullUpIcon  {
            -webkit-transform:rotate(-180deg) translateZ(0);
        }

        #pullDown.flip .pullDownIcon {
            -webkit-transform:rotate(-180deg) translateZ(0);
        }

        #pullUp.flip .pullUpIcon {
            -webkit-transform:rotate(0deg) translateZ(0);
        }

        #pullDown.loading .pullDownIcon, #pullUp.loading .pullUpIcon {
            background-position:0 100%;
            -webkit-transform:rotate(0deg) translateZ(0);
            -webkit-transition-duration:0ms;

            -webkit-animation-name:loading;
            -webkit-animation-duration:2s;
            -webkit-animation-iteration-count:infinite;
            -webkit-animation-timing-function:linear;
        }

        @-webkit-keyframes loading {
            from { -webkit-transform:rotate(0deg) translateZ(0); }
            to { -webkit-transform:rotate(360deg) translateZ(0); }
        }
    </style>
    <?php } ?>
</head>
<body>

<?php if($is_login_from_oa){ ?>
<div class="returnIndex">
    <a class="returnBtn" href="####" onclick="back_to_oa_app_index()"></a>
    <p class="txt">状态变更</p>
</div>
<?php } ?>

<form name="changestatus_form" id="changestatus_form" method="post" action="">
    <div class="wrap">
        <div class="nav">
            <div class="nav_cont clearfix">
                <a class="tab tabfirst" href="<?php echo U('Member/arrivalConfirm');?>">到场确认</a>
                <a class="tab" href="<?php echo U('Member/RegMember');?>">办卡客户</a>
                <a class="tab on" href="<?php echo U('Member/changeStatus');?>">状态变更</a>
                <a class="tab tablast" href="<?php echo U('Member/newMember');?>">自然来客</a>
            </div>
        </div>

        <!--如果是查询界面-->
        <?php if( $action_type == ''){ ?>

            <div class="vail_cont_div clearfix">
                <div class="listDiv">
                    <label class="label_txt" for="truename">客户姓名：</label>
                    <div class="inputDiv">
                        <input class="input_arri" onblur="" type="text" id="truename" name="truename" placeholder="查询客户姓名">
                        <i class="sanjiao"></i>
                    </div>
                </div>
                <div class="listDiv">
                    <label class="label_txt" for="telno">手机号：</label>
                    <div class="inputDiv">
                        <input class="input_arri" onblur="" type="text" id="telno" name="telno" placeholder="查询客户手机号">
                        <i class="sanjiao"></i>
                    </div>
                </div>
            </div>
            <div class="confirmDiv">
                <button class="comfirmbtn">确认</button>
            </div>
            <input type="hidden" name="action_type" value='serach_user_list'>
            <input type="hidden" name="authcode_key" value="<?=$form_sub_auth_key?>">

        <!--查询用户数据-->
        <?php }else if($action_type == 'serach_user_list'){ ?>
            <!--如果查询到用户数据-->
            <?php if(!empty($userinfo)) { ?>
                <div style="overflow: hidden; left: 0px;" id="wrapper">
                    <div style="transform-origin: 0px 0px 0px; transition-timing-function: cubic-bezier(0.33, 0.66, 0.66, 1); position: absolute; top: -0px; left: 0px; transition-duration: 242ms;" id="scroller">
                        <div class="" id="pullDown">
                            <span class="pullDownIcon"></span><span class="pullDownLabel"></span>
                        </div>
                        <ul id="listUI">
                            <li></li>
                            <?php foreach($userinfo as $key => $value) { ?>
                            <li>
                                <div class="userInfo add_border_b">
                                    <a style="display: block;" href="index.php?s=/Member/changeStatus&action_type=get_userinfo&authcode_key=<?=$form_sub_auth_key?>&uid=<?=$value['ID']?>">
                                        <dl class="infodl clearfix">
                                            <dt class="headerdt"><img class="headimg" src="./Public/IMAGES/header_portrait.png" alt=""/></dt>
                                            <dd class="infodd">客户姓名：<?=$value['REALNAME']?> [<font style='color:red;'><?=$value['CITY_NAME']?></font>]</dd>
                                            <dd class="infodd">手机号：<?=$value['MOBILENO']?></dd>
                                            <dd class="infodd" style="margin-bottom: 0px">项目名称：<?=$value['PROJECTNAME']?></dd>
                                        </dl>
                                    </a>
                                </div>
                            </li>
                            <?php } ?>
                            <li></li>
                        </ul>
                        <div class="" id="pullUp">
                            <span class="pullUpIcon"></span><span class="pullUpLabel"></span>
                        </div>
                    </div>
                    <input type="hidden" name="action_type" id = "action_type" value='ajax_serach_user_list'>
                    <input type="hidden" name="authcode_key" id = "authcode_key" value="<?=$form_sub_auth_key?>">
                    <input type="hidden" name="truename" id = "truename" value="<?=$truename?>">
                    <input type="hidden" name="telno" id = "telno" value="<?=$telno?>">
                    <input type="hidden" name="page" id = "page" value="1">
                </div>
                <?php }else { ?>
                    <div class="nosercherInfo">
                        <p class="info"> <span class="tanhao"></span>抱歉，系统暂未查到您需要的信息</p>
                    </div>
                <?php } ?>
        <!--如果是获取用户信息-->
        <?php }else if($action_type == 'get_userinfo') { ?>

                <div class="userInfo firstinfo_mt">
                    <a href=""><dl class="infodl clearfix">
                        <dt class="headerdt"><img class="headimg" src="./PUBLIC/IMAGES/header_portrait.png"/></dt>
                        <dd class="infodd">客户姓名：<?=$userinfo['REALNAME']?></dd>
                        <dd class="infodd">手机号：<?=$userinfo['MOBILENO']?></dd>
                        <dd class="infodd" style="margin-bottom: 0px">项目名称：<?=$project_name?></dd>
                    </dl></a>
                </div>
                <div class="vail_cont_div ">
                    <div class="posSystermg remove_posi_l  clearfix">
                        <div class="posSysterm remove_mt  clearfix">
                            <div class="listDiv  clearfix">
                                <label class="label_txt longtxtwid" for="cardstatus">办卡状态</label>
                                <div class="inputDiv">
                                    <select id="cardstatus" class="demo-test-select input_arri" name="cardstatus">
                                        <?php foreach ($card_status as $key => $_val){ ?>
                                        <option value="<?=$key?>" <?=$key==$current_card_status?'selected':''?>><?=$_val?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <!--认购时间-->
                            <div class="listDiv" data-type="cardstatus_2" style="display:none;">
                                <label class="label_txt" for="subscribetime"><em class="c-yel">*</em>认购时间：</label>
                                <div class="inputDiv">
                                    <input class="input_arri demo-test-date" type="date" id="subscribetime" name="subscribetime" value="<?=oracle_date_format($userinfo['SUBSCRIBETIME'])?>">
                                </div>
                            </div>
                            <!--签约时间-->
                            <div class="listDiv" data-type="cardstatus_3" style="display:none;">
                                <label class="label_txt" for="signtime"><em class="c-yel">*</em> 签约日期：</label>
                                <div class="inputDiv">
                                    <input class="input_arri demo-test-date" type="date" id="signtime" name="signtime" value="<?=oracle_date_format($userinfo['SIGNTIME'])?>">
                                </div>
                            </div>

                            <div class="listDiv clearfix">
                                <label class="label_txt longtxtwid" for="receiptstatus">收据状态</label>
                                <div class="inputDiv">
                                    <select id="receiptstatus" class="demo-test-select input_arri" name="receiptstatus">
                                        <?php foreach($receipt_status as $key => $_val){ ?>
                                        <option value="<?=$key?>" <?=$key==$current_receipt_status?'selected':''?>><?=$_val?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="listDiv clearfix">
                                <label class="label_txt longtxtwid" for="invoicestatus">发票状态</label>
                                <div class="inputDiv shortinputwid">
                                    <select id="invoicestatus" class="demo-test-select input_arri" name="invoicestatus">
                                        <?php foreach($invoice_status as $key => $_val){ ?>
                                        <option value="<?=$key?>" <?=$key==$current_invoice_status?'selected':''?>><?=$_val?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="listDiv clearfix">
                                <label class="label_txt longtxtwid" for="looker_mobileno">看房者手机号</label>
                                <div class="inputDiv shortinputwid">
                                    <input class="input_arri" type="text" id="looker_mobileno" name="looker_mobileno"  value ="<?=$userinfo['LOOKER_MOBILENO']?>" placeholder="<?=$userinfo['LOOKER_MOBILENO']?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="confirmDiv"><button class="comfirmbtn" id="changestatusbtn" type="button">确认</button></div>
                <input type="hidden" name="uid" value="<?=$userinfo['ID']?>">
                <input type="hidden" name="action_type" value='update_user_status'>
                <input type="hidden" name="authcode_key" value="<?=$form_sub_auth_key?>">
                <!--日历-->
                <div id="datePlugin">
                </div>
                <div id="selectPlugin">

                    <script type="application/javascript">
                        //根据不同的办卡状态填写不同的时间
                        $("#cardstatus").change(function(){
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


                        $(document).ready(function () {
                            $("#cardstatus").change();
                        });


                        /***
                         * 数据验证
                         * 数据提交
                         */
                        $("#changestatusbtn").live("click",function(){

                            /**基础信息数据验证**/
                            var looker_mobileno = $("#looker_mobileno").val();

                            //电话正则
                            var mobileReg = /^(13[0-9]{1}|145|147|15[0-9]{1}|18[0-9]{1}|17[0-9]{1})[0-9]{8}$/;

                            if(looker_mobileno != '' && !mobileReg.test(looker_mobileno)){
                                alert('请填入正确的看房人手机号！');
                                return false;
                            }

                            //提交操作
                            $.ajax({
                                type: "POST",
                                url: "index.php?s=/Member/changeStatus",
                                data:$('#changestatus_form').serialize(),
                                async: false,
                                dataType:"JSON",
                                success:function(data)
                                {
                                    if(data.status){
                                        alert("更新成功");
                                        location.href = 'index.php?s=/Member/changeStatus';
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

                <?php } ?>
                </div>
</form>

</body>
</html>