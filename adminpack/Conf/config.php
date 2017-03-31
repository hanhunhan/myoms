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
	'DB_TYPE' => 'Oracle', // ���ݿ�����
    //'DB_HOST' => '202.102.83.186', // ��������ַ
    'DB_NAME' => 'oms2', // ���ݿ���
    'DB_USER' => 'omstest', // �û���
    'DB_PWD' => 'omstest', // ����
    'DB_PORT' => '1521', // �˿�
	 'DB_CHARSET'=>  'gbk',// ���ݿ����
	 'DB_PREFIX' => '', // ���ݿ��ǰ׺
	//'DB_CASE_LOWER'=>false,

    'DEFAULT_CHARSET' => 'gb2312', // Ĭ���������
	'URL_CASE_INSENSITIVE'  => false,   // Ĭ��false ��ʾURL���ִ�Сд true���ʾ�����ִ�Сд
    'URL_MODEL'             => 3,       // URL����ģʽ,��ѡ����0��1��2��3,������������ģʽ��
    // 0 (��ͨģʽ); 1 (PATHINFO ģʽ); 2 (REWRITE  ģʽ); 3 (����ģʽ)  Ĭ��ΪPATHINFO ģʽ���ṩ��õ��û������SEO֧��
    'URL_PATHINFO_DEPR'     => '/',	// PATHINFOģʽ�£�������֮��ķָ����
    'URL_HTML_SUFFIX'       => '',  // URLα��̬��׺����
	'VAR_URL_PARAMS'      => '_URL_', // PATHINFO URL��������

	'TMPL_CACHE_ON' => false, 
	'PAGESIZE'=>20,
	'DEFAULTPWD'=>'House365**',//Ĭ������
	//���ݿ�����
    'DBTYPE' =>array(
		 
		'1'=>'ORACLE',
		'2'=>'MYSQL'
		
	),

	 
 
	'NONEPOWER'=>array(
		'Index/login',//��½ģ��
		'Index/verify',//��֤��ģ��
		'Index/loginOut',//�˳�ģ��
        'Mall/api_check',//���̵�¼��֤ģ��
		'Upload/save2oracle',//�ļ��ϴ�
		'Upload/showfile'//�ļ�����
	),
	'NONEROLE'=>array(
		'Index/index',//Ĭ��ҳ
		'Index/top',//����ҳ��
		'Index/left',//���ҳ��
		'Index/welcome',//Ĭ��ҳ��
		'Refer/getchannel',//Ĭ��ҳ��
		'Client/checkid',//Ĭ��ҳ
		'Api/is_over_payout_limit',
        'Upfile/ueditorUpImage',
        'Project/ajax_get_project_list',//��ȡ��Ŀ��Ϣ
        'Project/ajax_get_feescale',//��ȡ������Ϣ
        'Project/ajax_get_houseinfo_by_pid',//��ȡ��Ŀ¥����Ϣ
        'Project/asyncGetDSProjects',  // ��ȡ���еĵ�����Ŀ
        'Supplier/get_supplier_by_keyword',//���ݹؼ��ʻ�ȡ��Ӧ����Ϣ
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
        'Purchase/getFlowId',  // ��ȡ�ɹ�������ID
        'Purchase/opinionFlow',  // �ɹ�������Ȩ��
        'Purchase/bulk_purchase_opinionFlow',  // ���ڲɹ�
        'Supplier/ajax_add_supplier_info',
        'Supplier/get_lower_price_supplier',
		'Purchasing/append_to_contract',
        'ProjectAnalysis/occurred_cost_list',
        'Project/ajax_get_project_budget_sale_by_pid',
		'Upload/showfile',
        'Warehouse/ajaxMatchedStorage',  // ��ȡ�����Ʒ��Ϣ
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
        'Group/searchByName',  // ����Ȩ����
        'Finalaccounts/show',
        'Flow/flowList',
        'Activity/index',  // �������������ҳ
        'Activity/process',  // ���������������չʾҳ
        'Activity/show',  // ���������������չʾҳ
		'ProjectSet/show',
		'ProjectChange/show',
		'ProjectTermination/show', 
        'Activ/index',  // �������������ҳ
        'Activ/process',  // ���������������չʾҳ
        'Activ/opinionFlow',  // �����������
        'Activ/opinionFlowChange',  // ���������
        'Activ/XiangMuOpinionFlow',  // ��Ŀ�»����
		'/Activ/getContractList',
        'Provider/getUsers',  // չʾ�û��б�
        'Purchase/index',  // �ɹ���������������չʾҳ
        'Purchase/process',  // �ɹ�����ҳ��
		'InboundUse/process',
		'InboundUse/opinionFlow', // �û��ֿ⹤����
		'Displace/checkDisplaceDetailById',
		'Displace/checkDisplaceFlow',
		'Displace/ajaxPostInboundUse',
		'Displace/getTotalMoney',
        'Displace/process',  // �û�����ҳ��
        'Displace/opinionFlow',  // �û����빤����
        'Loan/process', // ���������ʾҳ��
        'Loan/opinionFlow',  // �������������
		'ProjectSet/opinionFlow',//
		'ProjectChange/opinionFlow',//
		'Finalaccounts/opinionFlow',
		'ProjectTermination/opinionFlow',
        'Payout_change/process', // ���ʱ���������ҳ
        'Payout_change/opinionFlow', // ���ʱ�������������ҳ��
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
        'Activ/getContractList',  // ��ȡ��ͬ�б�

        // ntd
        'Reimbursement/apply_purchase_reim',
        'House/ajaxIsFundPoolProject',  // ��ѯ�Ƿ��ʽ�ط���
        'Financial/ajaxRemainInvoicePayAmount', // ��Ʊ��¼��ʣ��ؿ���
        'Displace/commitSaleChange',
        'DisplaceSaleChange/process',  // ����������빤����
        'DisplaceSaleChange/opinionFlow',  // ����������빤����
		'AdvanceChaoe/show',
		'AdvanceChaoe/opinionFlow',
		'Project/ajax_get_fx_feescale',
		'Member/moveProject',
    ),
 
    
   'DB_TRIGGER_PREFIX'	=>	'TIG_',
   'DB_SEQUENCE_PREFIX' =>	'SEQ_',

   'filterType' => array('1'=>'ģ��','2'=>'Ϊ��','3'=>'=','4'=>'�ǿ�','5'=>'>=','6'=>'<=','7'=>'>','8'=>'<','9'=>'in'),
   //'SHOW_PAGE_TRACE' => true,
   //����
   'DOMAIN_NAME'=>'http://oms.house365.com/test/adminpack'

);  
    
return $config;
?>