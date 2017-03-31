<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=gbk"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1,minimum-scale=1,user-scalable=no">
<link href="Public/third/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="Public/uploadify/uploadify.css" rel="stylesheet">
<link href="Public/css/jquery-ui.css" type="text/css" rel="stylesheet"/>

<!--[if lt IE 9]>
<script src="./Public/js/html5shiv.min.js"></script>
<script src="./Public/js/respond.min.js"></script>
<![endif]-->

<style>
    body {
        font-family: Microsoft YaHei, Verdana, helvetica, arial, sans-serif;
        background-color: #f9f9f9;
        /*padding-bottom: 300px;*/
    }

    ul {
        list-style: none;
    }

    .main {
        padding-bottom: 80px;
    }

    .main .container {
        max-width: 970px!important;
    }

    .custom-btn-group {
        padding: 12px 15px;
    }

    .custom-btn-group .btn {
        padding: 10px 0;
        margin-bottom: 20px;
    }

    .four-width {
        width: 22%;
    }

    .apply-info {
        border: 1px solid #ccc;
    }

    .block {
        /*-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.25);*/
        /*box-shadow: 0 1px 3px rgba(0,0,0,0.25);*/
        padding: 4px;
    }

    .block-title {
        border-bottom: 1px solid #24b9e7;
        /*background-color: #f1f1f1;*/
        padding: 5px 0;
        font-size: 14px;
        font-weight: bold;
        color: #777;
        margin-bottom: 15px;
    }

    .panel .list-group-item label {
        /*border: 1px solid #f00;*/
        width: 130px;
        /*text-align: right;*/
        color: #777;
        font-weight: normal;

    }

    .catalog-button {
        width: 40px;
        height: 40px;
        border-radius: 2px;
        line-height: 40px;
        text-align: center;
        background: #f00;
        /*background: #ff7300;*/
        color: #fff;
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 999;
        font-weight: normal;
        font-size: 24px;
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1001;
        height: 100%;
        background: #000;
        opacity: .7;
        filter: alpha(opacity=70);
        -moz-opacity: 0.7;
        overflow: hidden;
    }

    .catalog {
        display: none;
        position: fixed;
        top: 0;
        width: 65%;
        right: -65%;
        /* height: 100%; */
        background: #fff;
        z-index: 1002;
        display: -webkit-box;
        display: -webkit-flex;
        display: -ms-flexbox;
        display: flex;
        -webkit-box-direction: normal;
        -webkit-box-orient: vertical;
        -webkit-flex-direction: column;
        -ms-flex-direction: column;
        flex-direction: column;
        color: #f00;
    }

    .noscroll {
        /*position: fixed;*/
        overflow: hidden !important;
    }

    .fixed-position {
        position: fixed;
    }

    .relative-position {
        position: relative;
    }

    /* General styles for all menus */
    .cbp-spmenu {
        background: #47a3da;
        position: fixed;
    }

    .cbp-spmenu h3 {
        color: #afdefa;
        font-size: 1.9em;
        padding: 20px;
        margin: 0;
        font-weight: 300;
        background: #0d77b6;
    }

    .cbp-spmenu a {
        display: block;
        color: #fff;
        font-size: 1.1em;
        font-weight: 300;
    }

    .cbp-spmenu a:hover {
        background: #258ecd;
    }

    .cbp-spmenu a:active {
        background: #afdefa;
        color: #47a3da;
    }

    /* Orientation-dependent styles for the content of the menu */

    .cbp-spmenu-vertical {
        width: 240px;
        height: 100%;
        top: 0;
        right: 0;
        z-index: 1002;
        max-width: 240px !important;
    }

    .cbp-spmenu-vertical a {
        border-bottom: 1px solid #258ecd;
        padding: 1em;
        text-decoration: none;
    }

    /* Vertical menu that slides from the left or right */

    .cbp-spmenu-left {
        left: -240px;
    }

    .cbp-spmenu-right {
        right: -240px;
        /*display: none;*/
    }

    .cbp-spmenu-left.cbp-spmenu-open {
        left: 0px;
    }

    .cbp-spmenu-right.cbp-spmenu-open {
        right: 0px !important;
    }

    /* Transitions */

    .cbp-spmenu,
    .cbp-spmenu-push {
        /*-webkit-transition: all 0.3s ease;*/
        /*-moz-transition: all 0.3s ease;*/
        /*transition: all 0.3s ease;*/
    }

    .users {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1002;
        background: #fff;
        padding: 60px 0 60px;
        overflow-y: scroll;
    }

    .users .panel {
        margin-bottom: 0;
        border-radius: 0;
        border-bottom: 0;
    }

    .users .users-body {
        position: static;
        padding-bottom: 60px;
    }

    .users .panel .panel-heading {
        padding: 6px 10px;
    }

    .users-header {
        position: fixed;
        z-index: 1005;
        background: #fff;
        /*border: solid 1px #ccc;*/
        left: 0px;
        right: 0px;
        top: 0px;
        width: 100%;
        height: 53px;
        vertical-align: middle !important;
        text-align: center;
        padding: 10px 5px;
        border-bottom: 1px solid #eee;
    }

    .users-footer {
        position: fixed;
        z-index: 1005;
        background: #eee;
        /*border: solid 1px #ccc;*/
        left: 0px;
        right: 0px;
        bottom: 0px;
        width: 100%;
        height: 53px;
        vertical-align: middle !important;
        text-align: center;
        padding: 10px 5px;
        border-top: 1px solid #ddd;
    }

    .search-box {
        margin: 0 auto;
        padding: 0 10px;
    }

    .search-box .search-box-text {
        width: 100%;
        height: 34px;
        border-radius: 3px;
        border: 1px solid #ccc;
        padding: 3px 6px;
        outline: none;
        display: block;
        margin-right: 3px;
    }

    .button-box {
        margin: 0 auto;
        padding: 0px 10px;
    }

    .clearfix:after {
        content: ".";
        display: block;
        height: 0;
        clear: both;
        visibility: hidden;
    }

    .selected-user {
        /*background: url('./Public/images/selected-user.jpg') no-repeat center;*/
        /*background-size: 15px 15px;*/
    }

    .users-footer .select-result {
        padding: 6px 12px;
    }

    .panel-opinion ul.selected-user-list {
        /*border: 1px solid #f00;*/
        list-style: none;
        padding: 0;
        margin-top: 10px;
    }

    .panel-opinion ul.selected-user-list li {
        /*border: 1px solid #0f0;*/
        /*border: 1px solid #f00;*/
        color: #31708f;
        background-color: #d9edf7;
        border-color: #bce8f1;
        padding: 8px 10px;
        margin-bottom: 5px;
        border-radius: 4px;
    }

    .selected-user-list li .close {
        right: 0;
        position: relative;
        top: -3px;
        float: right;
        font-size: 21px;
        font-weight: 700;
        line-height: 1;
        color: #000;
        text-shadow: 0 1px 0 #fff;
        filter: alpha(opacity=20);
        opacity: .2;
    }

    ul.attachment-list {
        /*border: 1px solid #f00;*/
        padding: 0;
    }

    ul.attachment-list li {
        /*border: 1px solid #0f0;*/
        margin: 5px;
        padding: 8px 10px;
    }

    .flow-graph-ul {
        /*border: 1px solid #f00;*/
        border-left: 1px solid #ddd;
        margin: 25px 0 10px 15px;
        padding: 0 0 0 10px;
    }

    .flow-graph-ul li {
        position: relative;
        margin-bottom: 30px;
        padding-right: 10px;
    }

    .flow-graph-ul li:before {
        content: '';
        position: absolute;
        background: #fff;
        height: 13px;
        width: 13px;
        left: -17px;
        top: 3px;
        border: 2px solid #ff8400;
        border-radius: 50%;
    }

    .flow-graph-ul li blockquote {
        border: none;
    }

    .flow-graph-ul li blockquote > p {
        font-size: 14px;
        line-height: 2;
    }

    .flow-graph-ul li dl {

    }

    .flow-graph-ul li dt, .flow-graph-ul li dd {
        padding: 3px 0;
    }

    .text-gray {
        color: #777;
    }

    .font-sm {
        font-size: 13px;
    }

    .list-group-item > label {
        /*border: 1px solid #f00;*/
        position: absolute;
        top: 15px;
        width: 40%;
        height: 100%;
        max-width: 180px;
    }

    .list-group-item .label_max {
        max-width: 180px;
    }

    .list-group-item > .content {
        padding: 5px;
        margin-left: 150px;
        word-wrap: break-word;
    }

    .text-red {
        color: #f00;
    }

    .panel-primary > .panel-heading {
        color: #fff;
        background-color: #3c8dbc;
        border-color: #3c8dbc;
    }

    .table-responsive > .table > thead > tr > th,
    .table-responsive > .table > tbody > tr > td {
        white-space: nowrap;
    }

    .table-title {
        width: 120px;
        color: #777;
    }

    .ps-text {
        /*border: 1px solid #f00;*/
        margin-top: -15px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #777;
        text-align: right;
        padding-right: 5px;
    }

    .uploadify-button {
        border-radius: 4px;
        -webkit-border-radius: 4px;
        /*padding: 8px 12px;*/
        padding-bottom: 8px;
        line-height: 1.4;
        max-width: 120px;
        border-width: 1px;
    }

    .layermbox0 .layermchild {
        min-width: 220px!important;
    }

    .layermchild h3 {
        margin-top: 0;
        margin-bottom: auto;
        overflow: auto;
    }

    .layermbtn {
        overflow: hidden;
    }

    .layermbtn span:first-child {
        height: 40px!important;
    }

    .layermcont {
        line-height: 2!important;
    }
</style>

<?php if(empty($isMobile)): ?><style>
        body {

        }

        .panel {
            /*width: 800px;*/
            /*margin: 0 auto 20px;*/
            border-radius: 0;
        }

        .panel .panel-heading {
            border-radius: 0;
        }

        .users-header {
            padding-right: 20px;
        }

        .users .panel {
            width: 600px;
            margin: 0 auto;
        }

        .users-header .search-box {
            width: 600px;
            margin: 0 auto;
        }

        .users-footer {
            padding-right: 20px;
        }

        .users-footer .button-box {
            width: 600px;
            margin: 0 auto;
        }

        .catalog-button {
            bottom: 60px;
            right: 60px;
            width: 60px;
            height: 60px;
            line-height: 60px;
            text-align: center;
            cursor: pointer;
        }

        .head .container {
            /*width: 600px;*/
            /*margin: 0 auto;*/
        }

        .jumbotron {
            padding-top: 20px;
            padding-bottom: 48px;
            position: fixed;
            width: 100%;
            z-index: 1000;
            top: 0;
        }

        .main {
            padding-top: 175px;
        }

        /* begin 审批意见的按钮样式*/
        .panel-opinion .custom-btn-group {
            /*border: 1px solid #f00;*/
            /*text-align: center;*/
        }

        .panel-opinion .btn-danger {
            display: inline-block;
            max-width: 120px;
            vertical-align: baseline;
            margin-right: 30px;
            padding: 6px 0;
        }

        .panel-opinion .btn-success {
            max-width: 300px;
        }

        .ps-text {
            display: none;
        }

        .head .container {
            position: relative;
            /*border: 1px solid #a00;*/
        }

        .head .container .back-btn-box {
            /*border: 1px solid #f00;*/
            position: absolute;
            right: 20px;
            top: 30px;
        }

        body.stop-scrolling {
            height: auto;
            overflow: auto;
        }

        .back-btn-box a {
            margin-left: 5px;
        }

        /* end 审批意见的按钮样式*/

        .panel-opinion ul.selected-user-list {
            margin-top: 0;
            margin-bottom: 10px;
        }

        input#COPY_USER_INPUT {
            margin-bottom: 10px;
        }
    </style>
    <style type="text/css" media="print">
        p {
            font-size: 11px;
        }

        .head.jumbotron {
            padding-top: 0;
        }

        .main {
            padding-top: 100px;
        }

        .panel {
            margin-bottom: 10px;
        }

        .panel .list-group-item {
            font-size: 13px !important;
            padding: 0 15px;
        }

        .panel .panel-heading {
            padding: 3px 15px;
            font-size: 11px;
        }

        .panel .panel-body {
            padding: 3px 15px;
            font-size: 11px;
        }

        .panel h3 {
            margin-top: 4px;
        }

        .panel .list-group-item label {
            top: 3px;
        }

        table tbody {
            overflow: hidden;
        }

        table th, table td {
            font-size: 9px;
            padding: 3px;
            line-height: 1.1;
        }

        .table > tbody > tr > td {
            padding: 0px !important;
        }

        .panel .label {
            font-size: 11px;
        }

        .flow-graph-ul li {
            margin-bottom: 0;
        }

        .flow-graph-ul footer {
            font-size: 11px;
        }

        .flow-graph-ul blockquote {
            padding: 10px;
            margin-bottom: 5px;
        }
    </style><?php endif; ?>
    <title><?php echo ($title); ?></title>
</head>
<body>

    <div class="head jumbotron">
        <div class="container">
            <h3><?php echo ($projectName); ?></h3>
            <span class="label label-danger" <?php echo ($labelStyle); ?>><?php echo ($flowTypeText); ?></span>
            <small><?php echo ($applicationTime); ?></small>
            <?php if(empty($isMobile)): ?><div class="back-btn-box">
        <?php if(!empty($bizWebEditable)): switch($flowType): case "projectset": case "projectchange": case "dulihuodong": case "dulihuodongbiangeng": case "xiangmuxiahuodong": case "xiangmuxiahuodongbiangeng": ?><a href="#" class="btn btn-primary btn-edit-biz"><i class="glyphicon glyphicon-edit"></i>&nbsp;编辑</a><?php break;?>
                <?php default: endswitch; endif; ?>
        <?php if($menu['opinion'] == 0): ?><a href="#" class="btn btn-primary btn-print-flow"><i class="glyphicon glyphicon-print"></i>&nbsp;打印</a>
            <a href="#" class="btn btn-warning btn-back-global" data-type="history" data-history="<?php echo ($_SERVER['HTTP_REFERER']); ?>">返回</a>
            <?php else: ?>
            <a href="#" class="btn btn-warning btn-back-global" data-type="parent" data-history="<?php echo ($_SERVER['HTTP_REFERER']); ?>">返回</a><?php endif; ?>
    </div><?php endif; ?>
        </div>
    </div>

    <div class="main">
        <div class="container">
            <!--申请说明-->
            <?php if(!empty($menu['application'])): ?><div class="panel panel-primary">
        <a name="application"></a>
        <div class="panel-heading"><i class="glyphicon glyphicon-comment"></i>&nbsp;申请说明</div>
        <div class="panel-body">
            <p><?php echo ($application['INFO']); ?></p>
            <p class="text-right">申请人：<?php echo ($application['USER_NAME']); ?></p>
        </div>
    </div><?php endif; ?>
<script>
    JSON.stringify()
</script>

            <!--采购详情-->
            <div class="panel panel-primary">
                <a name="purchase-detail"></a>
                <div class="panel-heading"><i class="glyphicon glyphicon-list-alt"></i>&nbsp;采购详情</div>
                <ul class="list-group">
                    <?php if(is_array($require)): $i = 0; $__LIST__ = $require;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?><li class="list-group-item"><label><?php echo ($item['alias']); ?></label><div class="content"><?php echo ($item['val']); ?>&nbsp;</div></li><?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </div>

            <!--采购明细-->
            <div class="panel panel-primary">
                <a name="purchase-list"></a>
                <div class="panel-heading"><i class="glyphicon glyphicon-th"></i>&nbsp;采购明细</div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">序号</th>
                            <th class="text-center">品牌</th>
                            <th class="text-center">采购型号</th>
                            <th class="text-center">品名</th>
                            <th class="text-center">指定采购人</th>
                            <th class="text-center">采购发起人</th>
                            <th class="text-center">单价最高限价</th>
                            <th class="text-center">申请数量</th>
                            <th class="text-center">合计金额</th>
                            <th class="text-center">费用类型</th>
                            <th class="text-center">是否资金池费用</th>
                            <th class="text-center">是否扣非</th>
                            <?php if(!empty($bizWebEditable)): ?><th class="text-center">操作</th><?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$bItem): $mod = ($k % 2 );++$k;?><tr>
                                <td class="text-center"><?php echo ($k); ?></td>
                                <td class="text-center"><?php echo ($bItem['BRAND']); ?></td>
                                <td class="text-center"><?php echo ($bItem['MODEL']); ?></td>
                                <td class="text-center"><?php echo ($bItem['PRODUCT_NAME']); ?></td>
                                <td class="text-center"><?php echo ($bItem['P_NAME']); ?></td>
                                <td class="text-center"><?php echo ($bItem['APPLY_USER_NAME']); ?></td>
                                <td class="text-center"><?php echo ($bItem['PRICE_LIMIT']); ?></td>
                                <td class="text-center"><?php echo ($bItem['NUM_LIMIT']); ?></td>
                                <td class="text-center"><?php echo ($bItem['TOTAL_COST']); ?></td>
                                <td class="text-center"><?php echo ($bItem['FEE_NAME']); ?></td>
                                <td class="text-center">
                                    <?php if($bItem['IS_FUNDPOOL'] == 1): ?>是
                                        <?php else: ?> 否<?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if($bItem['IS_KF'] == 1): ?>是
                                        <?php else: ?> 否<?php endif; ?>
                                </td>
                                <?php if(!empty($bizWebEditable)): ?><td class="text-center">
                                        <a href="#" class="row-op btn btn-primary btn-xs" data-action="1" fid="<?php echo ($bItem['ID']); ?>"><i class="glyphicon glyphicon-edit"></i>&nbsp;</a>
                                        <a href="#" class="row-op btn btn-danger btn-xs" data-action="3" fid="<?php echo ($bItem['ID']); ?>"><i class="glyphicon glyphicon-trash"></i></a>
                                    </td><?php endif; ?>
                            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="ps-text">注：横向拖动表格可查看完整数据</p>
            <!--流程图-->
            <?php if(!empty($workFlows)): ?><div class="panel panel-primary panel-flow-graph">
        <a name="flow-graph"></a>
        <div class="panel-heading"><i class="glyphicon glyphicon-calendar"></i>&nbsp;流程图</div>
        <ul class="flow-graph-ul">
            <?php if(is_array($workFlows)): $i = 0; $__LIST__ = $workFlows;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$flow): $mod = ($i % 2 );++$i;?><li class="">
                    <p><span class="label label-success">第<?php echo ($flow['step']); ?>步</span>&nbsp;<span class="label label-danger">
                        <?php switch($flow['STATUS']): case "1": ?>未开始办理<?php break;?>
                            <?php case "2": ?>办理中<?php break;?>
                            <?php case "3": ?>已办结<?php break;?>
                            <?php case "4": ?>结束<?php break; endswitch;?>
                    </span>&nbsp;&nbsp;<span class="text-gray font-sm"><?php echo ($flow['E_TIME']); ?></span></p>
                    <blockquote>
                        <p><?php echo ($flow['DEAL_INFO']); ?></p>
                        <footer>经办人:&nbsp;<strong><?php echo ($flow['NAME']); ?></strong></footer>
                    </blockquote>
                    <?php if(!empty($flow['FILES'])): ?><dl>
                            <dt>附件:</dt>
                            <?php if(is_array($flow['FILES'])): $i = 0; $__LIST__ = $flow['FILES'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$file): $mod = ($i % 2 );++$i;?><dd style="word-break: break-all;"><a href="<?php echo C('DOMAIN_NAME');?>/index.php?s=/Upload/showfile&filecode=<?php echo ($file['code']); ?>" class="download-file" data-file-code="<?php echo ($file['code']); ?>"><?php echo ($file['name']); ?></a></dd><?php endforeach; endif; else: echo "" ;endif; ?>

                        </dl><?php endif; ?>
                </li><?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div><?php endif; ?>
            <!--审批意见-->
            <?php if(!empty($menu['opinion'])): ?><div class="panel panel-primary panel-opinion">
    <a name="opinion"></a>
    <div class="panel-heading"><i class="glyphicon glyphicon-pencil"></i>&nbsp;审批意见</div>
    <div class="panel-body">
        <form class="form-horizontal">
            <?php if($showButtons['next'] == 1): ?><div class="form-group single-choice">
                    <label for="DEAL_USER" class="col-sm-3 col-md-2 control-label">转交至</label>

                    <div class="col-sm-9 col-md-5">
                        <?php if(($isMobile == 1) OR ($isFixedFlow == 1)): ?><button type="button" class="btn btn-block btn-success" id="DEAL_USER" name="DEAL_USER">选择转交人</button>
                            <?php else: ?>
                            <input type="text" class="form-control" id="DEAL_USER_INPUT" placeholder=""/><?php endif; ?>
                        <ul class="selected-user-list" data-target="DEAL_USER">
                        </ul>
                    </div>
                </div><?php endif; ?>
             <?php if(($showButtons['next'] == 1) or ($isFixedFlow == 1)): ?><div class="form-group">
                    <label for="COPY_USER" class="col-sm-3 col-md-2 control-label">抄送至</label>
                    <div class="col-sm-9 col-md-5">
                        <?php if($isMobile == 1 ): ?><button type="button" class="btn btn-block btn-success" id="COPY_USER" name="COPY_USER">选择抄送人</button>
                            <?php else: ?>
                            <input type="text" class="form-control" id="COPY_USER_INPUT" placeholder=""/><?php endif; ?>
                        <ul class="selected-user-list" data-target="COPY_USER">
                        </ul>
                    </div>
                </div><?php endif; ?>
            <?php if($isMobile == 0 ): if(($showButtons['next'] == 1) or ($isFixedFlow == 1)): ?><div class="form-group">
                        <label for="COPY_USER" class="col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-md-5">
                            <if condition="$isMobile eq 0 ">
                                <textarea class="form-control" placeholder="抄送组成员名称" id="GROUPNAME_TEXT" disabled='true'></textarea>
                                <input type="hidden" value=""  id="GROUPNAME_TEXT_ID"/><?php endif; ?>
                        </div>
                    </div><?php endif; ?>
                <?php if(($showButtons['next'] == 1) or ($isFixedFlow == 1)): ?><div class="form-group">
                        <label for="COPY_USER" class="col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-md-5">
                            <?php if($isMobile == 1 ): ?><!--<button type="button" class="btn btn-block btn-success" id="COPY_USERGROUP" name="COPY_USERGROUP">选择抄送组</button>-->
                                <?php else: ?>
                                <select  id="COPY_USERGROUP_SELECT" placeholder="选择抄送组" name="COPY_USERGROUP_SELECT" multiple="multiple" />
                                </select>
                                <ul class="selected-user-list" data-target="COPY_USERGROUP">
                                </ul><?php endif; ?>
                        </div>
                    </div><?php endif; ?>
            </if>
            <?php if($showButtons['next'] == 1 ): ?><div class="form-group">
                    <label class="col-sm-3 col-md-2 control-label">短信</label>

                    <div class="col-sm-9">
                        <label class="radio-inline">
                            <input type="radio" name="ISPHONE" id="ISPHONE1" value="-1"> 是
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="ISPHONE" id="ISPHONE2" value="0" checked> 否
                        </label>
                    </div>
                </div><?php endif; ?>
            <?php if($showButtons['next'] == 1 or $isFixedFlow == 1): ?><div class="form-group">
                    <label class="col-sm-3 col-md-2 control-label">OA邮件</label>

                    <div class="col-sm-9">
                        <label class="radio-inline">
                            <input type="radio" name="ISMALL" id="ISMALL1" value="-1"> 是
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="ISMALL" id="ISMALL2" value="0" checked> 否
                        </label>
                    </div>
                </div><?php endif; ?>
            
            <?php if(empty($flowId)): ?><div class="form-group  ">
                    <label for="INFO" class="col-sm-3 col-md-2 control-label">文字/说明</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" id="INFO" name="INFO">
                    </div>
                </div><?php endif; ?>
            <div class="form-group">
                <label for="DEAL_INFO" class="col-sm-3 col-md-2 control-label">审批意见</label>
                <div class="col-sm-9">
                    <textarea id="DEAL_INFO" class="form-control" rows="4"></textarea>
                </div>
            </div>
            <?php if(empty($isMobile)): ?><div class="form-group">
                    <label for="FILES" class="col-sm-3 col-md-2 control-label">附件</label>
                    <div class="col-sm-9">
                        <input id="FILES" name="FILES" type="file" multiple="true" class="btn btn-default"/>
                        <input  name="filesvalue" class="form-control" tfield="FILES" type="hidden" value=""/>
                    </div>
                </div><?php endif; ?>
            <div class="form-group">
                <?php if(!empty($showButtons)): ?><div class="col-sm-12 col-md-offset-2 col-md-9 custom-btn-group">
                        <?php if($showButtons['next'] == 1): ?><button type="button" class="btn btn-danger btn-block" id="btn-next" data-action="next">转交</button><?php endif; ?>
                        <?php if($showButtons['deny'] == 1): ?><button type="button" class="btn btn-danger btn-block" id="btn-deny" data-action="deny">否决</button><?php endif; ?>
                        <?php if($showButtons['pass'] == 1): ?><button type="button" class="btn btn-danger btn-block" id="btn-pass" data-action="pass">同意</button><?php endif; ?>
                        <?php if($showButtons['finish'] == 1): ?><button type="button" class="btn btn-danger btn-block" id="btn-finish" data-action="finish">备案</button><?php endif; ?>
                    </div><?php endif; ?>
            </div>
        </form>
    </div>
</div><?php endif; ?>
        </div>
    </div>

    <div class="foot">
        <!--菜单-->
        <?php if(!empty($menu)): ?><div class="catalog-button" id="catalog-button">
        <span class="glyphicon glyphicon-list"></span>
    </div>
    <div class="overlay">
    </div>
    <nav class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-right" id="right-menu">
        <h3>目录</h3>
        <?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$menuItem): $mod = ($i % 2 );++$i;?><a href="#<?php echo ($menuItem['name']); ?>" class="page-anchor"><?php echo ($menuItem['text']); ?></a><?php endforeach; endif; else: echo "" ;endif; ?>
    </nav><?php endif; ?>
        <!--用户选择列表-->
        <!--转交至用户选择-->
<div id="deal-user-list" class="users">
    <div class="users-header">
        <!--搜索框-->
        <div class="search-box">
            <input type="text" class="search-box-text pull-left" id="keyword" placeholder="请输入姓名或部门进行搜索">
            <div class="clearfix"></div>
        </div>
    </div>

    <div class="users-body">
        <!--用户列表-->
    </div>

    <div class="users-footer">
        <!--操作栏-->
        <div class="button-box">
            <button class="btn btn-primary pull-left ok">确定</button>
            <button class="btn btn-default pull-right back">返回</button>
            <div class="select-result"></div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>


<!--抄送至用户选择-->
<div id="copy-user-list" class="users">
    <div class="users-header">
        <!--搜索框-->
        <div class="search-box">
            <input type="text" class="search-box-text pull-left" id="" placeholder="请输入姓名或部门进行搜索">
            <div class="clearfix"></div>
        </div>
    </div>

    <!--<p style="padding: 0 10px"><label class="checkbox-inline">-->
        <!--<input type="checkbox" id="check-all"> 全选-->
    <!--</label>-->
    <!--</p>-->
    <div class="users-body">
        <!--用户列表-->
    </div>

    <div class="users-footer">
        <!--操作栏-->
        <div class="button-box">
            <button class="btn btn-primary pull-left ok">确定</button>
            <button class="btn btn-default pull-right back">返回</button>
            <div class="select-result"></div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>

<!--抄送至用户组选择-->
<div id="copy-usergroup-list" class="users">
    <div class="users-header">
        <!--搜索框-->
        <div class="search-box">
            <input type="text" class="search-box-text pull-left" id="" placeholder="请输入分组名称进行搜索">
            <div class="clearfix"></div>
        </div>
    </div>
    <div class="search-box" style="position:fixed;width:100%;height:53px;z-index:1005"><textarea id="group_name" placeholder="组内成员名称"  class="form-control" style=""></textarea></div>
    <!--<p style="padding: 0 10px"><label class="checkbox-inline">-->
    <!--<input type="checkbox" id="check-all"> 全选-->
    <!--</label>-->
    <!--</p>-->
    <div class="users-body" style="margin-top:100px">
        <!--用户列表-->
    </div>

    <div class="users-footer">
        <!--操作栏-->
        <div class="button-box">
            <button class="btn btn-primary pull-left ok">确定</button>
            <button class="btn btn-default pull-right back">返回</button>
            <div class="select-result"></div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
    </div>

<script src="Public/validform/js/jquery-1.9.1.min.js"></script>
<script src="Public/third/bootstrap/js/bootstrap.min.js"></script>
<script src="Public/uploadify/jquery.uploadify-3.1.min.js"></script>
<script src="Public/js/ECS5.js"></script>
<script src="//cdn.bootcss.com/sweetalert/1.1.3/sweetalert.min.js"></script>
<link rel="stylesheet" href="Public/select2/select2.css" type="text/css" media="all"/>
<link href="Public/css/style2.css" type="text/css" rel="stylesheet"/>
<script type="text/javascript" src="Public/select2/select2.js"></script>
<script type="text/javascript" src="Public/layer/layer.js"></script>
<!--jquery-ui-->
<script type="text/javascript" src="./Public/js/jquery-ui.js"></script>
<!--弹出框-->
<?php if($isMobile == 1): ?><script language="javascript" type="text/javascript" src="Public/layer.mobile/layer/layer.js"></script>
    <?php else: ?>
    <script language="javascript" type="text/javascript" src="Public/layer/layer.js"></script>
    <script language="javascript" type="text/javascript" src="Public/layer/extend/layer.ext.js"></script><?php endif; ?>
<!--selectize-->
<link rel="stylesheet" href="Public/selectize/examples/css/normalize.css">
<link rel="stylesheet" href="Public/selectize/examples/css/stylesheet.css">
<link rel="stylesheet" href="Public/selectize/dist/css/selectize.default.css"">
<script src="Public/selectize/dist/js/standalone/selectize.js"></script>
<script src="Public/selectize/examples/js/index.js"></script>

<!--comm.js-->
<script>
    $(function () {
        var CONFIG = {
            'USERS_URL': '__APP__/Touch/Provider/getUsers/flowType/<?php echo ($flowType); ?>',
            'MID_SCREEN_WIDTH': 768
        };

        // 判断是否为安卓设备
        function isAndroidDevice() {
            var is_android = false;

            if (navigator.userAgent.match(/android/i)) {
                is_android = true;
            }

            return is_android;
        }

        // 为body添加position属性
        function addPositionToBody() {
            if (isAndroidDevice()) {
                $('body').addClass('relative-position');
            } else {
                $('body').addClass('fixed-position');
            }
        }

        function removePositionFromBody() {
            $('body').removeClass('relative-position fixed-position');
        }

        var rightMenu = $('#right-menu');

        var menuWidth = $(window).width() * 0.6 + 'px';
        if ($(window).width() > 768) {  // 如果是大尺寸屏幕，则设置为最大值
            menuWidth = 240;
        }
        rightMenu.css({
            'width': menuWidth,
            'right': '-' + menuWidth
        });

        $(document).on('touchend click', '#catalog-button, .overlay', function (event) {
            // toggle overlay
            event.preventDefault();
            doMenuCtrl();
        });

        $(document).on('click', '#right-menu a', function (event) {
            event.preventDefault();
            var anchorName = $(this).attr('href');
            if (anchorName[0] == '#') {
                anchorName = anchorName.substr(1);
            }
            if (anchorName) {
                gotoAnchor(anchorName, 500);
            }
            doMenuCtrl();
        });

        function doMenuCtrl() {
            $('body').toggleClass('noscroll');
            $('.overlay').toggleClass('show');

            // toggle menu
            rightMenu.toggleClass('cbp-spmenu-open');
        }

        // 跳转至审批意见锚点
        function gotoAnchor(anchorName, timeSpan) {
            timeSpan = timeSpan || 0;
            var pos = $('a[name="' + anchorName + '"]').position();
            var top = parseInt(pos.top) - 20;
            if (parseFloat($(window).width()) >= CONFIG.MID_SCREEN_WIDTH) {
                top -= 140;
            }
            $('html, body').animate({
                scrollTop: top + 'px'
            }, timeSpan);
        }


        /**
         * 用户筛选列表及相应交互
         */
        function UserProvider(options) {
            return {
                init: function () {
                    var that = this;
                    this.type = options.type;  // single=单选， multi=多选
                    this.model = options.model;
                    this.userList = [];
                    if (options.list) {
                        this.elem = $('#' + options.list);
                    }

                    if (options.triggerBtn) {
                        this.triggerBtn = '#' + options.triggerBtn;
                    }

                    var search = function () {
                        var val = $.trim(that.elem.find('.search-box .search-box-text').val());
                        var searchReg = new RegExp(val, 'i');
                        var lis = that.elem.find('li');
                        for (var j = 0; j < lis.length; j++) {
                            var userDesc = $(lis[j]).find('.user-desc').text();
                            var username = $(lis[j]).attr('data-name');
                            if (!searchReg.test(userDesc) && !searchReg.test(username)) {
                                $(lis[j]).removeClass('show');
                                $(lis[j]).addClass('hide');
                            } else {
                                $(lis[j]).removeClass('hide');
                                $(lis[j]).addClass('show');
                            }
                        }

                        var uls = that.elem.find('ul');
                        for (var j = 0; j < uls.length; j++) {
                            var showLiCount = $(uls[j]).find('li.show').length;
                            if (showLiCount == 0) {
                                $(uls[j]).closest('.panel').addClass('hide');
                            } else {
                                $(uls[j]).closest('.panel').removeClass('hide');
                            }
                        }
                    };
                    this.elem.find('.search-box input.search-box-text').on('input', search);
                    return this;
                },

                fire: function () {
                    var that = this;
                    //console.log(that);
                    // 默认请求一次异步调用
                    function triggerOnceRequest() {
                        var type = that.type;
                        var model = that.model;
                        // 如果有转交至下一步按钮，则默认先请求一次调用
                        var hasNext = "<?php echo ($showButtons['next']); ?>";
                        if (hasNext) {
                            // 默认先请求一次
                            $.ajax({
                                url: CONFIG.USERS_URL,
                                type: 'GET',
                                dataType: 'json',
                                data: {
                                    flowId: '<?php echo ($flowId); ?>',
                                    type:type,
                                    model:model,
                                },
                                success: function (res) {
                                    if (res.status) {
                                        that.updateUserList(res.data);
                                        that.elem.addClass('loaded');
                                    }
                                },
                                error: function () {
                                    // todo
                                }
                            });
                        }
                    }
                    //console.log(that);
                    var resetSelectedUsers = function () {
                        that.elem.find('.select-indictor').addClass('hide');
                        that.elem.find('li.selected').removeClass('selected');

                        for (var key in that.userList) {
                            var id = that.userList[key]['id'];
                            that.elem.find('[ok-indictor="' + id + '"]').removeClass('hide');
                            that.elem.find('li[data-id="' + id + '"]').addClass('selected');
                        }

                        if (that.type == 'single') {
                            var desc = that.userList.length && that.userList['userDesc'] || '';
                            that.elem.find('.select-result').text(desc);
                        } else if (that.type == 'multi') {
                            var userNum = that.userList.length;
                            if(that.model == 'group'){
                                that.elem.find('.select-result').text('已选中' + userNum + '组');
                            }else{
                                that.elem.find('.select-result').text('已选中' + userNum + '人');
                            }

                        }

                    };
                    //console.log(that);
                    $(document).on('click', that.triggerBtn, function (event) {
                        var type = that.type;
                        var model = that.model;
                        event.preventDefault();
                        $('body').toggleClass('noscroll');
                        addPositionToBody();
                        that.elem.show();
                        //console.log(that)
                        if (!that.elem.hasClass('loaded')) {
                            $.ajax({
                                url: CONFIG.USERS_URL,
                                type: 'GET',
                                dataType: 'json',
                                data: {
                                    flowId: '<?php echo ($flowId); ?>',
                                    type:type,
                                    model:model,
                                },
                                success: function (res) {
                                    if (res.status) {
                                        that.updateUserList(res.data);
                                        that.elem.addClass('loaded');
                                    }
                                },
                                error: function () {
                                    // todo
                                }
                            });
                        } else {
                            resetSelectedUsers();
                        }
                    });
                    triggerOnceRequest();  // 请求一次异步调用

                    return this;
                },
                updateUserList: function (data) {
                    var that = this;
                    var createUserPanel = function () {
                        return $('<div class="panel panel-default"></div>')
                    };

                    var createUserDesc = function (user) {
                        if(user['NAME']) {
                            var text = user['NAME'];
                            if (user['DEPTNAME']) {
                                text += '-' + user['DEPTNAME'];
                            }
                            if (user['CITY_NAME']) {
                                text += '-' + user['CITY_NAME'];
                            }
                        }else{
                            var text = user['GROUPNAME'];
                        }
                        return text;
                    };

                    var createUserItem = function (data) {
                        var desc = createUserDesc(data);
                        if(data['USERNAME']) {
                            var view = $('<li class="list-group-item" data-name="' + data['USERNAME'] + '" data-id="' + data['ID'] + '"></li>');
                        }else{
                            var view = $('<li class="list-group-item" data-name="' + data['GROUPNAME'] + '" data-id="' + data['ID'] + '"></li>');
                        }
                        var indicator = $('<i class="glyphicon glyphicon-ok pull-right hide select-indictor" ok-indictor="' + data['ID'] + '"></i>');
                        view.append(indicator);
                        view.append('<span class=user-desc>' + desc + '</a>');

                        view.click(function () {

                            // 如果是单选，则最多只能选中一个条目
                            if (that.type == 'single') {
                                $('li.list-group-item.selected').removeClass('selected').find('i').addClass('hide');
                                that.elem.find('.select-result').text(desc);
                            }

                            indicator.toggleClass('hide');
                            view.toggleClass('selected');

                            if (that.type == 'multi') {
                                var userNum = that.elem.find('li.selected').length;
                                if(that.model == 'group'){
                                    var group_userId = []
                                     group_userId[0] =$(this).attr("data-id");
                                    $.ajax({
                                        url: "index.php?s=/Api/getFlowGroupName",
                                        dataType: "json",
                                        data: {
                                            "groupUserId": group_userId,
                                        },
                                        success: function (data) {
                                            $("#group_name").val(data);
                                            $("#group_name").prop("disabled",true);
                                        }
                                    })
                                    that.elem.find('.select-result').text('已选中' + userNum + '组');
                                }else {
                                    that.elem.find('.select-result').text('已选中' + userNum + '人');
                                }
                                setTimeout(function () {
                                    that.elem.find('.search-box input.search-box-text').val('').trigger('input');  // 清空搜索
                                }, 500);
                            }

                        });

                        return view;
                    };

                    var usersBody = this.elem.find('.users-body');
                    for (var key in data) {
                        var userPanel = createUserPanel();
                        userPanel.append('<div class="panel-heading"><strong>' + key + '</strong></div>');
                        var userGroup = $('<ul class="list-group"></ul>');
                        userPanel.append(userGroup);
                        for (var i = 0; i < data[key].length; i++) {
                            var listItem = createUserItem(data[key][i]);
                            userGroup.append(listItem);
                        }
                        usersBody.append(userPanel);
                    }
                },
                bindButtons: function () {
                    var that = this;

                    // 创建选择用户条目
                    var createSelectedUserItem = function (data) {
                        var elem = $('<li class="alert-info" data-id="' + data['id'] + '"></li>');
                        elem.append('<button type="button" class="close" data-dismiss="alert"  aria-label="Close"><span aria-hidden="true">&times;</span></button>')
                        elem.append('<strong>' + data['userDesc'] + '</strong>');

                        return elem;
                    };

                    // 更新选中的用户列表
                    var updateSelectedUsers = function () {
                        that.elem.hide();
                        $('body').removeClass('noscroll');
                        removePositionFromBody();
                        var triggerBtnElem = $(that.triggerBtn);
                        if (that.type == 'single') {
                            triggerBtnElem.hide();  // 隐藏输入框
                        }

                        var usersElem = triggerBtnElem.siblings('ul.selected-user-list');
                        usersElem.html('');
                        for (var i = 0; i < that.userList.length; i++) {
                            var item = createSelectedUserItem(that.userList[i]);
                            usersElem.append(item);
                        }
                    };

                    // 确定按钮
                    that.elem.find('.ok').click(function () {
                        that.userList = [];
                        var selectedCount = that.elem.find('li.selected').length;
                        if (!selectedCount) {
                            layer.open({
                                content: '未选择用户',
                                icon: 2
                            });
                        } else {
                            var selectedUsers = that.elem.find('li.selected');
                            for (var i = 0; i < selectedUsers.length; i++) {
                                var id = $(selectedUsers[i]).attr('data-id');
                                var userDesc = $(selectedUsers[i]).find('.user-desc').text();
                                if (id) {
                                    that.userList.push({'id': id, 'userDesc': userDesc});
                                }
                            }
                            // 更新选中的用户列表
                            updateSelectedUsers();
                            gotoAnchor('opinion');  // 跳转到审批意见锚点
                        }
                    });

                    // 返回按钮
                    that.elem.find('.back').click(function () {
                        that.elem.hide();
                        $('body').removeClass('noscroll');
                        removePositionFromBody();
                        gotoAnchor('opinion');  // 跳转到审批意见锚点
                    });

                    // 当用户点击移除按钮时
                    $(document).on('click', '.selected-user-list li > button', function () {
                        var remainCount = $(this).closest('ul').find('li').length;
                        if (remainCount == 1) {
                            var inputElem = $(this).closest('.form-group').find('button');
                            if (inputElem) {
                                inputElem.show();
                            }
                        }

                        var target = $(this).closest('ul').attr('data-target');
                        var id = $(this).parent().attr('data-id');
                        if (target == 'COPY_USER') {
                            var users = copyUserProvider.getSelectedUsers();
                            for (var i = 0; i < users.length; i++) {
                                if (users[i]['id'] == id) {
                                    copyUserProvider.removeUser(i);
                                    break;
                                }
                            }
                        } else {
                            var users = dealUserProvider.getSelectedUsers();
                            for (var i = 0; i < users.length; i++) {
                                if (users[i]['id'] == id) {
                                    dealUserProvider.removeUser(i);
                                    break;
                                }
                            }
                        }

                        $(this).parent().remove();
                    });
                    return this;
                },
                getSelectedUsers: function () {
                    return this.userList;
                },
                removeUser: function (pos) {
                    this.userList.splice(pos, 1)
                }
            }
        }


        /**
         * 获取表单数据
         * @returns {*}
         */
        function getFormData(action) {
            // 获取审核页面的业务数据（通过获取DOM元素获取）
            function getBizHtml() {
                var result = '',
                    filters = ['#flow-graph', '#opinion']; // 要过滤菜单的个数
                var anchors = $('#right-menu > a').filter(function(index) {
                    var part = $(this).attr('href');
                    return $.inArray(part, filters) == -1;
                });


                for (var i = 0; i < anchors.length; i++) {
                    var name = $(anchors[i]).attr('href');
                    if (name.length && name.charAt(0) == '#') {
                        name = name.substr(1);
                    }

                    var aHtml = $('a[name="' + name + '"]').closest('.panel').prop('outerHTML');
                    if (aHtml) {
                        aHtml = inputTagReplacedHtml(aHtml);
                        result += aHtml;
                    }

                    if (name == 'purchase-exec') {
                        var extraElem = $('a[name="' + name + '"]').closest('.panel').siblings('#contract_admin_confirm');
                        var extraHtml = extraElem && extraElem.prop('outerHTML') || '';

                        extraElem = null;
                        extraElem = $('a[name="' + name + '"]').closest('.panel').siblings('#confirmed_discount_ad');
                        var extraHtml2 = extraElem && extraElem.prop('outerHTML') || '';
                        extraHtml += extraHtml2;

                        result += '<div style="padding: 10px;">' + extraHtml + '</div>';
                    }
                }

                return result;

                function inputTagReplacedHtml(srcHtml) {
                    var destHtml = srcHtml;
                    if (srcHtml) {
                        destHtml = aHtml.replace(/<input([^>]*>)?/gi, function(a) {
                            var elem = $(a);
                            var type = elem.attr('type');
                            if (type == 'text') {
                                return '<span>' + elem.val() + '</span>';
                            } else if(type == 'radio') {
                                var checked = elem.attr('ok');
                                elem.attr('disabled', true);
                                if (checked) {
                                    elem.attr('checked', 'checked');
                                }
                                return elem.prop('outerHTML')
                            } else {
                                return a;
                            }
                        })
                    }

                    return destHtml;
                }
            }

            // 检查表单数据
            var checkFormData = function (action) {
                var response = {
                    status: true,
                    message: '',
                    data: data
                };

                if (parseInt(data['flowId']) == 0) {
                    if (!$.trim(data['INFO'])) {
                        response.status = false;
                        response.message = '申请说明信息不能为空';
                    }
                }

                if (data['flowTypePY'] == 'BenefitFlow') {
                    var fee_type = $('#fee_type').val() ? $('#fee_type').val() : $('#fee_type').html();
                    if (!$.trim(fee_type)) {
                        response.status = false;
                        response.message = '费用类别不能为空';
                    }

                }

                if (response.status) {
                    switch (action) {
                        case 'pass':
                        case 'next':
                            if (!data['DEAL_USERID']) {
                                response.status = false;
                                response.message = '转交人不能为空！';
                            }

                            if (response.status && !data['DEAL_INFO']) {
                                response.status = false;
                                response.message = '审批意见不能为空！';
                            }
                            break;
                            break;
                        case 'deny':
                        case 'finish':
                            if (response.status && !data['DEAL_INFO']) {
                                response.status = false;
                                response.message = '审批意见不能为空';
                            }
                            break;
                    }
                }

                return response;
            };

            var data = {};
            var info, dealUser, copyUsers = [], isPhone, isMail, dealInfo,copyUsersGroup = [];

            // 文字说明
            info = $('#INFO') && $('#INFO').val();
            data['INFO'] = info;

            // 转交至
//            var dealUserListElem = $('#DEAL_USER').parent().find('.selected-user-list li');
            var dealUserListElem = $('[data-target="DEAL_USER"]').find('li');
            if (dealUserListElem && dealUserListElem.first()) {
                dealUser = dealUserListElem.first().attr('data-id');
            }
            data['DEAL_USERID'] = dealUser;

            // 抄送至
//            var copyUserListElem = $('#COPY_USER').parent().find('.selected-user-list li');
            var copyUserListElem = $('[data-target="COPY_USER"]').find('li');

            if (copyUserListElem) {
                for (var i = 0; i < copyUserListElem.length; i++) {
                    var id = $(copyUserListElem[i]).attr('data-id');
                    if (id) {
                        copyUsers.push(id);
                    }
                }
            }

            data['COPY_USER'] = copyUsers;
            if (data['COPY_USER'].length > 0) {
                data['COPY_USERID'] = data['COPY_USER'].join(',') + ',';
				data['COPY_USERID'] += $("#GROUPNAME_TEXT_ID").val();
            }else data['COPY_USERID']  = $("#GROUPNAME_TEXT_ID").val();

            //抄送工作组
            //var copyUserGroupList = [];
           // $(".item").each(function(){
                //copyUserGroupList.push($(this).attr('data-value'));
           // })
           // data['COPY_USERGROUP'] = copyUserGroupList.join(',');

            // 附件
            var attachmentData = [];
            var attachmentListElem = $('#FILES').parent().find('.uploadify-queue-item');
            if (attachmentListElem) {
                for (var i = 0; i < attachmentListElem.length; i++) {
                    var elem = attachmentListElem[i];
                    var fileAbstract = $(elem).attr('id') + '-' + $(elem).attr('filename') + '-' + $(elem).attr('filesize');
                    attachmentData.push(fileAbstract);
                }
            }
            data['FILES'] = attachmentData.join(',');

            // 是否短信通知
            isPhone = $('input[name="ISPHONE"]:checked').val();
            data['ISPHONE'] = isPhone;

            // 是否邮件通知
            isMail = $('input[name="ISMALL"]:checked').val();
            data['ISMALL'] = isMail;

            dealInfo = $('#DEAL_INFO').val();
            data['DEAL_INFO'] = dealInfo;

            data['savedata'] = true;
            switch (action) {
                case 'next':
                    data['flowNext'] = true;
                    break;
                case 'deny':
                    data['flowNot'] = true;
                    break;
                case 'pass':
                    data['flowPass'] = true;
                    break;
                case 'finish':
                    data['flowStop'] = true;
            }

            data['flowId'] = '<?php echo ($flowId); ?>';
            data['recordId'] = '<?php echo ($recordId); ?>';
            data['type'] = '<?php echo ($flowType); ?>';
            data['flowTypePY'] = '<?php echo ($flowType); ?>';
            data['CASEID'] = '<?php echo ($CASEID); ?>';
            data['CHANGE'] = '<?php echo ($CHANGE); ?>';
            data['ACTIVID'] = '<?php echo ($ACTIVID); ?>';
            data['RECORDID'] = '<?php echo ($recordId); ?>';
            data['others'] = '<?php echo ($others); ?>';
            data['SCALETYPE'] = '<?php echo ($SCALETYPE); ?>';
            data['invoiceId'] = '<?php echo ($invoiceId); ?>';
            if (parseInt('<?php echo ($CID); ?>') > 0) {  // 变更版本号
                data['CID'] = '<?php echo ($CID); ?>';
            }

            // 工作流抄送业务数据
            var css = "<style>.panel{border:1px solid #ccc;margin:20px auto 20px;padding-bottom:0;max-width:90%;background:#fff;overflow-x:scroll;overflow-y:hidden;}.panel ul{list-style:none;margin:0;padding:0}.panel>.panel-heading{padding:10px 5px;font-weight:700;color:#444;background-color:#ddd}.panel>.panel-heading i{display:none}.panel>.panel-body{padding:4px}.panel .list-group-item{position:relative;display:block;margin-bottom:-1px;background-color:#fff;border-top:1px solid #ccc;padding:10px 15px 10px 300px}.list-group-item>label{display:block;position:absolute;left:25px;width:200px}.panel table{border-spacing:0;border-collapse:collapse;width:100%}.panel table tr th,.panel table tr td{border-top:1px solid #ddd;padding:8px;text-align:center;border-left:1px solid #ddd}.panel .content{width:100%;border:none;background-color:#fff}</style>";
            data['style'] = css;
            data['bizHtml'] = getBizHtml().replace(/\s+/gi, ' ');

            return checkFormData(action);
        }

        /**
         * 绑定审核人操作
         * @param which
         */
        function bindAction(which) {
            var that = this;
            var ACTION_URL = {
                'caigoushenqing': '<?php echo U("Purchase/opinionFlow");?>',  // 采购申请
                'bulkPurchase': '<?php echo U("Purchase/bulk_purchase_opinionFlow");?>',  // 采购申请
                'dulihuodong': '<?php echo U("Activ/opinionFlow");?>',  // 独立活动立项
                'xiangmuxiahuodong': '<?php echo U("Activ/XiangMuOpinionFlow");?>',  // 项目下活动立项
                'dulihuodongbiangeng': '<?php echo U("Activ/opinionFlowChange");?>',  // 独立活动立项变更
                'xiangmuxiahuodongbiangeng': '<?php echo U("Activ/opinionFlowChange");?>',  // 项目下活动立项变更
                'dianziedu': '<?php echo U("Payout_change/opinionFlow");?>',  // 垫资比例调整
                'jiekuanshenqing': '<?php echo U("Loan/opinionFlow");?>',  // 借款申请
                'huiyuantuipiao': '<?php echo U("InvoiceRecycle/opinionFlow");?>',
                'tksq': '<?php echo U("MemberRefund/opinionFlow");?>',
                'projectset': '__APP__/Touch/ProjectSet/opinionFlow',  //
                'projectchange': '__APP__/Touch/ProjectChange/opinionFlow',
                'finalaccounts': '__APP__/Touch/Finalaccounts/opinionFlow',
                'biaozhuntiaozheng': '<?php echo U("Feescale_change/opinionFlow");?>',

                'PurchaseNocash': '__APP__/Touch/PurchaseNocash/opinionFlow',
                'BenefitFlow': '__APP__/Touch/BenefitFlow/opinionFlow',
                'Termination': '__APP__/Touch/ProjectTermination/opinionFlow',

                'huiyuanhuanpiao': '<?php echo U("ChangeInvoice/opinionFlow");?>',
                'jianmianshenqing': '<?php echo U("MemberDiscount/opinionFlow");?>',
                'hetongkaipiao': '<?php echo U("Advert/opinionFlow");?>',
                'chengbenhuabo': '<?php echo U("Cost/opinionFlow");?>',
                'yewujintie': '<?php echo U("Benefits/opinionFlow");?>',
                'PurchasingBee': '<?php echo U("PurchasingBee/opinionFlow");?>',
                'zhihuanshenqing': '<?php echo U("Displace/opinionFlow");?>',
                'shoumai': '<?php echo U("InboundUse/opinionFlow","flowDisplaceType=1");?>',
                'neibulingyong': '<?php echo U("InboundUse/opinionFlow","flowDisplaceType=2");?>',
                'baosun': '<?php echo U("InboundUse/opinionFlow","flowDisplaceType=3");?>',
                'shoumaibiangeng': '<?php echo U("DisplaceSaleChange/opinionFlow");?>',
                'AdvanceChaoe' : '__APP__/Touch/AdvanceChaoe/opinionFlow',
            };

            var formData = {};  // 表单数据

            // 异步调用之前的操作
            var before = function (type) {

            };

            // 异步调用成功后的回调函数
            var successCallback = function (type) {

                return function () {
                };
            };

            // 异步调用失败后的回调函数
            var failCallback = function (type) {

                return function () {
                };
            };

            var doAction = function (action, data, onSuccess, onFail) {
                if (!action) {
                    return false;
                }
                var isMobile = '<?php echo ($isMobile); ?>', url = ACTION_URL[which];  // 获取URL
                $.ajax({
                    url: url,
                    type: 'POST',
                    async: false,
                    data: data,
                    success: (function (res) {
                        res = res && JSON.parse(res);

                        if (res.status == true || parseInt(res.status) == 1) {
                            onSuccess.call(that, res);
                            var msg = res.message || '操作成功';
                            layer.open({
                                content: msg,
                                btn: ['确定'],
                                yes: function() {
                                    if (isMobile) {
                                        if (res.url) {
                                            location.href = res.url;
                                        } else {
                                            location.reload();
                                        }
                                    } else {
                                        location.href = '<?php echo U("Admin/Flow/workStep");?>';  // PC版将跳转至指定页面
                                    }
                                }
                            });
                        } else {
                            onFail.call(that, res);
                            var msg = res.message || '操作失败';
                            $('#btn-next, #btn-deny, #btn-pass, #btn-finish').prop('disabled', false);
                            layer.open({
                                content: msg
                            });
                        }
                    }),
                    error: function () {
                        // todo
                    }
                });
            };

            $('#btn-next, #btn-deny, #btn-pass, #btn-finish').click(function () {
                $('#btn-next, #btn-deny, #btn-pass, #btn-finish').prop('disabled', true);
                var action = $(this).attr('data-action');
                var result = getFormData(action);
                //console.log(result);
                if (result.status == false) {

                    var msg = result.message || '请检查表单数据';
                    layer.open({ content: msg });
                    $('#btn-next, #btn-deny, #btn-pass, #btn-finish').prop('disabled', false);
                    return;
                } else {
                    formData = result.data;
                }
                if (action == 'finish' || action == 'deny') {
                    // 如果是备案或否决操作，则弹出确认框确认
                    var title = "确认备案？";
                    var text = "【备案】是工作流审批的最后一步，备案成功则完成审批。<br/><br/>确定要备案该工作流？";
                    if (action == 'deny') {
                        title = "确认否决？";
                        text = "执行“否决”操作成功后，该条工作流将被废弃。<br/>确定要否决该工作流？";
                    }

                    layer.open({
                        content: text,
                        btn: ['确定', '取消'],
                        yes: function() {
                            // 确定
                            before(which);
                            $('#btn-next, #btn-deny, #btn-pass, #btn-finish').prop('disabled', true);
                            doAction(action, formData, successCallback(which), failCallback(which));
                        },
                        no: function() {
                            // layer.mobile版的取消

                        },
                        btn2: function() {
                            // layer PC版的取消

                        }
                    });
                } else {
                    before(which);
                    $('#btn-next, #btn-deny, #btn-pass, #btn-finish').prop('disabled', true);
                    doAction(action, formData, successCallback(which), failCallback(which));
                }
            });
        }

        // 转交至用户选择
        var dealUserProvider = new UserProvider({
            model: 'user',
            type: 'single',
            list: 'deal-user-list',
            triggerBtn: 'DEAL_USER'
        });
        dealUserProvider.init().fire().bindButtons();

        // 抄送至用户选择
        var copyUserProvider = new UserProvider({
            model: 'users',
            type: 'multi',
            list: 'copy-user-list',
            triggerBtn: 'COPY_USER'
        });
        copyUserProvider.init().fire().bindButtons();
        
        var copyGroupProvider = new UserProvider({
            model : 'group',
            type: 'multi',
            list: 'copy-usergroup-list',
            triggerBtn: 'COPY_USERGROUP'

        })
        copyGroupProvider.init().fire().bindButtons();
         //绑定事件
        bindAction('<?php echo ($flowType); ?>');

        // 全局返回按钮
        $('.btn-back-global').click(function (event) {
            event.preventDefault();
            // 返回的类型type parent=上一级 history=上一步
            var type = $(this).attr('data-type');
            var historyLength = $(this).attr('data-history');

            history.back();
            return;
            if (type == 'history') {
                history.back();
            } else {
                location.href = '__APP__/Flow/workStep';
            }
        });

        // 打印工作流按钮
        $('.btn-print-flow').click(function (event) {

            event.preventDefault();
            $('#catalog-button, .panel-opinion, .back-btn-box, .cbp-spmenu').remove();
            $('.jumbotron').css({'position': 'absolute'});
            $('a').removeAttr('href');  // 去掉链接

            $('html, body').animate({
                scrollTop: 0
            }, 0);
            if (navigator.userAgent.toLowerCase().indexOf("firefox") != -1) {
                window.print();
            } else {
                document.execCommand('print');  // 打印当前页
            }

            location.reload();
        });
    });

</script>
<!--upload.js-->
<script>
    $(function () {
        // 更新文件列表
        function uploadify_uploadfilelist(filename, filesize, filecode, field) {
            var itemTemplate = '<div id="' + filecode + '" filename="' + filename + '" filesize="' + filesize + '"   name="filename_' + field + '" class="uploadify-queue-item">\
					<div class="cancel">\
						<a class="btn-remove-file" href="javascript:void(0);" data-filecode="' + filecode + '">&times;</a>\
					</div>\
					<span class="fileName"><a target="_blank" href="index.php?s=/Upload/showfile&filecode=' + filecode + '">' + filename + ' (' + filesize + ')</a></span><span class="data"></span>\
					<div class="uploadify-progress">\
						<div  > </div>\
					</div>\
				</div>';
            $("#" + field + "").parent().append(itemTemplate);
        }

        $(document).on('click', '.btn-remove-file', function () {
            var fileCode = $(this).attr('data-filecode');
            $("#" + fileCode).remove();
        });

        $('#FILES').uploadify({
            'uploader': 'index.php?s=/Upload/save2oracle/',
            'onUploadSuccess': function (file, data) {
                uploadify_uploadfilelist(file.name, file.size, data, 'FILES');
            },
            'formData': {
                'timestamp': '<?php echo time();?>',
                'token': "<?php echo md5('nr234n9i92n2' . time());?>"
            },
            'swf': 'Public/uploadify/uploadify.swf',
            'buttonText': '上传文件',
            'height': 'auto',
            'width': '120',
            'height': '35',
            'buttonCursor': 'hand'
        });
    });
</script>

<!--purchase.js-->
<script>
    $(function () {
        var ACTION = {
            EDIT: 1,
            CHECK: 2
        };

        function getSelectedPurchaseItem(fid) {
            var result = [];
            if (parseInt(fid) > 0) {
                var purchaseList = '<?php echo ($purchaseListJSON); ?>';
                if (purchaseList) {
                    purchaseList = JSON.parse(purchaseList);
                }
                for (var i = 0; i < purchaseList.length; i++) {
                    if (fid == purchaseList[i]['ID']) {
                        result = purchaseList[i];
                        break;
                    }
                }
            }

            return result;
        }

        $(document).on('click touchend', '.row-op', function (event) {
            event.preventDefault();
            var fid = $(this).attr('fid');
            var layerW = $(window).width() * 0.9,
                    layerH = $(window).height() * .7;


            var detail = getSelectedPurchaseItem(fid),
                    action = $(this).attr('data-action'),
                    url = '__APP__/Purchase/purchase_list/TAB_NUMBER/5/&parentchooseid=' + detail['PR_ID'] + '&showForm=' + action + '&ID=' + detail['ID'];
            if (action == 3) {  // 删除采购明细操作
                layer.open({
                    content: '确定删除该条采购明细？',
                    btn: ['确定', '取消'],
                    yes: function() {
                        url = '__APP__/Purchase/purchase_list/&parentchooseid=' + detail['PR_ID'] + '&faction=delData&ID=' + detail['ID'];
                        $.ajax({
                            url: url,
                            success: function (data) {
                                data = data && JSON.parse(data);
                                var msg = '采购明细删除成功！', type = 'success';
                                if (data.status != 'success') {
                                    msg = '采购明细删除失败！';
                                    type = 'error';
                                }
                                alert(msg);
                                location.reload();
                            },
                            error: function () {

                            }
                        });
                    },
                    no: function() {

                    },
                    btn2: function() {

                    }
                });
            } else {
                layer.open({
                    type: 2,
                    area: [layerW + 'px', layerH + 'px'],
                    fix: true, //不固定
                    shadeClose: true,
                    content: url,
                    shade: 0.8,
                    end: function () {
                        if (action == ACTION.EDIT) {
                            location.reload();
                        }
                    }
                });
            }
        });

        //置换业务工作流更新处理
        $(document).on('click touchend', '.displace-row-op', function (event) {
            //初始化
            event.preventDefault();
            var fid = $(this).attr('fid');
            var layerW = $(window).width() * 0.9,
                    layerH = $(window).height() * .7;

            var action = $(this).attr('data-action'),
                    updateurl = $(this).attr('data-update-url'),
                    delurl = $(this).attr('data-del-url');

            if (action == 3) {  // 删除采购明细操作
                layer.open({
                    content: '确定删除该条记录吗？',
                    btn: ['确定', '取消'],
                    yes: function() {
                        $.ajax({
                            url: delurl,
                            success: function (data) {
                                data = data && JSON.parse(data);
                                var msg = '亲,删除成功！', type = 'success';
                                if (data.status != 'success') {
                                    msg = '亲,删除失败,请重试！';
                                    type = 'error';
                                }
                                alert(msg);
                                location.reload();
                            },
                            error: function () {

                            }
                        });
                    },
                    no: function() {

                    },
                    btn2: function() {

                    }
                });
            } else {
                layer.open({
                    type: 2,
                    area: [layerW + 'px', layerH + 'px'],
                    fix: true, //不固定
                    shadeClose: true,
                    content: updateurl,
                    shade: 0.8,
                    end: function () {
                        if (action == ACTION.EDIT) {
                            location.reload();
                        }
                    }
                });
            }
        });
    });

</script>

<!--history.js-->
<script>
    $(function () {
        var histroyLength = history.length;
        $('.btn-back-global').attr('data-history', histroyLength);

        //工作组下拉
        $.ajax({
            type: "GET",
            url: "index.php?s=/Api/ajax_get_groupname",
            dataType:"JSON",
            success:function(data)
            {
                var groupName = [];
                $.each(data, function(key, value)
                {
                    groupName .push('<option value="'+ value['ID'] +'">'+ value['GROUPNAME']+'</option>');
                })
                $("select[name='COPY_USERGROUP_SELECT']").append(groupName.join(''));

                //展示值
                var $select = $('#COPY_USERGROUP_SELECT').selectize({
                    plugins: ['remove_button'],
                    persist: false,
                    create: true,
                    maxItems: null,
                    valueField: 'id',
                    labelField: 'title',
                    searchField: 'title',
                    create: false,
					onItemRemove:function(a){removeItem(a);},
					onItemAdd:function(a){ addItem(a)}
                });
            },

        })

    });
</script>

<!--autocomplete.js-->
<?php if($isMobile == 0): ?><script>
		Array.prototype.del=function(n) {　//n表示第几项，从0开始算起。
			//prototype为对象原型，注意这里为对象增加自定义方法的方法。
		　if(n<0)　//如果n<0，则不进行任何操作。
			return this;
		　else
			return this.slice(0,n).concat(this.slice(n+1,this.length));
		}

        function createSelectedUserItem(data, callback) {
            callback = callback || function () {
            };
            var elem = $('<li class="alert-info" data-id="' + data['id'] + '"></li>');
            var button = $('<button type="button" class="close" data-dismiss="alert"  aria-label="Close"><span aria-hidden="true">&times;</span></button>');
            elem.append(button);
            elem.append('<strong>' + data['userDesc'] + '</strong>');

        callback.call(this, button);

            return elem;
        }

        $("#DEAL_USER_INPUT").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "index.php?s=/Api/getFlowPeople",
                    dataType: "json",
                    data: {
                        "search": request.term,
                        "roleId": $("#roleId").val()
                    },
                    success: function (obJect) {
                        response($.map(obJect, function (item) {
                            return {
                                label: item.name,
                                value: item.name,
                                USERID: item.id,
                                PHONE: item.phone,
                                CITY: item.city
                            }
                        }));
                    }
                });
            },
            minLength: 1,
            select: function (event, ui) {
                $('#DEAL_USER_INPUT').hide();
                var usersElem = $(this).siblings('ul.selected-user-list');
                usersElem.html('');
                var item = createSelectedUserItem({
                    id: ui.item.USERID,
                    userDesc: ui.item.label
                }, function (elem) {
                    $(elem).unbind('click').bind('click', function () {
                        $(elem).closest('li').remove();
                        $('#DEAL_USER_INPUT').show().focus();
                    })
                });
                usersElem.append(item);

                // 清空输入框的内容
                $(this).val('');
                return false;
            }
        });

        $("#COPY_USER_INPUT").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "index.php?s=/Api/getFlowPeople",
                    dataType: "json",
                    data: {
                        "search": request.term,
                        "roleId": $("#roleId").val()
                    },
                    success: function (obJect) {
                        response($.map(obJect, function (item) {
                            return {
                                label: item.name,
                                value: item.name,
                                USERID: item.id,
                                PHONE: item.phone,
                                CITY: item.city
                            }
                        }));
                    }
                });
            },
            minLength: 1,
            select: function (event, ui) {
                var usersElem = $(this).siblings('ul.selected-user-list');
                var item = createSelectedUserItem({
                    id: ui.item.USERID,
                    userDesc: ui.item.label
                }, function (elem) {
                    $(elem).unbind('click').bind('click', function () {
                        $(elem).closest('li').remove();
                    })
                });
                //console.log(item);
                usersElem.append(item);

                // 清空输入框的内容
                $(this).val('');
                return false;
            }
        });
		
		 
     // $("select[name='COPY_USERGROUP_SELECT']").change(function(){
		function addItem(a){  //alert(a);
            //var groupUserId = $("select[name='COPY_USERGROUP_SELECT']").val();
            $.ajax({
                url: "index.php?s=/Api/getFlowGroupName",
                dataType: "json",
                data: {
                    "groupUserId": a,
                },
                success: function (data) {
					//if($("#GROUPNAME_TEXT").val() !=''){
						//console.log(data);
						//data= eval('(' + data + ')')
						var users = new Array();
						var text = new Array();
						if($("#GROUPNAME_TEXT_ID").val().length>0)users= $("#GROUPNAME_TEXT_ID").val().split(','); 
						if($("#GROUPNAME_TEXT").val().length>0)text  = $("#GROUPNAME_TEXT").val().split(','); 
					for(var i in data ){ 
						//$("#GROUPNAME_TEXT").append(','+data[i]['SinUserName']);
						text.push(data[i]['SinUserName']);
						users.push(data[i]['USERNAME']);
					}
					var ccc = text.join(',');
					ccc=ccc.substring(0, ccc.lastIndexOf(','));  
					$("#GROUPNAME_TEXT").val(ccc);
					var cc = users.join(','); 
					cc=cc.substring(0, cc.lastIndexOf(','));  
					$("#GROUPNAME_TEXT_ID").val(cc);
						 
					//}else $("#GROUPNAME_TEXT").val( data);
                    $("#GROUPNAME_TEXT").prop("disabled",true);
                }
            })
		}
        //});
		function removeByValue(arr, val) {
		  for(var i=0; i<arr.length; i++) {
			if(arr[i] == val) {
			  arr.splice(i, 1);
			  break;
			}
		  }
		}

		function removeItem(value){
		
			$.ajax({
					url: "index.php?s=/Api/getFlowGroupName&actt=1",
					dataType: "json",
					data: {
						"groupUserId": value,
					},
					success: function (data) {
						//if($("#GROUPNAME_TEXT").val() !=''){
							//console.log(data);
							//data= eval('(' + data + ')')
							var  GROUPNAME_TEXT = new Array();
							
							if($("#GROUPNAME_TEXT").val().length>0) GROUPNAME_TEXT = $("#GROUPNAME_TEXT").val().split(',');
							 
							//alert(GROUPNAME_TEXT.split(','));
							var  GROUPNAME_TEXT_ID = new Array();
							if($("#GROUPNAME_TEXT_ID").val().length>0) GROUPNAME_TEXT_ID = $("#GROUPNAME_TEXT_ID").val().split(',');
							 
						for(var i in data ){ 

							/*for(var ii in GROUPNAME_TEXT){ 
								if(GROUPNAME_TEXT[ii] != data[i]['SinUserName']){
									 
									GROUPNAME_TEXT_NEW.push(GROUPNAME_TEXT[ii]);
								}
							} 
							for(var ii in GROUPNAME_TEXT_ID){
								if(GROUPNAME_TEXT_ID[ii] != data[i]['USERNAME']){
									GROUPNAME_TEXT_ID_NEW.push(GROUPNAME_TEXT_ID[ii]);
								}
							}*/
							//GROUPNAME_TEXT.del(data[i]['SinUserName']);
							//GROUPNAME_TEXT_ID.del(data[i]['USERNAME']);
							removeByValue(GROUPNAME_TEXT,data[i]['SinUserName']);
							removeByValue(GROUPNAME_TEXT_ID,data[i]['USERNAME']); //alert(GROUPNAME_TEXT_ID);
							 
						}
						var cc = GROUPNAME_TEXT.join(',');
						//cc=cc.substring(0, cc.lastIndexOf(','));  
						$("#GROUPNAME_TEXT").val(cc);
						var ccc = GROUPNAME_TEXT_ID.join(',');  
						//ccc=ccc.substring(0, ccc.lastIndexOf(','));  
						$("#GROUPNAME_TEXT_ID").val(ccc);
						//alert(cc);	 
						//}else $("#GROUPNAME_TEXT").val( data);
						$("#GROUPNAME_TEXT").prop("disabled",true);
					}
				});
		}

    </script><?php endif; ?>
</body>
</html>