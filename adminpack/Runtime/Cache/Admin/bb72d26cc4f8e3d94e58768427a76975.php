<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title>数据发生表</title>
    <meta charset="GBK">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
	 <link href="Public/third/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./Public/css/jquery-ui.css" type="text/css" rel="stylesheet"/>

    <script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
 
 

    <script language="javascript" type="text/javascript" src="./Public/My97DatePicker/WdatePicker.js"></script>

     
    <script type="text/javascript" src="./Public/js/jquery-ui.js"></script>
<script src="Public/third/bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./Public/validform/css/style.css" type="text/css" media="all"/>
    <link rel="stylesheet" href="./Public/validform/css/validateForm.css" type="text/css" media="all"/>
    <link rel="stylesheet" href="./Public/css/report.css" type="text/css" media="all"/>
    <style>
        html {
            overflow: auto;
        }

        

		.maintable table td{
		word-break:keep-all;
		padding:5px 10px;
		text-align:center;
		vertical-align:middle!important;
		}
		.maintable table thead {
			color: #fff;
			background-color: #3c8dbc !important;
			white-space: nowrap;
		}
		.formtable td{
		padding:10px 50px;
		}
		 
    </style>
    <script type="text/javascript">

    </script>

</head>
<body>

<div class="containter">
    <div class="right fright j-right">
        <form id="search_form" action="__ACTION__" method="post" class="form-inline" role="form">
            
                    <table class="formtable">
					<tr><td>
						<div class="form-group">
						 <label  for="search_prjname">项目名称：</label>
                            <input type="text" id="search_prjname" name="search_prjname" value="<?php echo ($search_prjname); ?>"
                                   class="form-control"/> <span class="c-red">[逗号分隔，对比查询]</span>
						</div>
						</td>
						<td>
                        
							<div class="form-group">
							 <div class="data">
							 <label   for="search_btime">开始时间：</label>
                           
                                <input type="text" name="search_btime" id="search_btime" class="form-control"
                                       onfocus="WdatePicker({dateFmt:'yyyy-MM',alwaysUseStartDate:true})"
                                       value="<?php echo ($search_btime); ?>"/>
                            </div>
							</div>
						</td>
						<td>
							<div class="form-group">
							 <div class="data">
							 <label   for="search_etime">结束时间：</label>
                           
                                <input type="text" name="search_etime" id="search_btime" class="form-control"
                                       onfocus="WdatePicker({dateFmt:'yyyy-MM',alwaysUseStartDate:true})"
                                       value="<?php echo ($search_btime); ?>"/>
                            </div>
							</div>

						</td>
						</tr>
						<tr>

						<td>
                        <div class="form-group">
								 <label   for="search_state">是否已决算：</label>
                                <select name="search_state" class="form-control">
                                    <?php if(is_array($prjState)): $i = 0; $__LIST__ = $prjState;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($search_state == $key): ?><option value="<?php echo ($key); ?>" selected='selected'><?php echo ($vo); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                </select>
								</div>
                        </td>
						<td>
                           <div class="form-group">
								 <label  for="isfundpool">是否资金池项目：</label>
                                <select name="isfundpool"  class="form-control">
                                    <?php if(is_array($prj_isfundpool)): $i = 0; $__LIST__ = $prj_isfundpool;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($isfundpool == $key): ?><option value="<?php echo ($key); ?>" selected='selected'><?php echo ($vo); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                        </td>
						<td>
                            <div class="form-group">
							<label   for="coststate">费用类别：</label>

                                <select name="coststate" class="form-control">
                                    <?php if(is_array($cost_state)): $i = 0; $__LIST__ = $cost_state;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i; if($coststate == $key): ?><option value="<?php echo ($key); ?>" selected='selected'><?php echo ($vo); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($key); ?>"><?php echo ($vo); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                </select>
                            </div>
                       </td>
						</tr>
						</table>
                  
				  <div style="width:300px; padding:10px; margin:0 auto; ">
				   <button type="submit" class="btn btn-default btn-sm">
					  <span class="glyphicon glyphicon-search"></span> 查 询"
					</button>
                     &nbsp; &nbsp; &nbsp; &nbsp;
                    
					 <button type="submit" class="btn btn-default btn-sm"  name="export" >
					  <span class="glyphicon glyphicon-export"></span> 导出数据
					</button>
				 
                </div>
             
            <input type="hidden" id="u" value="<?php echo ($pageurl); ?>&pn=<?php echo ($page); ?>"/>
        </form>

        <div  class="maintable" >
            <table  class="table table-hover table-bordered table-striped">
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