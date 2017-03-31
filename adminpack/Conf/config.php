<?php
$config = array(
	'APP_GROUP_LIST'=>'Admin,Touch',
	'DEFAULT_GROUP' => 'Admin',
	/*'DB_TYPE'=>'mysql',
	'DB_HOST'=>'127.0.0.1',
	'DB_NAME'=>'365tf',
	'DB_USER'=>'root',
	'DB_PWD'=>'',
	'DB_PORT'=>'3306',
	'DB_PREFIX'=>'',
	'DB_CHARSET'=>'gbk',*/
	'DB_TYPE' => 'Oracle', // 数据库类型
    //'DB_HOST' => '202.102.83.186', // 服务器地址
    'DB_NAME' => 'oms2', // 数据库名
    'DB_USER' => 'omstest', // 用户名
    'DB_PWD' => 'omstest', // 密码
    'DB_PORT' => '1521', // 端口
	 'DB_CHARSET'=>  'gbk',// 数据库编码
	 'DB_PREFIX' => '', // 数据库表前缀
	//'DB_CASE_LOWER'=>false,

    'DEFAULT_CHARSET' => 'gb2312', // 默认输出编码
	'URL_CASE_INSENSITIVE'  => false,   // 默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'             => 3,       // URL访问模式,可选参数0、1、2、3,代表以下四种模式：
    // 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式，提供最好的用户体验和SEO支持
    'URL_PATHINFO_DEPR'     => '/',	// PATHINFO模式下，各参数之间的分割符号
    'URL_HTML_SUFFIX'       => '',  // URL伪静态后缀设置
	'VAR_URL_PARAMS'      => '_URL_', // PATHINFO URL参数变量

	'TMPL_CACHE_ON' => false, 
	'PAGESIZE'=>20,
	'DEFAULTPWD'=>'House365**',//默认密码
	//数据库类型
    'DBTYPE' =>array(
		 
		'1'=>'ORACLE',
		'2'=>'MYSQL'
		
	),

	 
 
	'NONEPOWER'=>array(
		'Index/login',//登陆模块
		'Index/verify',//验证码模块
		'Index/loginOut',//退出模块
        'Mall/api_check',//电商登录验证模块
		'Upload/save2oracle',//文件上传
		'Upload/showfile'//文件下载
	),
	'NONEROLE'=>array(
		'Index/index',//默认页
		'Index/top',//顶部页面
		'Index/left',//左边页面
		'Index/welcome',//默认页面
		'Refer/getchannel',//默认页面
		'Client/checkid',//默认页
		'Api/is_over_payout_limit',
        'Upfile/ueditorUpImage',
        'Project/ajax_get_project_list',//获取项目信息
        'Project/ajax_get_feescale',//获取费用信息
        'Project/ajax_get_houseinfo_by_pid',//获取项目楼盘信息
        'Project/asyncGetDSProjects',  // 获取所有的电商项目
        'Supplier/get_supplier_by_keyword',//根据关键词获取供应商信息
        'Member/batch_change_status',
        'Business/opinionFlow',
        'Business/index',
        'MemberDistribution/index',
        'Advert/index',
        'Business/fund_pool_status',
        'Member/get_minfo_by_telno',
		'Member/changeStatus',
		'Warehouse/ajax_get_warehouse_num',
        'Warehouse/ajax_get_from_warehouse2',
        'Purchasing/use_detail_list',
        'Purchase/check_purchase_list_by_pid',
        'Purchase/getFlowId',  // 获取采购工作流ID
        'Purchase/opinionFlow',  // 采购工作流权限
        'Purchase/bulk_purchase_opinionFlow',  // 大宗采购
        'Supplier/ajax_add_supplier_info',
        'Supplier/get_lower_price_supplier',
		'Purchasing/append_to_contract',
        'ProjectAnalysis/occurred_cost_list',
        'Project/ajax_get_project_budget_sale_by_pid',
		'Upload/showfile',
        'Warehouse/ajaxMatchedStorage',  // 获取库存商品信息
		'Flow/workstep',
		'Flow/flow_show',
		'Flow/flow_info',
		'Flow/flow_files',
		'Flow/flow_opinions',
		'Flow/flow_submit',
		'Finalaccounts/workstep',
		'Finalaccounts/flow_show',
		'Finalaccounts/flow_info',
		'Finalaccounts/flow_handle',
		'Finalaccounts/flow_files',
		'Finalaccounts/flow_opinions',
		'Flow/recoverFlow',
        'Group/searchByName',  // 搜索权限组
        'Finalaccounts/show',
        'Flow/flowList',
        'Activity/index',  // 活动审批工作流首页
        'Activity/process',  // 活动审批工作流数据展示页
        'Activity/show',  // 活动审批工作流数据展示页
		'ProjectSet/show',
		'ProjectChange/show',
		'ProjectTermination/show', 
        'Activ/index',  // 活动审批工作流首页
        'Activ/process',  // 活动审批工作流数据展示页
        'Activ/opinionFlow',  // 活动审批工作流
        'Activ/opinionFlowChange',  // 变更工作流
        'Activ/XiangMuOpinionFlow',  // 项目下活动审批
		'/Activ/getContractList',
        'Provider/getUsers',  // 展示用户列表
        'Purchase/index',  // 采购审批工作流数据展示页
        'Purchase/process',  // 采购申请页面
		'InboundUse/process',
		'InboundUse/opinionFlow', // 置换仓库工作流
		'Displace/checkDisplaceDetailById',
		'Displace/checkDisplaceFlow',
		'Displace/ajaxPostInboundUse',
		'Displace/getTotalMoney',
        'Displace/process',  // 置换申请页面
        'Displace/opinionFlow',  // 置换申请工作流
        'Loan/process', // 借款申请显示页面
        'Loan/opinionFlow',  // 借款审批工作流
		'ProjectSet/opinionFlow',//
		'ProjectChange/opinionFlow',//
		'Finalaccounts/opinionFlow',
		'ProjectTermination/opinionFlow',
        'Payout_change/process', // 垫资比例调整主页
        'Payout_change/opinionFlow', // 垫资比例工作流审批页面
        'InvoiceRecycle/process',
        'InvoiceRecycle/opinionFlow',
        'MemberRefund/process',
        'MemberRefund/opinionFlow',
        'Feescale_change/process',
        'Feescale_change/opinionFlow',
		'ChangeInvoice/process',
        'ChangeInvoice/opinionFlow',
		'MemberDiscount/process',
        'MemberDiscount/opinionFlow',
		'Advert/process',
		'Advert/opinionFlow',
		'Cost/process',
		'Cost/opinionFlow',

		'PurchasingBee/show',
		'PurchasingBee/opinionFlow',
		'PurchaseNocash/process',
		'PurchaseNocash/opinionFlow',
		'Benefits/process',
		'Benefits/opinionFlow',
		'Benefits/update_benefits_data',
		'BenefitFlow/process',
		'BenefitFlow/opinionFlow',
		'BenefitFlow/update_benefits_data',
		'House/ajaxIsFundPoolProject',
		'Reimbursement/apply_purchase_reim',

 

        'Index/chooseCate', // todo
        'Activ/getContractList',  // 获取合同列表

        // ntd
        'Reimbursement/apply_purchase_reim',
        'House/ajaxIsFundPoolProject',  // 查询是否资金池费用
        'Financial/ajaxRemainInvoicePayAmount', // 开票记录的剩余回款金额
        'Displace/commitSaleChange',
        'DisplaceSaleChange/process',  // 售卖变更申请工作流
        'DisplaceSaleChange/opinionFlow',  // 售卖变更申请工作流
		'AdvanceChaoe/show',
		'AdvanceChaoe/opinionFlow',
		'Project/ajax_get_fx_feescale',
		'Member/moveProject',
    ),
 
    
   'DB_TRIGGER_PREFIX'	=>	'TIG_',
   'DB_SEQUENCE_PREFIX' =>	'SEQ_',

   'filterType' => array('1'=>'模糊','2'=>'为空','3'=>'=','4'=>'非空','5'=>'>=','6'=>'<=','7'=>'>','8'=>'<','9'=>'in'),
   //'SHOW_PAGE_TRACE' => true,
   //域名
   'DOMAIN_NAME'=>'http://oms.house365.com/test/adminpack'

);  
    
return $config;
?>