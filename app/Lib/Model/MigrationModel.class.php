<?php

/**
 * ���뷽�� MODEL��
 *
 * @author liuhu
 */
class MigrationModel extends Model{
    
    protected $tablePrefix  =   'ERP_';
    private $usertable = 'USERS';
	private $feetype = 'FEE';//��������
	private $member = 'CARDMEMBER';//�쿨��Ա
	private $project = 'PROJECT';//�쿨��Ա
	private $casetable = 'CASE';//����
	private $feetable = 'FEE';//����
	private $project_status = array(//������Ŀ״̬ӳ��
		'0'=>'2',//��ʾδ�ύ���״̬
		'1'=>'6',//��ʾ���������
		'2'=>'3', //���ͨ��״̬
		'5'=>'5',// ����ֹ������������ֹ��
		'10'=>'3'//����������Ŀ�����б������޸ģ�
		
	);
	private $project_bstatus = array(//������Ŀ����״̬ӳ��
		'0'=>'1',
		'1'=>'1',
		'2'=>'2',
		'5'=>'1',
		'10'=>'5'//?
		
	);
    private $financialconfirm = array(//������Ŀ����ȷ��ӳ��
		'1' => '3',//������ȷ��
		'2' => '1'//����δȷ��

	);
	private $payment_financialconfirm = array(//������Ŀ������ϸ����ȷ��ӳ��
		'1' => '1',//������ȷ��
		'2' => '0'//����δȷ��

	);
	private $refund_status = array(//������Ŀ�˿�״̬ӳ�� ����еĶ�����Ϊ0
		'1' => '0',//δ�ύ���
		'2' => '0',//����� 2 
		'5' => '3',//��ֹ(��˲�ͨ��)
		'10' => '4'//���ͨ��

	);
	private $refund_list_status = array(//�˿״̬ӳ�� ����еĶ�����Ϊ0
		'1' => '0',//δ�ύ���
		'2' => '0',//����� 1
		'5' => '2',//��ֹ(��˲�ͨ��)
		'10' => '3'//���ͨ��
	);
	private $invoicestatu = array(//��Ʊ״̬ӳ�� 
		'1' => '1',//"δ��",
		'5' => '1',//"������", �����е�״̬��ӳ��Ϊδ��  �Ա�����ϵͳ����������
		'2' => '3',//"�ѿ�δ��",
		'3' => '4',//"����",
		'4' => '5'//"���ջ�"
	);
	private $cardstatus = array(//�쿨״̬ӳ�� 
		'1' => '1', //"�Ѱ�δ�ɽ�",
		'2' => '2',//"�Ѱ����Ϲ�",
		'3' => '3',//"�Ѱ���ǩԼ",
		'4' => '4'//"�˿�"
	);
	private $invoice_status = array(//��Ʊ״̬ӳ�� 
		'1' =>'1', //"δ��",
		'5' =>'5', //"������",
		'2' =>'2',//"�ѿ�δ��",
		'3' =>'3',//"����",
		'4' =>'4' //"���ջ�",
	);
	private $receipt_status = array(//�վ�״̬
		'2' =>'2', //"�ѿ�δ��",
		'3' =>'3', //"����",
		'4' =>'4' //"���ջ�",
	);
    private $city_adduid = array(//���и�����
		'1' => 'zhuye2', // �Ͼ� ����
		'2' => 'wumengxu',//���� ������
		'6' =>'zhou_qian', //�Ϸ� ��ٻ ����
		'7' => 'mujuan',//�ߺ� ����
		'4' => 'lishuangshuang',//���� ��˫˫
		'8' =>'fenglanjuan', //���� ������
		'9' =>'hanjing',//���� ���
		'101' => 'liujun2',//'����', ����
		'103' => 'xujiayi',//'��ɽ', ���ܲ
		'102' => 'zhangbinbin1',//'����', �ű��
		'104' => 'wangchengwu',//'����', ������
		'111' => 'wangshangpei'//'����',������

		
	);
    //���캯��
    public function __construct() 
    {
        parent::__construct();
    }
    
    
    //��ȡ�û������
    public function get_users_table_name()
    {   
        return $this->tablePrefix.$this->usertable;
    }
	 //��ȡ�������ͱ����
    public function get_fee_table_name()
    {   
        return $this->tablePrefix.$this->feetype;
    }
	//��ȡ ����
    public function get_table_name($table)
    {   
        return $this->tablePrefix.$table;
    }
    
    //�����û���¼����ȡ�û�id
	public function get_users_id($username){
		$tablename = $this->get_users_table_name();
		$info =  $this->table($tablename)->where("USERNAME='$username'")->field('ID')->find();
		return $info['ID'];
	}
	//�����û���¼����ȡ�û�����
	public function get_users_name($username){
		$tablename = $this->get_users_table_name();
		$info =  $this->table($tablename)->where("USERNAME='$username'")->field('NAME')->find();
		return $info['NAME'];
	}
	//�����û���½�����߲���id
	public function get_users_deptid($username){
		$tablename = $this->get_users_table_name();
		$info =  $this->table($tablename)->where("USERNAME='$username'")->field('DEPTID')->find();
		return $info['DEPTID'];
	}
	//�����û�������ȡ�û�id
	public function get_users_id_byname($name){
		$tablename = $this->get_users_table_name();
		$info =  $this->table($tablename)->where("NAME='$name'")->field('ID')->select(); 
		return  $info;
	}
	//���ݻ�Ա�������� ��ȡ������id
	public function get_city_adduid($city){
		$tablename = $this->get_users_table_name();
		$username = $this->city_adduid[$city];
		return $this->get_users_id($username); 
		 
	}

	//����ԭ��Ŀ״̬��ȡ��ϵͳ��Ӧ״ֵ̬
	public function get_project_status($status,$sdate,$edate){
		if($status==2 and $edate < time() ){//��������Ŀ
			return 3;
		}elseif($status==2 and $edate > time() and  $sdate < time() ){//������ ִ����
			return 3;
		}elseif($status==2 and  $sdate > time() ){//������ ʱ��δ��
			return 3;
		}
		return $this->project_status[$status];

	}
	//����ԭ��Ŀ״̬��ȡ��ϵͳ��Ӧ״ֵ̬
	public function get_project_bstatus($status,$sdate,$edate){
		if($status==2 and $edate < time() ){//��������Ŀ
			return 4;//���ڽ���
		}elseif($status==2 and $edate > time() and  $sdate < time() ){//������ ִ����
			return 2;
		}elseif($status==2 and  $sdate > time() ){//������ ʱ��δ��
			return 2;
		}
		return $this->project_bstatus[$status];

	}
	//����ԭ��Ŀ����ȷ��״̬��ȡ��ϵͳ��Ӧֵ
	public function get_financialconfirm($status){
		return $this->financialconfirm[$status];

	}
	//����ԭ��Ŀ������ϸ����ȷ��״̬��ȡ��ϵͳ��Ӧֵ
	public function get_payment_financialconfirm($status){
		return $this->payment_financialconfirm[$status];

	}
	//����ԭ��Ŀ�˿�״̬��ȡ��ϵͳ��Ӧֵ
	public function get_refund_status($status){
		return $this->refund_status[$status];

	}
	//����ԭ��Ŀ�˿״̬��ȡ��ϵͳ��Ӧֵ
	public function get_refund_list_status($status){
		return $this->refund_list_status[$status];

	}
	//����ԭ��Ŀ��Ʊ״̬��ȡ��ϵͳ��Ӧֵ
	public function get_invoicestatu($status){
		return $this->invoicestatu[$status];

	}
	//����ԭ��Ŀ�쿨״̬��ȡ��ϵͳ��Ӧֵ
	public function get_cardstatus($status){
		return $this->cardstatus[$status];
	}
	//����ԭ��Ŀ��Ʊ״̬��ȡ��ϵͳ��Ӧֵ
	public function get_invoice_status($status){
		return $this->invoice_status[$status];
	}
	//����ԭ��Ŀ�վ�״̬��ȡ��ϵͳ��Ӧֵ
	public function get_receipt_status($status){
		return $this->receipt_status[$status];
	}
	//���ݷ������͵�pinyin��ȡ��Ӧ��id
	public function get_fee_id($py){
		$tablename = $this->get_fee_table_name();
		$info =  $this->table($tablename)->where("INPUTNAME='$py'")->field('ID')->find();
		return $info['ID'];
	}
	//���� ��������ĿID��ȡ��Ŀ����
	public function get_project_name($tlfid){
		$tablename = $this->get_table_name($this->project);
		$info =  $this->table($tablename)->where("TLF_PROJECT_ID='$tlfid'")->field('PROJECTNAME')->find();
		return $info['PROJECTNAME'];

	}
	//������������ĿID ��ȡ��ĿID
	public function get_project_id($tlfid){  
		$tablename = $this->get_table_name($this->project);
		$info =  $this->table($tablename)->where("TLF_PROJECT_ID='$tlfid'")->field('ID')->find();
		return $info['ID'];

	}
	//���� ��ĿID ��ȡCase ID
	public function get_case_id($id){
		$tablename = $this->get_table_name($this->casetable);
		$info =  $this->table($tablename)->where("SCALETYPE=1 and PROJECT_ID='$id'")->field('ID')->find();
		return $info['ID'];

	}
	//���ݷ���py��ȡid
	public function get_fee_id_new($py){
		$tablename = $this->get_table_name($this->feetable);
		$info =  $this->table($tablename)->where("INPUTNAME='$py'")->field('ID')->find();
		return $info['ID'];

	}


	 
     
    
    
     
}

 