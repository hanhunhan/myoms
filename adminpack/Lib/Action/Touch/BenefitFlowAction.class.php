<?php

/**
 * ������Ԥ�����������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/8
 * Time: 10:25
 */
class BenefitFlowAction extends ExtendAction {
    /**
     * �ɹ������ѯ���
     */
    const PURCHASE_REQUIRE_SQL = <<<SQL
        SELECT A.*,B.PROJECTNAME
        FROM ERP_NONCASHCOST  A
        LEFT JOIN ERP_PROJECT B 
		ON A.PROJECT_ID = B.ID
        WHERE A.ID = %d
SQL;

    /**
     * �Ƿ������д�ۺ����Ȩ��ID
     */
    const DISCOUNT_AD_AUTHORITY = 257;

    /**
     * �Ƿ������д�ڶ�ȷ�Χ�ڵ�Ȩ��
     */
    const WITH_LIMIT_AUTHORITY = 358;
    

    /**
     * �ɹ�����״̬����
     * @var array
     */
    protected $requirementDesc = array(
        0 => 'δ�ύ',
        1 => '�����',
        2 => '���ͨ��',
        3 => '���δͨ��',
        4 => '�ɹ����'
    );
	protected $message = array(
		0=> '����ʧ��',
		'1'=>'�����ɹ�',
		'-1'=>'����Ŀ�ɱ��ѳ������ʶ�Ȼ򳬳�����Ԥ�㣨�ܷ���>��Ʊ�ؿ�����*���ֳɱ��ʣ������̲�������ͨ����',
		'-2'=>'δ�����ؾ���ɫ',
	);

    public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'purchase-benefit' => array(
                'name' => 'purchase-benefit',
                'text' => ' Ԥ�����������'
            ),
           'purchase-budget' => array(
                'name' => 'purchase-budget',
                'text' => '  ����Ԥ��'
            )
			,
			'purchase-exec' => array(
                'name' => 'purchase-exec',
                'text' => '  ��Ŀִ�����'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->init();
    }

    /**
     * ��ʼ��������
     */
    private function init() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('BenefitFlow');  // ��Ŀ�²ɹ�����
        $this->assign('flowType', 'BenefitFlow');
        $this->assign('flowTypeText', 'Ԥ����������������');
    }

    /**
     * ��������
     */
    public function process() {
		if($this->flowId){
			 $this->workFlow->nextstep($this->flowId);  // ���޸�Ŀǰ��״̬
		}else{
			$this->recordId = $_REQUEST['RECORDID'];
			$this->assign('recordId', $this->recordId);
			$this->assign('CASEID', $_REQUEST['CASEID']);
		}
		$case = M('Erp_case')->where("ID=".$_REQUEST['CASEID'])->find();
        $this->getPurchaseInfo($this->recordId,$case['SCALETYPE']);
         
 
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', 'Ԥ����������������');
        $this->assign('menu', $this->menu);  // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
		 
        $this->display('index');
    }

    /**
     * ��ȡ�ɹ�����
     * @param $requireId
     * @param string $caseType
     * @return array
     */
    protected function getPurchaseInfo($requireId, $caseType = '') {
        
		$flowId = $this->flowId;//1609
		//Ȩ���ж�
		$permisition_con_dis = $this->haspermission(self::DISCOUNT_AD_AUTHORITY);//��ͬ����ԱȨ��
		$permisition_con_dis = intval($permisition_con_dis);
		//var_dump($permisition_con_dis);

		$permisition_in_limit = $this->haspermission(self::WITH_LIMIT_AUTHORITY);//������ѡ�Ƿ��ڶ����Ȩ��
		$permisition_in_limit = intval($permisition_in_limit);
				   
		//ҵ�����Model
		$benefits_model =D("Benefits");
		
		$benefits_id =  $this->recordId;
		 
			 
		$scale_type = $caseType;//ҵ������
		
		
		$benefits_info = $benefits_model->get_info_by_id($benefits_id,array("PROJECT_ID","DISCOUNT_AD","TYPE","FEE_TYPE","INQUOTA"));
		$discount_ad = $benefits_info[0]["DISCOUNT_AD"] !== null ? $benefits_info[0]["DISCOUNT_AD"] : -1; //�ۺ���
		$benefits_type = $benefits_info[0]["TYPE"];
		$fee_type = $benefits_info[0]["FEE_TYPE"];
		$isquota = $benefits_info[0]["INQUOTA"];
		$prjid = $benefits_info[0]["PROJECT_ID"];
        $project = M('Erp_project')->where("ID=$prjid")->find();
        // comment by xuke: ����Ҫ�����ж���
//		$canCommitBenefit = D('ProjectCase')->canCommitBenefit($prjid, $scale_type);
//		if (!$canCommitBenefit) {
//			js_alert("����Ŀ������ִ���л����ڽ���״̬����������������");
//			exit;
//		}
        $prjModel = D('Project');
		$case_model = D("ProjectCase"); 
		$conf_where = "PROJECT_ID=$prjid and SCALETYPE=$scale_type";
		$field_arr = array("ID");
		$case_id =$case_model->get_info_by_cond($conf_where,$field_arr);
		$case_id = $case_id[0]["ID"];
		 
		$this->_merge_url_param['benefits_type'] = $benefits_type;
		!empty($_GET['operate']) ? $this->_merge_url_param['operate'] = $_GET['operate'] : 0;
		$sql = $this->getBenefitSql($benefits_id, $scale_type);  // ������Ŀ���ͻ�ȡ��sql���
		$arr = M()->query($sql);
		$user = D("Erp_users")->field("NAME")->where("ID=".$arr[0]["AUSER_ID"])->find();
		$contract_no = $arr[0]["CONTRACT"];//��ͬ���
		$project_name = $arr[0]["PROJECTNAME"];//��Ŀ����
		$company = $arr[0]["COMPANY"];//��ͬ�ͻ�����
		$contract_money = isset($arr[0]["MONEY"]) ?  $arr[0]["MONEY"]  : '';//��ͬ�ͻ�����
		$apply_amount = $arr[0]["AMOUNT"];//����������
		$auser = $user["NAME"];
		$supplier = $arr[0]['NAME'];


		
		//$sql1 = "select sum(AMOUNT) SUMMONEY from erp_benefits a where a.PROJECT_ID=$prjid and status = 3";
		$sql1 = "select sum(AMOUNT) SUMMONEY from erp_benefits a where a.TYPE <> 2 AND a.STATUS in(2,3)   and a.PROJECT_ID=$prjid and ID<>".$_REQUEST['RECORDID'];
		$arr1 = M()->query($sql1);
		$sum_money = $arr1[0]["SUMMONEY"] ? $arr1[0]["SUMMONEY"] : 0;//�ۼ������� (��������)
		
		//����Ԥ����ط���
		$case_type = D("ProjectCase")->get_info_by_id($case_id,array("SCALETYPE"));
		$case_type = $case_type[0]["SCALETYPE"];   
		//$btype = D('Businessclass')->where("ID=$case_type")->find(); 
		$btypelist = M()->query("select * from ERP_BUSINESSCLASS where id =$case_type");  
		if($case_type==1){
			$btypelist[0]['YEWU'] = '������';
		}
		//��Ŀ���롢�������󡢸��������ʡ��ۺ�������
	 
		if(in_array($case_type, array(1, 2)))
		{
			$sql = "select SUMPROFIT,OFFLINE_COST_SUM_PROFIT,OFFLINE_COST_SUM_PROFIT_RATE,ONLINE_COST_RATE "
			. "from ERP_PRJBUDGET where CASE_ID = $case_id";
			//echo $sql;
			$fee_info = M()->query($sql);
			$prj_income_budget = $fee_info[0]["SUMPROFIT"] ? $fee_info[0]["SUMPROFIT"] : 0 ;//��Ŀ����
			$pay_profit_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] : 0;//��������
			$pay_profit_rate_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] : 0;//����������
			$Comprehensive_profit_rate_budget = $fee_info[0]["ONLINE_COST_RATE"] ? $fee_info[0]["ONLINE_COST_RATE"] : 0;//�ۺ�������
			
			//Ԥ�����·���
			$sql1 = "select GETBUGVCOST($case_id) BUGVCOST from dual";
			$prj_budget_vcost = M()->query($sql1);
			$prj_budget_vcost = $prj_budget_vcost[0]["BUGVCOST"] ? $prj_budget_vcost[0]["BUGVCOST"] : 0;            
			
			//Ԥ���ۺ����
			$sql2 = "select GETVADCOST($case_id,$case_type) AD_BUDGET from dual";
			$discount_ad_budget = M()->query($sql2);
			$discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["AD_BUDGET"] : 0;
			
			//Ԥ����������
			$sql3 = "select GETBUGOTHERCOST($case_id) OTHER_FEE from dual";
			$other_fee_budget = M()->query($sql3);
			//echo $case_id;
			$other_fee_budget = $other_fee_budget[0]["OTHER_FEE"] ? $other_fee_budget[0]["OTHER_FEE"] : 0;
		}
		         
		$project_budget_fee["prj_income"] = $prj_income_budget;
		$project_budget_fee["cost_fee"] = $prj_budget_vcost;
		$project_budget_fee["pay_profit"] = $pay_profit_budget;
		$project_budget_fee["pay_profit_rate"] = $pay_profit_rate_budget;
		$project_budget_fee["discount_ad"] = $discount_ad_budget;
		$project_budget_fee["other_fee"] = $other_fee_budget;
		$project_budget_fee["Comprehensive_profit_rate"] = $Comprehensive_profit_rate_budget;

		//echo $case_id;
		
		//��Ŀִ��״����ط���     
		if(in_array($case_type, array(1, 2)))
		{
			//��Ŀ����
                if ($case_type == 1) {
                    // �����ǿ�Ʊ����
                    $prj_income_e =  M()->query("select getprjdata($case_id,2,null) PRJ_INCOME_E from dual");
                } else {
                    // �����ǻؿ�����
                    $prj_income_e =  M()->query("select getCaseInvoiceAndReturned($case_id, 2, 2) PRJ_INCOME_E from dual");
                }
                $prj_income_e = $prj_income_e ? $prj_income_e[0]["PRJ_INCOME_E"] : 0;

                //ʵ�����·���
                $cost_fee_e = M()->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
                $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;
                $cost_fee_e = $cost_fee_e + $prjModel->caseSignNoPay($case_id,2);
                
                //��������
                $pay_profit_e = floatval($prj_income_e) - floatval($cost_fee_e);

                //����������
                $pay_profit_rate_e = 0;
                if (floatval($prj_income_e) != 0) {
                    $pay_profit_rate_e = round($pay_profit_e * 100 / floatval($prj_income_e), 2);
                }

                //�ۺ���
                $discount_ad_e = $discount_ad;
                
                //ִ�У�ʵ�ʣ���������
                $sql4 = "select SUM(AMOUNT) OTHER_FEE_E from erp_benefits where CASE_ID = $case_id and TYPE = 1 AND STATUS=3";
                $other_fee_e = M()->query($sql4);
                $other_fee_e = $other_fee_e[0]["OTHER_FEE_E"] ? $other_fee_e[0]["OTHER_FEE_E"] : 0;
                
               //ִ�У�ʵ�ʣ��ۺ�������
                $Comprehensive_profit_rate_e = ($pay_profit_e - floatval($discount_ad_e)) / floatval($prj_income_e);

			
		}
		 
		$project_exe_fee["prj_income_e"] = $prj_income_e;
		$project_exe_fee["cost_fee_e"] = $cost_fee_e;
		$project_exe_fee["pay_profit_e"] = $pay_profit_e;
		$project_exe_fee["pay_profit_rate_e"] = $pay_profit_rate_e;
		$project_exe_fee["discount_ad_e"] = $discount_ad_e;
		$project_exe_fee["other_fee_e"] = $other_fee_e;
		$project_exe_fee["Comprehensive_profit_rate_e"] = $Comprehensive_profit_rate_e; 
		
				   
		//Ӳ����Ŀʵ��ִ����ط���            
		//��Ʊ���
		$sql5 = "select sum(INVOICE_MONEY) SUM_INVOICE_MONEY from erp_billing_record where CASE_ID = $case_id and status = 4";
		//echo $sql5;
		$invoice_money_ad = M()->query($sql5);
		$invoice_money_ad = $invoice_money_ad[0]["SUM_INVOICE_MONEY"] ? $invoice_money_ad[0]["SUM_INVOICE_MONEY"] : 0;
		
		//�ؿ���
//            $sql6 = "select sum(MONEY) SUM_MONEY from erp_payment_records where CASE_ID = $case_id";
		$sql6 = "SELECT SUM(t.INCOME) SUM_MONEY FROM erp_income_list t WHERE t.case_id = {$case_id} AND t.status = 4 ";
		$refund_money_ad = M()->query($sql6);
		$refund_money_ad= $refund_money_ad[0]["SUM_MONEY"] ? $refund_money_ad[0]["SUM_MONEY"] : 0;
	   
		//ʵ�����·���
		$cost_fee_ad = M()->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
		$cost_fee_ad = $cost_fee_ad ? $cost_fee_ad[0]["COST_FEE_E"] : 0;

		//��������
		//$pay_profit_ad = M()->query("select getprjdata($case_id,8,null) PAY_PROFIT_E from dual");           
		$pay_profit_ad = $refund_money_ad - $cost_fee_ad;
		
		//����������
		//$pay_profit_rate_ad = M()->query("select getprjdata($case_id,9,null) PAY_PROFIT_RATE_E from dual");
		$pay_profit_rate_ad = round(($refund_money_ad - $cost_fee_ad)/$refund_money_ad * 100,2);
		
		$contract_discount_ad = $discount_ad;//��ͬ�ۿ�
		$contract_exe_fee["invoice_money_ad"] = $invoice_money_ad;
		$contract_exe_fee["refund_money_ad"] = $refund_money_ad;
		$contract_exe_fee["cost_fee_ad"] = $cost_fee_ad;
		$contract_exe_fee["pay_profit_ad"] = $pay_profit_ad;
		$contract_exe_fee["pay_profit_rate_ad"] = $pay_profit_rate_ad;
		$contract_exe_fee["contract_discount_ad"] = $contract_discount_ad;

		//���������
		$this->assign('paramUrl', $this->_merge_url_param);
		$this->assign('case_id',$case_id);
		$this->assign('benefits_type',$benefits_type);
		$this->assign('scale_type',$scale_type);
		$this->assign('permisition_con_dis',$permisition_con_dis);
		$this->assign('permisition_in_limit',$permisition_in_limit);
		$this->assign('flowid',$flowId);
		$this->assign('activities_id',$activities_id); 
		$this->assign('fee_type',$fee_type);
		$this->assign('isquota',$isquota);
		 
		//������Ϣ����
		$this->assign('apply_amount',$apply_amount); 
		$this->assign('btype',$btypelist[0]);
		$this->assign('contract_no',$contract_no);
		$this->assign('company',$company);
		$this->assign('contract_money',$contract_money);
		$this->assign('project_name',$project_name);
		$this->assign('sum_money',$sum_money);
		$this->assign('auser',$auser);
		$this->assign('supplier',$supplier);
		
		//��Ŀ����ԱȲ���
		$this->assign("project_budget_fee",$project_budget_fee);//��ĿԤ���������
		$this->assign("project_exe_fee",$project_exe_fee);//��Ŀִ������������
		$this->assign("contract_exe_fee",$contract_exe_fee);//Ӳ��ִ������������

		$this->assign("project",$project);
		
		$this->assign('discount_ad',$discount_ad); 
		$this->assign('paramUrl',"&flowId=".$_REQUEST['flowId']."&RECORDID=".$_REQUEST['RECORDID']."&CASEID=".$_REQUEST['CASEID']."&flowTypePinYin=".$_REQUEST['flowTypePinYin']."");    
	 
        
       
    }

     

     

    /**
     * ����������
     */
    public function opinionFlow() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        $_REQUEST = u2g($_REQUEST);
        
        $result = $this->workFlow->doit($_REQUEST);
        if (is_array($result)) {
            $response = $result;
        } else {
			$response['result']	= $result;
            $response['status'] = $result >0? $result : 0;
			$response['message'] = g2u($this->message[$result]);
        }

        echo json_encode($response);
    }


	 /**
     * ��ȡbenefit��sql���
     * @param $benefitId
     * @param $scaleType
     * @return string
     */
    private function getBenefitSql($benefitId, $scaleType) {
        if (empty($benefitId) || empty($scaleType)) {
            return '';
        }

        // ����ǵ�����Ŀ
        if ($scaleType == 1) {
            $sql = <<< ET
                    SELECT a.CONTRACT,
                           a.PROJECTNAME,
                           a.COMPANY,
                           b.AMOUNT,
                           b.AUSER_ID,
                          s.NAME
                    FROM erp_benefits b
                    LEFT JOIN erp_project a ON b.project_id =a.id
                    LEFT JOIN erp_supplier s on s.id = b.supplier
                    where b.id={$benefitId}
ET;
        } else {
            $sql = <<< ET
            SELECT a.CONTRACT,
                   a.PROJECTNAME,
                   c.COMPANY,
                   b.AMOUNT,
                   b.AUSER_ID,
                   c.MONEY,
                   s.NAME
            FROM erp_benefits b
            LEFT JOIN erp_project a ON b.project_id =a.id
            LEFT JOIN erp_income_contract c ON c.contract_no = a.contract
            LEFT JOIN erp_supplier s on s.id = b.supplier
            WHERE b.id={$benefitId}
ET;
        }
        return $sql;
    }

	public function update_benefits_data()
        {
            if($_REQUEST["discount_ad_budget"])
            {
                $data["AMOUNT"] = floatval($_REQUEST["discount_ad_budget"]);
                $data["FEE_ID"] = 98;
                $data["ACTIVITIES_ID"] = intval($_REQUEST["activities_id"]);
                $data["ISVALID"] = 0;  // 0=��δ��� -1=�����
                $data["MARK"] = "�ۺ����";

                $caseID = D('ProjectCase')->where("PROJECT_ID = {$_REQUEST['prjid']} AND SCALETYPE = {$_REQUEST['scale_type']}")->getField('ID');
                if ($caseID !== false) {
                    $data['CASE_ID'] = $caseID;
                }
                
                //���ݻid�鿴�Ƿ��Ѿ��м�¼
                $fee_info = D("Erp_actibudgetfee")
                    ->where("ACTIVITIES_ID = ".$_REQUEST["activities_id"]." AND FEE_ID = 98 AND ISVALID = 0")->field("ID")->find();

                if($fee_info["ID"])
                {
                    $up_num = D("Erp_actibudgetfee")->where("ID = ".$fee_info["ID"])->save(array("AMOUNT"=>$data["AMOUNT"]));
                    //var_dump($up_num);
                }
                else
                {
                    $insert_id = D("Erp_actibudgetfee")->add($data);
                }

                if($insert_id > 0 || $up_num > 0)
                {
                    $result['state'] = 1;
                    $result["msg"] = "���ݱ���ɹ���";
                }
                else
                {
                    $result['state'] = 0;
                    $result["msg"] = "���ݱ���ʧ�ܣ�";
                }
                $result['msg'] = g2u($result['msg']);
                echo json_encode($result);
                exit;
            }
            
            if(array_key_exists('discount_ad', $_REQUEST) && $_REQUEST['discount_ad'] !== '') $updata_arr["DISCOUNT_AD"] = $_REQUEST["discount_ad"];
            if($_REQUEST["inquota"]) $updata_arr["INQUOTA"] = intval($_REQUEST["inquota"]);
            $feeType = trim($_REQUEST["fee_type"]);
            if(!empty($feeType)) $updata_arr["FEE_TYPE"] = u2g($_REQUEST["fee_type"]);
            $benefits_id = $_REQUEST["RECORDID"];
            $benefits_model = D("Benefits");
            if (empty($updata_arr)) return;
            $res = $benefits_model->update_info_by_id($benefits_id,$updata_arr);
            //echo $this->model->_sql();die;
            if($res)
            {  
                $result['state'] = 1;
                $result["msg"] = "���ݱ���ɹ���";
            }
            else
            {
                $result['state'] = 0;
                $result["msg"] = "���ݱ���ʧ�ܣ�";
            }
            $result['msg'] = g2u($result['msg']);
            echo json_encode($result);
            exit;
        }
        


    
}