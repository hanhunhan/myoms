<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
    <head>
        <title>自然到场</title>
        <meta charset="GBK">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>
        <link href="./Public/css/jquery-ui.css" type="text/css" rel="stylesheet"/>
        <script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="./Public/validform/js/Validform_v5.3.2.js"></script>
        <script type="text/javascript" src="./Public/validform/js/common.js"></script>
        <script type="text/javascript" src="./Public/js/common.js"></script>

        <script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>

        <script type="text/javascript" src="./Public/validform/plugin/swfupload/swfuploadv2.2.js"></script>
        <script type="text/javascript" src="./Public/validform/plugin/swfupload/Validform.swfupload.handler.js"></script>
        <script type="text/javascript" src="./Public/js/jquery-ui.js"></script>

        <link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all"/>
        <link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all"/>
        <link rel="stylesheet" href="./Public/css/report.css" type="text/css" media="all"/>
        <style>
            html {
                overflow: auto;
            }

            .jbtab th {
                background: none repeat scroll 0 0 #349ac0;
                color: #fff;
                padding: 5px 0;
            }

            .jbtab td {
                border: 1px solid #e1e1e1;
                padding: 10px 0;
            }

            .kctjbot input {
                height: 30px;
                line-height: 30px;
            }

            .smalltext {
                text-align: right;
                color: brown;
                float: right;
                padding-right: 10px;
            }

            .bdtj{
                font-size:14px ;
                line-height: 50px;;
            }

            .bdtj select{
                width: 200px;
                height:28px ;
            }
            .sm_blue{
                color:white;
                background-color: #349ac0;
            }
        </style>
    </head>
    <body>
    <script type="text/javascript">
        /**隔行变色**/
        function changColor(){
            var table_Element = document.getElementById("table_style");
            var tr_Element = table_Element.rows;
            for(var i=0;i<tr_Element.length;i++){
                if(i%2==0)
                {
                    if(tr_Element[i].className)
                        tr_Element[i].className += " bgf0";
                    else
                        tr_Element[i].className = "bgf0";
                }else{
                    if(tr_Element[i].className)
                        tr_Element[i].className += " bgfff";
                    else
                        tr_Element[i].className = "bgfff";
                }
            }
        };

        /**当页面加载时执行**/
        window.onload = function (){
            changColor();
        };

        function show_statics_details(city_id, start_day, end_day)
        {
            var url = "<?=U('RTStatistics/city_pro_detail') . '&show_details=1'?>";
            url = url + "&city_id=" +city_id+'&start_day='+start_day+'&end_day='+end_day;
            $('.iframePop').attr('src', url);
        }
    </script>

    <div class="containter">
        <div class="right fright j-right">
            <?php  if($show_details == 0) { ?>
            <form id="search_form" action="__ACTION__" method="post">
                    <table cellpadding="0" cellspacing="0" width="100%" class="bdtj">
                        <tr>
                            <td align="right">时间段：&nbsp;</td>
                            <td>
                                    <input type="text" name="search_btime" id="search_btime" class="width105"
                                           onfocus="WdatePicker({dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true})"
                                           value="<?php echo ($start_day); ?>"/>
                                &nbsp;-&nbsp;
                                    <input type="text" name="search_etime" id="search_etime" class="width105"
                                           onfocus="WdatePicker({dateFmt:'yyyy-MM-dd',alwaysUseStartDate:true})"
                                           value="<?php echo ($end_day); ?>"/>
                            </td>
                        </tr>
                    </table>
                    <div class="kctjbot">
                        <input type="hidden" name="act" value="search" />
                        <input type="submit" class="btn139 sm_blue" value="查 询" />
                    </div>
                    <div class="contractinfo-table">
                        <?=$tbl_str?>
                    </div>
            </form>
            <div id="js_genjin" style="width:900px;height:600px;margin-top:50px;margin-left:auto;margin-right: auto;">
                <iframe frameborder="0" scrolling="true" width="900" height="600" class='iframePop' src="<?=U('RTStatistics/city_pro_detail') . '&show_details=1'?>"></iframe>
            </div>
            <?php }else{ ?>
            <div class="contractinfo-table">
            <?=$tbl_str?>
            </div>
            <?php } ?>
        </div>
    </div>
    </body>
</html>