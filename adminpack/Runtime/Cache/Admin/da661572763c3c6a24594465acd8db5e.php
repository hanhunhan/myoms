<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=gb2312"/>
    <title>添加权限组</title>
    <!--select 2 style-->
    <link rel="stylesheet" href="./Public/select2/select2.css" type="text/css" media="all"/>
    <link href="Public/third/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel=stylesheet href="./Tpl/css/global.css" type="text/css">
    <link href="./Public/css/style2.css" type="text/css" rel="stylesheet"/>
    <link rel=stylesheet href="./Tpl/css/mainPage.css" type="text/css">
    <!--<script type="text/javascript" src="./Tpl/js/jquery-1.5.2.min.js"></script>-->
    <script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
    <!--select2 js-->
    <script type="text/javascript" src="./Public/select2/select2.js"></script>
    <style type="text/css">
        .table2 th {
            background: none repeat scroll 0 0 #349ac0;
            color: #fff;
            padding: 5px 10px;
        }

        .menu-index {
            /*border: 1px solid #ccc;*/
            padding: 10px 20px 0 10px;
        }

        .menu-index li {
            padding: 5px;
        }

        .menu-index li > a {
            color: #00f;
        }

        .back-top, .submit-box {
            position: fixed;
            right: 40px;
            bottom: 40px;
            width: 80px;
            height: 40px;
            background: #0066cc;
            border: 2px solid #efefef;
            border-radius: 5px;
            line-height: 34px;
            text-align: center;
        }

        .submit-box {
            right: 140px;
        }

        .submit-box > button {
            border: none;
            color: #fff;
            width: 80px;
            height: 37px;
            line-height: 37px;
            background: #005916;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-top > a {
            color: #fff;
            display: block;
            text-decoration: none;
            width: 80px;
            height: 40px;
        }

        h4 > a {
            color: #f00;
        }
    </style>
    <script type="text/javascript">
        function selectAll(obj) {
            var status = $(obj).prop("checked");
            var t = $(obj).parent().parent().parent();
            t.find("input[name^='role']").prop("checked", status);//全选按钮
            t.find("input[name^='tgroup']").prop("checked", status);//全选按钮
        }

        function selectrole(obj, id) {
            var status = $(obj).prop("checked");
            var t = $(obj).parent().parent().next();
            t.find("input[name^='role']").prop("checked", status);

            $(obj).parent().prev().find("input[name^='role']").prop("checked", status);

            var tag = true;
            var tGroups = $('#menu' + id).parent().parent().next().find("input[name^='tgroup']");
            for (var i = 0; i < tGroups.length; i++) {
                if ($(tGroups[i]).prop('checked') == false) {
                    tag = false;
                    break;
                }
            }
            $('#menu' + id).prop("checked", tag)
        }

        function Is_select(id) {
            var tag = true;
            $('#c' + id).find("input[name^='role']").each(function () {
                if ($(this).prop("checked") == false) tag = false;
            })


            var status = $('#t' + id).find("input[name^='role']").prop("checked");
            if (status == false) tag = false;

            $('#t' + id).find("input[name^='tgroup']").prop("checked", tag)

            tag = true;
            $('#t' + id).parent().parent().find("input[name^='tgroup']").each(function () {
                if ($(this).prop("checked") == false) tag = false;
            })

            $('#t' + id).parent().parent().prev().find("input[name^='menu']").prop("checked", tag);

        }

        function check() {
            var groupName = $('#groupName').val();
            groupName = groupName.replace(/^\s+|\s+$/, '');
            var cityId = $('#cityId').val();
            if (groupName == '') {
                alert('用户组名称不能为空！');
                return false;
            }
            if (cityId == '') {
                alert('请选择城市！');
                return false;
            }
            var tag = true;
            $("input[name^='role']").each(function () {
                if ($(this).prop("checked") == true) tag = false;
            });
            if (tag) {
                alert('请勾选功能选项！');
                return false;
            }

        }

        function delgroup(id) {
            if (confirm('你确定要删除该权限组吗？')) {
                var link = "<?php echo U('Group/deleteGroup?id=');?>" + id
                location.href = link;
            }
        }

        $(function () {
            $('#LOAN_GROUPID').select2();

            $('.limits').each(function () {
                var tag1 = true;
                $(this).find('.itemManage-list').each(function () {
                    var tag2 = true;
                    var t = $(this).find(".con-t input[name^='role']").prop("checked");
                    if (t == false) tag2 = false;
                    $(this).find(".con-c input[name^='role']").each(function () {
                        if ($(this).prop("checked") == false) tag2 = false;
                    })
                    $(this).find(".con-t input[name^='tgroup']").prop("checked", tag2);
                })

                $(this).find(".itemManage-list input[name^='tgroup']").each(function () {
                    if ($(this).prop("checked") == false) tag1 = false;
                })

                $(this).find(".titles input[name^='menu']").prop("checked", tag1);
            });
        })
    </script>
</head>

<body>
<div class="containter">
    <div class="right fright j-right">
        <div class="handle-tab">
            <ul>
                <li class="selected"><a href="<?php echo U('Group/viewGroup');?>">权限管理</a></li>
            </ul>
        </div>
        <div class="text-center">
            <form class="form-inline" method="POST" action="<?php echo U('Group/searchByName');?>">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon" for="LOAN_GROUPID">权限组名称</div>
                        <select name="LOAN_GROUPID" id="LOAN_GROUPID" class="form-control text-center">
                            <option value="-1">请选择</option>
                            <?php if(is_array($all_group)): foreach($all_group as $key=>$voGroup): ?><option value="<?php echo ($voGroup["LOAN_GROUPID"]); ?>"><?php echo ($voGroup["LOAN_GROUPNAME"]); ?></option><?php endforeach; endif; ?>
                        </select>
                    </div>

                </div>
                <button type="submit" class="btn btn-primary">搜索</button>
            </form>
        </div>
        <div class="box" id="addLimits">
            <div class="box-con">
                <div class="add-info">
                    <table class="table2" width="100%">
                        <tr>
                            <th>编号</th>
                            <th>权限组名称</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>修改时间</th>
                            <th><a class="btn btn-default btn-sm" href="<?php echo U('Group/addGroup');?>/mod/add">添加权限组</a></th>
                            <?php if(is_array($re)): $i = 0; $__LIST__ = $re;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                                    <td><?php echo ($vo["LOAN_GROUPID"]); ?></td>
                                    <td><?php echo ($vo["LOAN_GROUPNAME"]); ?></td>
                                    <td>
                                        <?php if($vo["LOAN_GROUPSTATUS"] == 1): ?>激活
                                            <?php elseif($vo["LOAN_GROUPSTATUS"] == 0): ?>
                                            <font style="color:red">非激活</font><?php endif; ?>
                                    </td>
                                    <td><?php echo (date('Y-m-d H:i',$vo["LOAN_GROUPCREATED"])); ?></td>
                                    <td><?php echo (date('Y-m-d H:i',$vo["LOAN_GROUPUPDATED"])); ?></td>
                                    <td style="text-align: center;"><a class="btn btn-danger btn-xs"
                                                                       href="<?php echo U('Group/editGroup?id='.$vo['LOAN_GROUPID']);?>"
                                                                       title="修改"><i
                                            class="glyphicon glyphicon-edit"></i></a></td>
                                </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    </table>
                    <div class="cut-page"><span><?php echo ($page); ?></span></div>
                </div>

                <?php if($act == 'add' OR $act == 'edit'): ?><form method="post" action="<?php echo ($action); ?>" onsubmit="return check()">
                        <input type="hidden" name="act" value="<?php echo ($act); ?>">
                        <input type="hidden" name="id" value="<?php echo ($group['LOAN_GROUPID']); ?>">
                        <a name="top"></a>

                        <div class="box-b">
                            <div class="hd"><h3>用户组</h3></div>
                            <div class="center-btn mt10" style="text-align: right"><input type="submit" value="确定"
                                                                                          class="btn-a mr50"/><input
                                    name="重置"
                                    type="reset"
                                    class="btn-a"
                                    value="重填"/>
                            </div>
                            <div class="bd">
                                <div class="limits">
                                    <div class="add-info">
                                        <label class="mr10"><em class="xing">*</em>权限组名称：<input type="text"
                                                                                                class="input-text"
                                                                                                name="groupName"
                                                                                                id="groupName"
                                                                                                value="<?php echo ($group["LOAN_GROUPNAME"]); ?>"/></label>
                                        <span style="color: red">类型</span>：
                                        <label class="mr10">
                                            <input type="radio" name="bases" value="1"
                                            <?php if($group[LOAN_BASE] == 1): ?>checked<?php endif; ?>
                                            >高级
                                        </label>
                                        <label>
                                            <input type="radio" name="bases" value="0"
                                            <?php if($group[LOAN_BASE] != 1): ?>checked<?php endif; ?>
                                            >普通
                                        </label>

                                        | <span style="color: red">状态</span>：<label class="mr10"><input name="status"
                                                                                                        type="radio"
                                                                                                        value="1"
                                        <?php if($group[LOAN_GROUPSTATUS] == 1): ?>checked<?php endif; ?>
                                        />激活</label>
                                        <label class="mr10"><input name="status" type="radio" value="0"
                                            <?php if($group[LOAN_GROUPSTATUS] == 0): ?>checked<?php endif; ?>
                                            />未激活</label>
                                        | <span style="color: red">项目权限</span>：
                                        <label class="mr10">
                                            <input type="radio" name="auth" value="1"
                                            <?php if($group[LOAN_GROUPALL] == 1): ?>checked<?php endif; ?>
                                            >全部
                                        </label>
                                        <label>
                                            <input type="radio" name="auth" value="0"
                                            <?php if($group[LOAN_GROUPALL] != 1): ?>checked<?php endif; ?>
                                            >自己
                                        </label>
                                        | <span style="color: red">查看会员</span>：
                                        <label class="mr10">
                                            <input type="radio" name="LOAN_VMEM" value="1"
                                            <?php if($group[LOAN_VMEM] == 1): ?>checked<?php endif; ?>
                                            >全部
                                        </label>
                                        <label>
                                            <input type="radio" name="LOAN_VMEM" value="0"
                                            <?php if($group[LOAN_VMEM] != 1): ?>checked<?php endif; ?>
                                            >自己
                                        </label>

                                    </div>
                                </div>
                                <ul class="menu-index">
                                    <?php if(is_array($propy)): $i = 0; $__LIST__ = $propy;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><li><a href="#menu_anchor_<?php echo ($menu["menuID"]); ?>"><?php echo ($i); ?>. <?php echo ($menu["menuName"]); ?></a></li><?php endforeach; endif; else: echo "" ;endif; ?>
                                </ul>

                                <?php if(is_array($propy)): $i = 0; $__LIST__ = $propy;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menu): $mod = ($i % 2 );++$i;?><div class="limits">
                                        <div class="titles"><h4><a
                                                name="menu_anchor_<?php echo ($menu["menuID"]); ?>"><?php echo ($menu["menuName"]); ?></a></h4>
                                            <label><input id="menu<?php echo ($menu["menuID"]); ?>" name="menu<?php echo ($menu["menuID"]); ?>"
                                                          type="checkbox"
                                                          onclick="selectAll(this)"/>全选</label></div>
                                        <div class="con">
                                            <?php if(is_array($menu['smenu'])): $i = 0; $__LIST__ = $menu['smenu'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$smenu): $mod = ($i % 2 );++$i;?><div class="itemManage-list">
                                                    <div class="con-t" id="t<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>">
                                                        <?php if($smenu['smenuval']['loan_status'] == 1): ?><h5><input type="checkbox" name="rolemain[]" checked
                                                                       onclick="Is_select(<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>)"
                                                                       value="<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>"><?php echo ($smenu['smenuval']['LOAN_ROLENAME']); ?>
                                                            </h5><label>
                                                            <?php else: ?>
                                                            <h5><input type="checkbox" name="rolemain[]"
                                                                       onclick="Is_select(<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>)"
                                                                       value="<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>"><?php echo ($smenu['smenuval']['LOAN_ROLENAME']); ?>
                                                            </h5><label><?php endif; ?>

                                                        <input name="tgroup<?php echo ($menu["menuID"]); ?>"
                                                               onclick="selectrole(this,<?php echo ($menu["menuID"]); ?>)"
                                                               type="checkbox"/>全选</label>
                                                    </div>
                                                    <div class="con-c" id="c<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>">
                                                        <?php if(is_array($smenu['sroleval'])): $i = 0; $__LIST__ = $smenu['sroleval'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$role): $mod = ($i % 2 );++$i; if($role['loan_status'] == 1): ?><label><input
                                                                        name="role<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>[]"
                                                                        type="checkbox" checked
                                                                        value="<?php echo ($role[LOAN_ROLEID]); ?>"
                                                                        onclick="Is_select(<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>)"/><?php echo ($role['LOAN_ROLENAME']); ?></label>
                                                                <?php else: ?>
                                                                <label><input
                                                                        name="role<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>[]"
                                                                        type="checkbox" value="<?php echo ($role[LOAN_ROLEID]); ?>"
                                                                        onclick="Is_select(<?php echo ($smenu['smenuval']['LOAN_ROLEID']); ?>)"/><?php echo ($role['LOAN_ROLENAME']); ?></label><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                                                    </div>
                                                </div><?php endforeach; endif; else: echo "" ;endif; ?>
                                        </div>
                                    </div><?php endforeach; endif; else: echo "" ;endif; ?>

                                <div class="center-btn mt10" style="text-align: right"><input type="submit" value="确定"
                                                                                              class="btn-a mr50"/><input
                                        name="重置" type="reset" class="btn-a" value="重填"/></div>
                                <div class="back-top"><a href="#top">返回顶部</a></div>
                                <div class="submit-box">
                                    <button type="submit">确定</button>
                                </div>
                            </div>
                        </div>
                    </form><?php endif; ?>

            </div>
        </div>
    </div>
</div>

</body>
</html>