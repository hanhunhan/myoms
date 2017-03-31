<?php
/**
 * ҵ�����
 * Created by PhpStorm.
 * User: superkemi
 */

class BenefitsAction extends ExtendAction {
    /*
     * ���캯��
     */
    const DISCOUNT_AD_AUTHORITY = 257;
    const WITH_LIMIT_AUTHORITY = 358;

    protected $feeScaleType = null;
    protected $caseId = 0;

    public function __construct() {
        parent::__construct();

        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'benefit_detail' => array(
                'name' => 'benefit-detail',
                'text' => '��������'
            ),
            'apply_info' => array(
                'name' => 'apply-info',
                'text' => '�������'
            ),
            'budget' => array(
                'name' => 'budget',
                'text' => '����Ԥ��'
            ),
            'exec_info' => array(
                'name' => 'exec-info',
                'text' => '��Ŀִ�����'
            ),
            'contract_admin_confirm' => array(
                'name' => 'contract-admin-confirm',
                'text' => '��ͬ����Աȷ��'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->title = 'ҵ�����';
        $this->processTitle = '����ҵ�����������';

        //caseID
        $this->caseId = intval($_REQUEST['CASEID']);

        //recordId
        //�����һ�ε�������չ�����⣨������������
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('Benefits');

        // ����������
        $this->assign('flowId', $this->flowId);
        $this->assign('recordId', $this->recordId);
        $this->assign('CASEID', $this->caseId);
        $this->assign('title', $this->title);
    }

    /**
     * չʾ������Ϣ
     */
    public function process() {
        //process����

        //ת����һ����״̬��
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);

        //Ȩ���ж�
        //��ͬ����ԱȨ��
        $permisition_con_dis = $this->haspermission(self::DISCOUNT_AD_AUTHORITY);
        $permisition_con_dis = intval($permisition_con_dis);

        //������ѡ�Ƿ��ڶ����Ȩ��
        $permisition_in_limit = $this->haspermission(self::WITH_LIMIT_AUTHORITY);
        $permisition_in_limit = intval($permisition_in_limit);

        //ҵ�����Model
        $benefits_model =D("Benefits");
        $benefits_id = $this->recordId;

        //��ȡҵ�������Ϣ
        $search_arr = array("PROJECT_ID","CASE_ID","SCALE_TYPE","DISCOUNT_AD","TYPE","FEE_TYPE","INQUOTA");
        $benefits_info = $benefits_model->get_info_by_id($benefits_id,$search_arr);

        //��ĿID
        $prjid = $benefits_info[0]["PROJECT_ID"];
        //ҵ������
        $scale_type = $benefits_info[0]["SCALE_TYPE"];
        //ҵ��ID
        $case_id = $benefits_info[0]["CASE_ID"];
        //�ۺ���ѣ�Ӳ��ҵ��ʱ�����ͬ�ۿ�
        $discount_ad = $benefits_info[0]["DISCOUNT_AD"] !== null ? $benefits_info[0]["DISCOUNT_AD"] : -1; //�ۺ���
        //��������
        $benefits_type = $benefits_info[0]["TYPE"];
        //��������
        $fee_type = $benefits_info[0]["FEE_TYPE"];
        //�Ƿ��ڶ���ڣ�Ӳ��ҵ��ʱΪ�������Ϻ�ͬҪ��
        $isquota = $benefits_info[0]["INQUOTA"];

        //������Ŀ���ͻ�ȡ��sql���
        $sql = $this->getBenefitSql($benefits_id, $scale_type);
        $arr = M()->query($sql);

        //�û���
        $user = D("Erp_users")->field("NAME")->where("ID=".$arr[0]["AUSER_ID"])->find();
        //��ͬ���
        $contract_no = $arr[0]["CONTRACT"];
        //��Ŀ����
        $project_name = $arr[0]["PROJECTNAME"];
        //��ͬ�ͻ�����
        $company = $arr[0]["COMPANY"];
        //��ͬ�ͻ�����
        $contract_money = floatval($arr[0]["MONEY"]);
        //����������
        $apply_amount = $arr[0]["AMOUNT"];
        $auser = $user["NAME"];

        //�ۼ������� (��������)
        $sql_amount = "SELECT SUM(AMOUNT) SUMMONEY FROM ERP_BENEFITS A WHERE A.PROJECT_ID=$prjid AND (STATUS = 3 OR STATUS = 2) AND A.TYPE <> 2 AND ID <> " . $this->recordId;
        $arr_amount = M()->query($sql_amount);
        $sum_money = $arr_amount[0]["SUMMONEY"] ? $arr_amount[0]["SUMMONEY"] : 0;

        //����Ԥ����ط���
        $case_type = D("ProjectCase")->get_info_by_id($case_id,array("SCALETYPE"));
        $case_type = $case_type[0]["SCALETYPE"];

        // �������浯���˵�
        if (in_array($case_type, array(3, 8))) {  // ����Ƿ��ҷ��ճ���Ŀ�����޸���Ӧ�Ĳ˵�
            unset($this->menu['apply_info']);
            unset($this->menu['budget']);
        } else {
            unset($this->menu['contract_admin_confirm']);
        }

        //��Ŀ���롢�������󡢸��������ʡ��ۺ�������
        //����
        if(in_array($case_type, array(1, 2)))
        {
            $sql = "SELECT SUMPROFIT,OFFLINE_COST_SUM_PROFIT,OFFLINE_COST_SUM_PROFIT_RATE,ONLINE_COST_RATE "
                . "FROM ERP_PRJBUDGET WHERE CASE_ID = $case_id";
            //Ԥ����Ϣ
            $fee_info = M()->query($sql);
            $prj_income_budget = $fee_info[0]["SUMPROFIT"] ? $fee_info[0]["SUMPROFIT"] : 0 ;//��Ŀ����
            $pay_profit_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] : 0;//��������
            $pay_profit_rate_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] : 0;//����������
            $Comprehensive_profit_rate_budget = $fee_info[0]["ONLINE_COST_RATE"] ? $fee_info[0]["ONLINE_COST_RATE"] : 0;//�ۺ�������

            //Ԥ�����·���
            $sql_budget_offline = "SELECT GETBUGVCOST($case_id) BUGVCOST FROM DUAL";
            $prj_budget_vcost = M()->query($sql_budget_offline);
            $prj_budget_vcost = $prj_budget_vcost[0]["BUGVCOST"] ? $prj_budget_vcost[0]["BUGVCOST"] : 0;

            //Ԥ���ۺ����
            //$sql_ad_cost = "SELECT GETVADCOST($case_id,$case_type) AD_BUDGET FROM DUAL";
            $sql_ad_cost = "select nvl(sum(B.AMOUNT),0) vadcost from erp_prjbudget a,erp_budgetfee b where a.case_id = $case_id and a.id=B.BUDGETID and b.feeid =98 and b.isvalid=-1";
            $discount_ad_budget = M()->query($sql_ad_cost);
            $discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["VADCOST"] : 0;

            //Ԥ����������
            $sql_other_fee = "SELECT GETBUGOTHERCOST($case_id) OTHER_FEE FROM DUAL";
            $other_fee_budget = M()->query($sql_other_fee);
            $other_fee_budget = $other_fee_budget[0]["OTHER_FEE"] ? $other_fee_budget[0]["OTHER_FEE"] : 0;
        }
        //�
        else if($case_type == 4)
        {
            $act_id = D("Erp_activities")->where("CASE_ID = ".$case_id)->field("ID")->find();
            $activities_id = $act_id["ID"];
            $sql_fee = "SELECT PRINCOME,BUDGET FROM ERP_ACTIVITIES WHERE ID = ".$activities_id;
            $fee_info = M()->query($sql_fee);

            //��Ŀ����
            $prj_income_budget = $fee_info[0]["PRINCOME"];

            //���·���
            $prj_budget_vcost = $fee_info[0]["BUDGET"];

            //��������
            $pay_profit_budget = $fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"];

            //����������
            $pay_profit_rate_budget = round(($fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"]) * 100/$fee_info[0]["PRINCOME"],2);

            //Ԥ����������
            $other_fee_budget = 0;

            //Ԥ���ۺ����
            //$sql_ad_budget = "SELECT GETVADCOST($activities_id,$case_type) AD_BUDGET FROM DUAL";
            $sql_ad_budget = "select nvl(sum(B.AMOUNT),0) vadcost from erp_actibudgetfee B where B.activities_id = $activities_id  and b.fee_id = 98 and b.isvalid=0";
            $discount_ad_budget = M()->query($sql_ad_budget);
            $discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["VADCOST"] : 0;

            //�ۺ�������
            $Comprehensive_profit_rate_budget = round(($fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"] - $discount_ad_budget) * 100/$fee_info[0]["PRINCOME"],2);
        }
        $project_budget_fee["prj_income"] = $prj_income_budget;
        $project_budget_fee["cost_fee"] = $prj_budget_vcost;
        $project_budget_fee["pay_profit"] = $pay_profit_budget;
        $project_budget_fee["pay_profit_rate"] = $pay_profit_rate_budget;
        $project_budget_fee["discount_ad"] = $discount_ad_budget;
        $project_budget_fee["other_fee"] = $other_fee_budget;
        $project_budget_fee["Comprehensive_profit_rate"] = $Comprehensive_profit_rate_budget;

        //��Ŀִ��״����ط���
        if(in_array($case_type, array(1, 2)))
        {
            //��Ŀ����
            if ($case_type == 1) {
                $prj_income_e = M()->query("SELECT GETPRJDATA($case_id,2,null) PRJ_INCOME_E FROM DUAL");
            }
            else
            {
                $prj_income_e = M()->query("select getCaseInvoiceAndReturned($case_id, 2, 2) PRJ_INCOME_E from dual");
            }
            $prj_income_e = $prj_income_e ? $prj_income_e[0]["PRJ_INCOME_E"] : 0;

            //ʵ�����·���
            $cost_fee_e = M()->query("SELECT GETOFFLINECOST($case_id) COST_FEE_E FROM DUAL");
            $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;

            //��ԱǩԼδ������
            $case_sign_nopay = M()->query("SELECT CASE_SIGN_NOPAY($case_id,2) CASE_SIGN_NOPAY FROM DUAL");
            $case_sign_nopay = $case_sign_nopay ? $case_sign_nopay[0]["CASE_SIGN_NOPAY"] : 0;

            $cost_fee_e += $case_sign_nopay;

            //��������
            $pay_profit_e = floatval($prj_income_e) - floatval($cost_fee_e);

            //����������
            $pay_profit_rate_e = 0;
            if (floatval($prj_income_e) != 0) {
                $pay_profit_rate_e = round($pay_profit_e * 100 / floatval($prj_income_e), 2);
            }
//            $pay_profit_rate_e = M()->query("SELECT GETPRJDATA($case_id,9,null) PAY_PROFIT_RATE_E FROM DUAL");
//            $pay_profit_rate_e = $pay_profit_rate_e ? $pay_profit_rate_e[0]["PAY_PROFIT_RATE_E"] : 0;

            //�ۺ���
            $discount_ad_e = $discount_ad;

            //ִ�У�ʵ�ʣ���������
            $sql_act_other_cost = "SELECT SUM(AMOUNT) OTHER_FEE_E FROM ERP_BENEFITS WHERE CASE_ID = $case_id AND TYPE = 1 AND STATUS=3";

            $other_fee_e = M()->query($sql_act_other_cost);
            $other_fee_e = $other_fee_e[0]["OTHER_FEE_E"] ? $other_fee_e[0]["OTHER_FEE_E"] : 0;

            //ִ�У�ʵ�ʣ��ۺ�������
            $Comprehensive_profit_rate_e = M()->query("SELECT GETPRJDATA($case_id,10,null) COMPREHENSIVE FROM DUAL");
            $Comprehensive_profit_rate_e = $Comprehensive_profit_rate_e[0]["COMPREHENSIVE"] ? $Comprehensive_profit_rate_e[0]["COMPREHENSIVE"] : 0;

        }
        else if($case_type == 4)
        {
            //ʵ����Ŀ����
            $sql = "SELECT SUM(INVOICE_MONEY) SUM_INVOICE_MONEY FROM ERP_BILLING_RECORD WHERE CASE_ID = $case_id AND STATUS = 4";
            $prj_income_e = M()->query($sql);
            $prj_income_e = $prj_income_e[0]["SUM_INVOICE_MONEY"] ? $prj_income_e[0]["SUM_INVOICE_MONEY"] : 0;

            //ʵ�����·���
            $cost_fee_e = M()->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
            $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;

            //��������
            $pay_profit_e = floatval($prj_income_e - $cost_fee_e);

            //����������
            //$pay_profit_rate_e = round(($prj_income_e - $cost_fee_e)/$prj_income_e*100,2);
            //����������
            $pay_profit_rate_e = M()->query("select getprjdata($case_id,9,null) PAY_PROFIT_RATE_E from dual");
            $pay_profit_rate_e = $pay_profit_rate_e ? $pay_profit_rate_e[0]["PAY_PROFIT_RATE_E"] : 0;

            //�ۺ���
            $discount_ad_e = $discount_ad;

            //ִ�У�ʵ�ʣ���������
            $other_fee_e = 0;

            //�ۺ�������
            $Comprehensive_profit_rate_e = round(($prj_income_e - $cost_fee_e - 0 )/$prj_income_e*100,2);
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
        $sql_invoice = "SELECT SUM(INVOICE_MONEY) SUM_INVOICE_MONEY FROM ERP_BILLING_RECORD WHERE CASE_ID = $case_id AND STATUS = 4";
        $invoice_money_ad = M()->query($sql_invoice);
        $invoice_money_ad = $invoice_money_ad[0]["SUM_INVOICE_MONEY"] ? $invoice_money_ad[0]["SUM_INVOICE_MONEY"] : 0;

        //�ؿ���
        $sql_return_money = "SELECT SUM(T.INCOME) SUM_MONEY FROM ERP_INCOME_LIST T WHERE T.CASE_ID = {$case_id} AND T.STATUS = 4 ";
        $refund_money_ad = M()->query($sql_return_money);
        $refund_money_ad= $refund_money_ad[0]["SUM_MONEY"] ? $refund_money_ad[0]["SUM_MONEY"] : 0;

        //ʵ�����·���
        $cost_fee_ad = M()->query("SELECT GETOFFLINECOST($case_id) COST_FEE_E FROM DUAL");
        $cost_fee_ad = $cost_fee_ad ? $cost_fee_ad[0]["COST_FEE_E"] : 0;

        //��������
        $pay_profit_ad = $refund_money_ad - $cost_fee_ad;

        //����������
        $pay_profit_rate_ad = round(($refund_money_ad - $cost_fee_ad)/$refund_money_ad * 100,2);

        //��ͬ�ۿ�
        $contract_discount_ad = $discount_ad;
        $contract_exe_fee["invoice_money_ad"] = $invoice_money_ad;
        $contract_exe_fee["refund_money_ad"] = $refund_money_ad;
        $contract_exe_fee["cost_fee_ad"] = $cost_fee_ad;
        $contract_exe_fee["pay_profit_ad"] = $pay_profit_ad;
        $contract_exe_fee["pay_profit_rate_ad"] = $pay_profit_rate_ad;
        $contract_exe_fee["contract_discount_ad"] = $contract_discount_ad;

        //���������
        $this->assign('case_id',$case_id);
        $this->assign('benefits_type',$benefits_type);
        $this->assign('scale_type',$scale_type);
        $this->assign('permisition_con_dis',$permisition_con_dis);
        $this->assign('permisition_in_limit',$permisition_in_limit);
        $this->assign('activities_id',$activities_id);
        $this->assign('fee_type',$fee_type);
        $this->assign('isquota',$isquota);

        //������Ϣ����
        $this->assign('apply_amount',$apply_amount);
        $this->assign('contract_no',$contract_no);
        $this->assign('company',$company);
        $this->assign('contract_money',$contract_money);
        $this->assign('project_name',$project_name);
        $this->assign('sum_money',$sum_money);
        $this->assign('auser',$auser);

        //��Ŀ����ԱȲ���
        //��ĿԤ���������
        $this->assign("project_budget_fee",$project_budget_fee);
        //��Ŀִ������������
        $this->assign("project_exe_fee",$project_exe_fee);
        //Ӳ��ִ������������
        $this->assign("contract_exe_fee",$contract_exe_fee);

        $this->assign('discount_ad',$discount_ad);

        //��ť
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //case ID ����ID
        $this->assign('caseId', $this->caseId);
        //recordId
        $this->assign('recordId', $this->recordId);
        //�˵�
        $this->assign('menu', $this->menu);
        $this->display('process');
    }

    /**
     * ����ҵ���������
     */
    public function update_benefits_data()
    {
        $response = array(
            'status'=>false,
            'msg'=>'',
            'data'=>null,
            'url'=>'',
        );

        $act = isset($_REQUEST["act"])?trim($_REQUEST["act"]):'';
        $this->caseId = isset($_REQUEST['caseId'])?intval($_REQUEST['caseId']):0;
        $this->recordId = isset($_REQUEST['recordId'])?intval($_REQUEST['recordId']):0;

        $activities_id = isset($_REQUEST["activities_id"])?intval($_REQUEST["activities_id"]):0;
        $discount_ad = $_REQUEST['discount_ad'];
        $inquota = $_REQUEST['inquota'];
        $fee_type = $_REQUEST['fee_type'];

        if(!$act || !$this->caseId || !$this->recordId){
            die(@json_encode($response));
        }

        M()->startTrans();
        $benefits_model = D("Benefits");
        $success = false;
        switch($act){
            case 'discount_ad_budget':
                $data["AMOUNT"] = floatval($_REQUEST["discount_ad_budget"]);
                $data["FEE_ID"] = 98;
                $data["ACTIVITIES_ID"] = intval($_REQUEST["activities_id"]);
                // 0:��δ��� -1:�����
                $data["ISVALID"] = 0;
                $data["MARK"] = "�ۺ����";

                //���ݻid�鿴�Ƿ��Ѿ��м�¼
                $fee_info = D("Erp_actibudgetfee")
                    ->where("ACTIVITIES_ID = {$activities_id} AND FEE_ID = 98 AND ISVALID = 0")->field("ID")->find();

                if($fee_info["ID"]) {
                    $up_num = D("Erp_actibudgetfee")->where("ID = " . $fee_info["ID"])->save(array("AMOUNT" => $data["AMOUNT"]));
                    if ($up_num)
                        $success = true;
                }
                else {
                    $insert_id = D("Erp_actibudgetfee")->add($data);
                    if ($insert_id)
                        $success = true;
                }
                break;
            case 'discount_ad':
                $updata_arr['DISCOUNT_AD'] = $discount_ad;
                $res = $benefits_model->update_info_by_id($this->recordId,$updata_arr);
                if($res)
                    $success = true;
                break;
            case 'is_ok':
                $updata_arr['INQUOTA'] = $inquota;
                $res = $benefits_model->update_info_by_id($this->recordId,$updata_arr);
                if($res)
                    $success = true;
                break;
            case 'fee_type':
                $updata_arr['FEE_TYPE'] = $fee_type;
                $res = $benefits_model->update_info_by_id($this->recordId,$updata_arr);
                if($res)
                    $success = true;
                break;
        }

        if($success){
            M()->commit();
            $response['status'] = true;
            $response["msg"] = g2u("�ף����ݱ���ɹ���");
        }
        else{
            M()->rollback();
            $response['status'] = false;
            $response["msg"] = g2u("�ף����ݱ���ʧ��,����ϵ����Ա��");
        }

        die(@json_encode($response));
    }


    /**
     * @param $benefitId  ҵ�����ID
     * @param $scaleType  ҵ������
     * @return string
     */
    private function getBenefitSql($benefitId, $scaleType) {
        if (empty($benefitId) || empty($scaleType)) {
            return '';
        }

        //����
        if ($scaleType == 1) {
            $sql = <<< ET
                    SELECT a.CONTRACT,
                           a.PROJECTNAME,
                           a.COMPANY,
                           b.AMOUNT,
                           b.AUSER_ID
                    FROM erp_benefits b
                    LEFT JOIN erp_project a ON b.project_id =a.id
                    where b.id={$benefitId}
ET;

        } else  if($scaleType == 4){
            $sql = <<< ET
            SELECT c.CONTRACT_NO as CONTRACT,
                   a.PROJECTNAME,
                   c.COMPANY,
                   b.AMOUNT,
                   b.AUSER_ID,
                   c.MONEY
            FROM erp_benefits b
            LEFT JOIN erp_project a ON b.project_id =a.id
            LEFT JOIN erp_income_contract c ON c.id = b.contract_no
            WHERE b.id={$benefitId}
ET;
        }else {
            $sql = <<< ET
            SELECT a.CONTRACT,
                   a.PROJECTNAME,
                   c.COMPANY,
                   b.AMOUNT,
                   b.AUSER_ID,
                   c.MONEY
            FROM erp_benefits b
            LEFT JOIN erp_project a ON b.project_id =a.id
            LEFT JOIN erp_income_contract c ON c.contract_no = a.contract
            WHERE b.id={$benefitId}
ET;
        }

        return $sql;
    }

    /**
     * ����������
     */
    public function opinionFlow() {
        $_REQUEST = u2g($_REQUEST);

        $response = array(
            'status'=>false,
            'message'=>'',
            'data'=>null,
            'url'=>U('Flow/flowList','status=1'),
        );

        //Ȩ���ж�
        if($this->flowId) {
            if (!$this->myTurn) {
                $response['message'] = g2u('�Բ��𣬸ù�������û��Ȩ�޴���');
                die(@json_encode($response));
            }
        }

        //������֤
        $error_str = '';
        if($_REQUEST['flowNext'] && !$_REQUEST['DEAL_USERID']){
            $error_str .= "�ף���ѡ����һ��ת���ˣ�\n";
        }

        if(!trim($_REQUEST['DEAL_INFO'])){
            $error_str .= "�ף�����д���������\n";
        }

        if($error_str){
            $response['message'] = g2u($error_str);
            die(@json_encode($response));
        }

        //����ID
        $_REQUEST['caseId'] = $this->caseId;

        $result = $this->workFlow->doit($_REQUEST);

        if (is_array($result)) {
            $response = $result;
        } else {
            if($result)
                $response['status'] = 1;
            else
                $response['status'] = 0;
        }

        echo json_encode($response);
    }

}