<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <link rel="stylesheet" href="//cdn.bootcss.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <link rel="stylesheet" href="./Public/css/AdminLTE.min.css">
    <link rel="stylesheet" href="./Public/css/welcome.css">

    <script type="text/javascript" src="./Public/validform/js/jquery-1.9.1.min.js"></script>
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            个人桌面
            <small>Version 2.0.1</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> 首页</a></li>
            <li class="active">面板</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <i class="fa fa-bullhorn"></i>
                        <h3 class="box-title">公告</h3>
                    </div>
                    <div class="box-body border-radius-none ">
                        <?php echo ($view_data['notice']); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php foreach($view_data['quick'] as $key=>$val){ ?>
            <div class="col-md-2">
                <a href="<?php echo ($val['url']); ?>">
                    <div class="info-box">
                        <span class="info-box-icon <?php echo ($val['bg_color']); ?>"><i class="<?php echo ($val['i_class']); ?>"></i></span>

                        <div class="info-box-content">
                            <span class="info-box-text text-navy text-bg"><?php echo ($val['title']); ?></span>
                            <span class="info-box-number text-navy text-sm"><?php echo ($val['num']); ?></span>
                        </div>
                    </div>
                </a>
            </div>
            <?php } ?>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h5 class="box-title text-sm"><i class="fa fa-tasks"></i>&nbsp;待办工作流</h5>
                        <div class="box-tools pull-right">
                            <!--<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>-->
                            <!--<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>-->
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <?php if(empty($view_data)){ ?>
                            <div class="center">暂无工作流</div>
                            <?php }else{ ?>
                            <table class="table no-margin">
                                <thead>
                                <tr>
                                    <th width="5%">序号</th>
                                    <th width="20%">类别</th>
                                    <th width="10%">发起人</th>
                                    <th width="5%">步骤</th>
                                    <th width="40%">说明</th>
                                    <th width="20%">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach($view_data['flow_data'] as $key=>$val){ ?>
                                <tr>
                                    <td width="5%"><?php echo ($key+1); ?></td>
                                    <td><span class="label" style="background-color:<?php echo ($flow_color[$val['PINYIN']]); ?>"><?php echo ($val['FLOWTYPE']); ?></span>
                                    </td>
                                    <td><?php echo ($val['NAME']); ?></td>
                                    <td width="5%"><?php echo ($val['MAXSTEP']); ?></td>
                                    <td><span title="<?php echo ($val['INFO']); ?>"
                                              href="#"><?=mb_substr($val['INFO'],0,30,"gb2312")?></span></td>
                                    <td><a class="contrtable-link btn btn-danger btn-xs"
                                           onclick="handleFlow(<?php echo ($val['ID']); ?>)" href="javascript:;">办理</a>&nbsp;&nbsp;&nbsp;&nbsp;<a
                                            class="contrtable-link btn btn-success btn-xs"
                                            href="<?php echo U('Flow/viewFlow');?>&FLOWID=<?php echo ($val['ID']); ?>" target="_blank">流程图</a>
                                    </td>
                                </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="box-footer clearfix"></div>
                </div>
            </div>
        </div>
    </section>

    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <b>Version</b> 2.0.1
        </div>
        <strong>Copyright&copy2006-2016 <a href="#">江苏三六五网络股份有限公司</a>.</strong> All rights reserved.
    </footer>
</div>
<script>
    function handleFlow(flowId) {
        var url = '';
        var appUrl = "__APP__";

        $.ajax({
            url: "<?php echo U('Api/getFlowType');?>",
            dataType: "json",
            async: false,
            data: {
                'flowId': flowId
            },
            success: function (obJect) {
                if (obJect['nopower'] == 1) {
                    alert('您没有权限操作该城市的流程！');
                } else {
                    var type = '&flowTypePinYin=' + obJect[0]['PINYIN'];
                    switch (obJect[0]['PINYIN']) {
                        case 'yewujintie':
                            url = appUrl + '/Touch/Benefits/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'caigoushenqing':
                            if (obJect[0]['CASEID'] > 0) {
                                type += '&purchaseType=purchase';
                            } else {
                                type += '&purchaseType=bulkPurchase';
                            }
                            url = appUrl + '/Touch/Purchase/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'feifuxianchengbenshenqing':  // 非现金成本申请
                            url = appUrl + '/Touch/PurchaseNocash/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + '&TAB_NUMBER=26' + type;
                            break;
                        case 'chengbenhuabo':  // 成本划拨
                            url = appUrl + '/Touch/Cost/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'lixiangbiangeng': // 立项变更
                            url = appUrl + '/Touch/ProjectChange/show&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + '&CHANGE=-1&type=1&flowType=lixiangbiangeng&active=1&tabNum=20' + type;
                            break;
                        case 'biaozhuntiaozheng':  // 标准调整
                            url = appUrl + '/Touch/Feescale_change/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'hetongkaipiao':  // 合同开票
                            url = appUrl + '/Touch/Advert/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'tksq':  // 退款申请
                            url = appUrl + '/Touch/MemberRefund/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'dianziedu': // 垫资比例调整
                            url = appUrl + '/Touch/Payout_change/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'lixiangshenqing':  // 立项申请
                            url = appUrl + '/Touch/ProjectSet/show&tabNum=20&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'huiyuantuipiao':  // 会员退票
                            url = appUrl + '/Touch/InvoiceRecycle/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'xiangmuxiahuodong':  // 项目下活动
//                                url = appUrl + '/Touch/Activ/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + '&active=1&tabNum=10';
//                                break;
                        case 'dulihuodongbiangeng':  // 独立活动
//                                url = appUrl + '/Activ/opinionFlowChange&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + '&active=1&CHANGE=-1&type=3&tabNum=9&activId=' + obJect[0]['ACTIVID'];
//                                break;
                        case 'xiangmuxiahuodongbiangeng':  // 项目下活动变更
//                                url =
//                                        appUrl + '/Activ/opinionFlowChange&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + '&activId=' + obJect[0]['ACTIVID'] + '&active=1&CHANGE=-1&type=2&tabNum=9';
//                                break;
                        case 'dulihuodong':  // 独立活动
                            url = appUrl + '/Touch/Activ/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + '&tabNum=8' + type;
                            break;
                        case 'xiangmuzhongzhi':  // 项目终止
                            url =
                                    appUrl + '/Touch/ProjectTermination/show&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'xiangmujuesuan':  // 项目决算
                            url =
                                    appUrl + '/Touch/Finalaccounts/show&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'jiekuanshenqing':  // 借款申请
                            url =
                                    appUrl + '/Touch/Loan/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'jianmianshenqing':  // 会员减免
                            url =
                                    appUrl + '/Touch/MemberDiscount/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'huiyuanhuanpiao':  // 会员换票
                            url =
                                    appUrl + '/Touch/ChangeInvoice/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'yusuanqita':  // 预算其他
                            url = appUrl + '/Touch/BenefitFlow/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'];
                            break;
                        case 'xiaomifengchaoe':  // 小蜜蜂
                            url = appUrl + '/Touch/PurchasingBee/show&prjid=0&flowId=' + flowId + '&beeId=' + obJect[0]['RECORDID'] + '&TAB_NUMBER=25';
                            break;
                        case 'zhihuanshenqing':
                            url = appUrl + '/Touch/Displace/process&flowId=' + flowId + '&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'shoumai':
                            url = appUrl + '/Touch/InboundUse/process&flowId=' + flowId +'&RECORDID='+obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type + '&flowDisplaceType=1';
                            break;
                        case 'neibulingyong':
                            url = appUrl + '/Touch/InboundUse/process&flowId=' + flowId +'&RECORDID='+obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type + '&flowDisplaceType=2';
                            break;
                        case 'baosun':
                            url = appUrl + '/Touch/InboundUse/process&flowId=' + flowId +'&RECORDID=' + obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type + '&flowDisplaceType=3';
                            break;
                        case 'shoumaibiangeng':
                            url = appUrl + '/Touch/DisplaceSaleChange/process&flowId=' + flowId +'&RECORDID='+obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type;
                            break;
                        case 'dianzibilichaoe':
                            url = appUrl + '/Touch/AdvanceChaoe/show&flowId=' + flowId +'&RECORDID='+obJect[0]['RECORDID'] + '&CASEID=' + obJect[0]['CASEID'] + type ;
                            break;
                    }
                    window.location.href = url;
                }
            }
        });
    }
</script>
</body>
</html>