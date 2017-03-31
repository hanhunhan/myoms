<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>调整过程表</title>
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

        .menufixed {
            top: 0;
            position: fixed;
        }

        .jbtab th {
            background: none repeat scroll 0 0 #417eb7;
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
    </style>
</head>
<body>
<div class="containter">
    <div class="right fright j-right">
        <form id="search_form" action="__ACTION__" method="post">
            <div class="kctjcon">
                <table cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td align="right">项目名称：&nbsp;</td>
                        <td>
                            <input type="text" id="search_prjname" name="search_prjname" value="<?php echo ($search_prjname); ?>"
                                   class="width190 ac_input"/> <span class="c-red">[逗号分隔，对比查询]</span>
                        </td>
                        <td align="right">是否已决算：&nbsp;</td>
                        <td>
                            <div class="jssel">
                                <select name="search_state">
                                    <?php if(is_array($prjState)): $i = 0; $__LIST__ = $prjState;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($search_state == $key): ?><option value="<?php echo ($key); ?>" selected='selected'><?php echo ($vo); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </td>
                        <td align="right"> &nbsp;</td>
                        <td></td>
                    </tr>
                </table>
                <div class="kctjbot">
                    <input type="submit" class="btn2 sm_blue" value="查 询"/>
                    <input type="submit" class="btn2" style="border:none;cursor:pointer;" name="export" value="导出数据"/>
                </div>
            </div>
            <input type="hidden" id="u" value="<?php echo ($pageurl); ?>&pn=<?php echo ($page); ?>"/>
        </form>
        <div class="contractinfo-table">
            <table>
                <thead>
                <?php echo ($th_str); ?>
                </thead>
                <tbody>
                <?php echo ($data_str); ?>
                </tbody>
            </table>
            <p class="pagenum"><?php echo ($page_nav); ?></p>
        </div>
    </div>
</div>
<style>
    .ui-autocomplete-input {
        background-image: none;
    }
</style>
<script>
    // --- begin 报表搜索项目
    $(function () {
        function split(val) {
            return val.split(/,\s*/);
        }

        function extractLast(term) {
            return split(term).pop();
        }

        $("#search_prjname")
                .bind("keydown", function (event) {
                    if (event.keyCode === $.ui.keyCode.TAB &&
                            $(this).data("ui-autocomplete").menu.active) {
                        event.preventDefault();
                    }
                })
                .autocomplete({
                    source: function (request, response) {
                        var keyword = extractLast(request.term);
                        $.ajax({
                            url: "<?php echo U('Project/asyncGetDSProjects');?>",
                            type: "GET",
                            dataType: "JSON",
                            data: {keyword: keyword},
                            success: function (data) {
                                if (data.status == 'noauth') {
                                    alert(data.msg);
                                    location.reload();
                                } else {
                                    //判断返回数据是否为空，不为空返回数据。
                                    if (data[0]['id'] > 0) {
                                        response(data);
                                    } else {
                                        response(data);
                                    }
                                }
                            }
                        });
                    },
                    minLength: 1,
                    removeinput: 0,
                    select: function (event, ui) {
                        var terms = split(this.value);
                        // 移除当前输入
                        terms.pop();
                        // 添加被选项
                        terms.push(ui.item.value);
                        // 添加占位符，在结尾添加逗号+空格
                        terms.push("");
                        this.value = terms.join(", ");
                        return false;
                    },
                    focus: function () {
                        return false;
                    },
                    close: function (event) {
                    }
                });


    });
    // -- end 报表搜索项目
</script>
</body>
</html>