<?php
$config = array(
    /*'DB_TYPE'=>'mysql',
    'DB_HOST'=>'127.0.0.1',
    'DB_NAME'=>'365tf',
    'DB_USER'=>'root',
    'DB_PWD'=>'',
    'DB_PORT'=>'3306',
    'DB_PREFIX'=>'',
    'DB_CHARSET'=>'gbk',*/
    'DB_TYPE' => 'oracle', // ���ݿ�����
    //'DB_HOST' => '192.168.105.94', // ��������ַ
    'DB_NAME' => 'lorcl', // ���ݿ���
    'DB_USER' => 'oms', // �û���
    'DB_PWD' => 'oms', // ����
    'DB_PORT' => '1521', // �˿�
    'DB_CHARSET'=>  'gbk',      // ���ݿ����
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

    //'SHOW_PAGE_TRACE'=>true,    //��������ҳ��
    //'LOAD_EXT_CONFIG'=>$_COOKIE[CITYEN].'Config',//���ֻ�ܶ�̬���ط������
    /****************���ݿ�����*****************/
    /*memcache ����*/
    /*************����************/
    'department_aray'=>array(
        '1'=>"������",
        '2'=>"�·���",
        '3'=>"���ַ���",
        '4'=>"�Ҿ���",
        '5'=>"������",
        '6'=>"�ͷ���"
    ),
    /*************����************/
    'power_come_from'=>array(
        '1'=>"�·�",
        '2'=>"���ַ�",
        '3'=>"�Ҿ�",
        '4'=>"����"
    ),
    /*************����************/
    'city_array'=>array(
        "1000000"   =>"�Ͼ�",
        "3000000"   =>"����",
        "7000000"   =>"�ߺ�",
        "8000000"   =>"�Ϸ�",
        "9000000"   =>"����",
        "10000000"  =>"����",
        "11000000"  =>"����",
        "15000000"  =>"��ɽ",
        "17000000"  =>"����",
        "18000000"  =>"����",
        "19000000"  =>"����",
        "52000000"  =>"���",
        "49000000"  => "��ͨ",
        "128000000" => "����",
        "55000000"  => "����",
        "51000000"  => "̩��",
        "234000000" => "����",
        "42000000"  => "����",
        "235000000" => "�ɶ�",
        "50000000"  => "����",
        "57000000"  => "����",
        "188000000" => "�人",
        "99000000"  => "������",
        "175000000" => "ʯ��ׯ",
        "43000000"  => "����",
        '237000000' => '����',
        '231000000' => '����',
        '415000000' => '����',
        '238000000' => '����',
        '112000000' => '��ˮ',
        '239000000' => '����',
        '172000000' => '��ͷ',
        '48000000'  => '����ɽ',
        '131000000' => '����',
        '229000000' => '����',
        '84000000'  => '����',
        '126000000' => '����',
        '64000000'  => '����',
        '146000000' => '����',
        '220000000' => '��ɽ',
        '221000000' => '�麣',
        '82000000'  => '����',
        '233000000' => '����',
        '78000000'  => '��',
        '79000000'  => '����',
        '80000000'  => '����',
        '421000000' => '����',
        '91000000'  => '��ɽ',
        '98000000'  => '����',
        '110000000' => '֣��',
        '114000000' => '����',
        '139000000' => '����',
        '147000000' => '�ϲ�',
        '178000000' => '����',
        '185000000' => '̫ԭ',
        '423000000' => '����',
        '425000000' => '����',
        '427000000' => '����',
        '82000000'  => '����',
        '85000000'  => '��ѵ',
        '86000000'  => 'Ȫ��',
        '87000000'  => '����',
        '88000000'  => '��ʩ',
    ),
    'city_config_array'=>array(
        "1000000"   =>"nj",
        "3000000"   =>"sz",
        "7000000"   =>"wh",
        "8000000"   =>"hf",
        "9000000"   =>"wx",
        "10000000"  =>"hz",
        "11000000"  =>"cz",
        "15000000"  =>"ks",
        "17000000"  =>"xa",
        "18000000"  =>"cq",
        "19000000"  =>"sy",
        "52000000"  =>"tj",
        "49000000"  => "nt",
        "128000000" => "jx",
        "55000000"  => "yz",
        "51000000"  => "tz",
        "234000000" => "hb",
        "42000000"  => "bb",
        "235000000" => "cd",
        "50000000"  => "xz",
        "57000000"  => "yx",
        "188000000" => "wuhan",
        "99000000"  => "hrb",
        "175000000" => "sjz",
        "43000000"  => "chuzhou",
        '237000000' => 'cc',
        '231000000' => 'suzhou',
        '415000000' => 'fuling',
        '238000000' => 'chaozhou',
        '112000000' => 'hs',
        '239000000' => 'jieyan',
        '172000000' => 'st',
        '48000000'  => 'mas',
        '131000000' => 'ly',
        '229000000' => 'zhuzhou',
        '84000000'  => 'dz',
        '126000000' => 'jining',
        '64000000'  => 'bd',
        '146000000' => 'nb',
        '220000000' => 'zs',
        '221000000' => 'zh',
        '82000000'  => 'dl',
        '233000000' => 'fy',
        '78000000'  => 'jh',
        '79000000'  => 'hd',
        '80000000'  => 'zunyi',
        '421000000' => 'jr',
        '91000000'  => 'fs',
        '98000000'  => 'gz',
        '110000000' => 'zz',
        '114000000' => 'huizhou',
        '139000000' => 'luoyang',
        '147000000' => 'nc',
        '178000000' => 'shenzhen',
        '185000000' => 'ty',
        '423000000' => 'lh',
        '425000000' => 'gy',
        '427000000' => 'nn',
        '82000000'  => 'lz',
        '85000000'  => 'peixun',
        '86000000'  => 'qz',
        '87000000'  => 'km',
        '88000000'  => 'enshi',
    ),
    /*************����************/


    /*************����************/
    'NONEPOWER'=>array(
        'Index/login',//��½ģ��
        'Index/verify',//��֤��ģ��
        'Index/loginOut',//�˳�ģ��
        'Mall/api_check',//���̵�¼��֤ģ��
    ),
    'NONEROLE'=>array(
        'Index/index',//Ĭ��ҳ
        'Index/top',//����ҳ��
        'Index/left',//���ҳ��
        'Index/welcome',//Ĭ��ҳ��
        'Refer/getchannel',//Ĭ��ҳ��
        'Client/checkid',//Ĭ��ҳ
        'Upfile/ueditorUpImage',
        'Project/ajax_get_project_list',//��ȡ��Ŀ��Ϣ
        'Project/ajax_get_feescale',//��ȡ������Ϣ
        'Project/ajax_get_houseinfo_by_pid',//��ȡ��Ŀ¥����Ϣ
        'Member/show_pay_list',
        'Member/show_refund_list',
        'Member/show_bill_list',
        'Member/apply_invoice',
        'Member/export_member',
        'Business/opinionFlow',
        'Business/cost_list',
        'Business/fund_pool_status',
        'Member/get_minfo_by_telno',
        'Member/merchant_manage',
        'MemberRefund/refund_list',
        'MemberRefund/add_to_audit_list',
        'MemberRefund/refund_audit_list',
        'MemberRefund/opinionFlow',
        'MemberRefund/delete_from_audit_list',
        'MemberRefund/delete_from_details',
        'MemberRefund/refund_progress_list'
    ),

    //�����Ʒ���״̬
    'SORT_STATUS' =>array(
        '0'=>'δ�ύ���',
        '1'=>'�ȴ����',
        '2'=>'�����ͨ��',
        '3'=>'���δͨ��'
    ),

    //��������״̬
    'LOAN_STATUS' =>array(
        '0'=>'�������ϣ��ȴ���ϵ',
        '1'=>'����ͨ�����ȴ�ǩԼ',
        '2'=>'ǩԼ�ɹ����ȴ��ſ�',
        '3'=>'�ſ�ɹ�',
        '4'=>'�ſ�ʧ��'
    ),
    'DB_TRIGGER_PREFIX'	=>	'TIG_',
    'DB_SEQUENCE_PREFIX' =>	'SEQ_',

    'filterType' => array('1'=>'ģ��','2'=>'Ϊ��','3'=>'=','4'=>'�ǿ�','5'=>'>=','6'=>'<=','7'=>'>','8'=>'<','9'=>'in'),

);

return $config;
?>