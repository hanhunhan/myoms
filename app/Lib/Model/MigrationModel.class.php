<?php

/**
 * 导入方法 MODEL类
 *
 * @author liuhu
 */
class MigrationModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    private $usertable = 'USERS';
	private $feetype = 'FEE';//费用类型
	private $member = 'CARDMEMBER';//办卡会员
	private $project = 'PROJECT';//办卡会员
	private $casetable = 'CASE';//案例
	private $feetable = 'FEE';//案例
	private $project_status = array(//新老项目状态映射
		'0'=>'2',//表示未提交审核状态
		'1'=>'6',//表示审核流程中
		'2'=>'3', //审核通过状态
		'5'=>'5',// 被终止（工作流被终止）
		'10'=>'3'//被废弃（项目进行中被申请修改）
		
	);
	private $project_bstatus = array(//新老项目电商状态映射
		'0'=>'1',
		'1'=>'1',
		'2'=>'2',
		'5'=>'1',
		'10'=>'5'//?
		
	);
    private $financialconfirm = array(//新老项目财务确认映射
		'1' => '3',//财务已确认
		'2' => '1'//财务未确认

	);
	private $payment_financialconfirm = array(//新老项目付款明细财务确认映射
		'1' => '1',//财务已确认
		'2' => '0'//财务未确认

	);
	private $refund_status = array(//新老项目退款状态映射 审核中的都设置为0
		'1' => '0',//未提交审核
		'2' => '0',//审核中 2 
		'5' => '3',//终止(审核不通过)
		'10' => '4'//审核通过

	);
	private $refund_list_status = array(//退款单状态映射 审核中的都设置为0
		'1' => '0',//未提交审核
		'2' => '0',//审核中 1
		'5' => '2',//终止(审核不通过)
		'10' => '3'//审核通过
	);
	private $invoicestatu = array(//开票状态映射 
		'1' => '1',//"未开",
		'5' => '1',//"申请中", 申请中的状态都映射为未开  以便在新系统中重新申请
		'2' => '3',//"已开未领",
		'3' => '4',//"已领",
		'4' => '5'//"已收回"
	);
	private $cardstatus = array(//办卡状态映射 
		'1' => '1', //"已办未成交",
		'2' => '2',//"已办已认购",
		'3' => '3',//"已办已签约",
		'4' => '4'//"退卡"
	);
	private $invoice_status = array(//发票状态映射 
		'1' =>'1', //"未开",
		'5' =>'5', //"申请中",
		'2' =>'2',//"已开未领",
		'3' =>'3',//"已领",
		'4' =>'4' //"已收回",
	);
	private $receipt_status = array(//收据状态
		'2' =>'2', //"已开未领",
		'3' =>'3', //"已领",
		'4' =>'4' //"已收回",
	);
    private $city_adduid = array(//城市负责人
		'1' => 'zhuye2', // 南京 朱晔
		'2' => 'wumengxu',//苏州 吴梦煦
		'6' =>'zhou_qian', //合肥 周倩 ？？
		'7' => 'mujuan',//芜湖 牧娟
		'4' => 'lishuangshuang',//无锡 李双双
		'8' =>'fenglanjuan', //杭州 封兰娟
		'9' =>'hanjing',//西安 韩婧
		'101' => 'liujun2',//'蚌埠', 刘军
		'103' => 'xujiayi',//'马鞍山', 许嘉懿
		'102' => 'zhangbinbin1',//'滁州', 张彬彬
		'104' => 'wangchengwu',//'阜阳', 王成武
		'111' => 'wangshangpei'//'六安',王尚培

		
	);
    //构造函数
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    //获取用户表表名
    public function get_users_table_name()
    {   
        return $this->tablePrefix.$this->usertable;
    }
	 //获取费用类型表表名
    public function get_fee_table_name()
    {   
        return $this->tablePrefix.$this->feetype;
    }
	//获取 表名
    public function get_table_name($table)
    {   
        return $this->tablePrefix.$table;
    }
    
    //根据用户登录名获取用户id
	public function get_users_id($username){
		$tablename = $this->get_users_table_name();
		$info =  $this->table($tablename)->where("USERNAME='$username'")->field('ID')->find();
		return $info['ID'];
	}
	//根据用户登录名获取用户名称
	public function get_users_name($username){
		$tablename = $this->get_users_table_name();
		$info =  $this->table($tablename)->where("USERNAME='$username'")->field('NAME')->find();
		return $info['NAME'];
	}
	//更具用户登陆名或者部门id
	public function get_users_deptid($username){
		$tablename = $this->get_users_table_name();
		$info =  $this->table($tablename)->where("USERNAME='$username'")->field('DEPTID')->find();
		return $info['DEPTID'];
	}
	//根据用户姓名获取用户id
	public function get_users_id_byname($name){
		$tablename = $this->get_users_table_name();
		$info =  $this->table($tablename)->where("NAME='$name'")->field('ID')->select(); 
		return  $info;
	}
	//根据会员所属城市 获取操作人id
	public function get_city_adduid($city){
		$tablename = $this->get_users_table_name();
		$username = $this->city_adduid[$city];
		return $this->get_users_id($username); 
		 
	}

	//根据原项目状态获取新系统对应状态值
	public function get_project_status($status,$sdate,$edate){
		if($status==2 and $edate < time() ){//已完结的项目
			return 3;
		}elseif($status==2 and $edate > time() and  $sdate < time() ){//立项办结 执行中
			return 3;
		}elseif($status==2 and  $sdate > time() ){//立项办结 时间未到
			return 3;
		}
		return $this->project_status[$status];

	}
	//根据原项目状态获取新系统对应状态值
	public function get_project_bstatus($status,$sdate,$edate){
		if($status==2 and $edate < time() ){//已完结的项目
			return 4;//周期结束
		}elseif($status==2 and $edate > time() and  $sdate < time() ){//立项办结 执行中
			return 2;
		}elseif($status==2 and  $sdate > time() ){//立项办结 时间未到
			return 2;
		}
		return $this->project_bstatus[$status];

	}
	//根据原项目财务确认状态获取新系统对应值
	public function get_financialconfirm($status){
		return $this->financialconfirm[$status];

	}
	//根据原项目付款明细财务确认状态获取新系统对应值
	public function get_payment_financialconfirm($status){
		return $this->payment_financialconfirm[$status];

	}
	//根据原项目退款状态获取新系统对应值
	public function get_refund_status($status){
		return $this->refund_status[$status];

	}
	//根据原项目退款单状态获取新系统对应值
	public function get_refund_list_status($status){
		return $this->refund_list_status[$status];

	}
	//根据原项目开票状态获取新系统对应值
	public function get_invoicestatu($status){
		return $this->invoicestatu[$status];

	}
	//根据原项目办卡状态获取新系统对应值
	public function get_cardstatus($status){
		return $this->cardstatus[$status];
	}
	//根据原项目发票状态获取新系统对应值
	public function get_invoice_status($status){
		return $this->invoice_status[$status];
	}
	//根据原项目收据状态获取新系统对应值
	public function get_receipt_status($status){
		return $this->receipt_status[$status];
	}
	//根据费用类型的pinyin获取对应的id
	public function get_fee_id($py){
		$tablename = $this->get_fee_table_name();
		$info =  $this->table($tablename)->where("INPUTNAME='$py'")->field('ID')->find();
		return $info['ID'];
	}
	//根据 团立方项目ID获取项目名称
	public function get_project_name($tlfid){
		$tablename = $this->get_table_name($this->project);
		$info =  $this->table($tablename)->where("TLF_PROJECT_ID='$tlfid'")->field('PROJECTNAME')->find();
		return $info['PROJECTNAME'];

	}
	//根据团立方项目ID 获取项目ID
	public function get_project_id($tlfid){  
		$tablename = $this->get_table_name($this->project);
		$info =  $this->table($tablename)->where("TLF_PROJECT_ID='$tlfid'")->field('ID')->find();
		return $info['ID'];

	}
	//根据 项目ID 获取Case ID
	public function get_case_id($id){
		$tablename = $this->get_table_name($this->casetable);
		$info =  $this->table($tablename)->where("SCALETYPE=1 and PROJECT_ID='$id'")->field('ID')->find();
		return $info['ID'];

	}
	//根据费用py获取id
	public function get_fee_id_new($py){
		$tablename = $this->get_table_name($this->feetable);
		$info =  $this->table($tablename)->where("INPUTNAME='$py'")->field('ID')->find();
		return $info['ID'];

	}


	 
     
    
    
     
}

 