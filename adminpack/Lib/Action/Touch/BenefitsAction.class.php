<?php
/**
 * 业务津贴
 * Created by PhpStorm.
 * User: superkemi
 */

class BenefitsAction extends ExtendAction {
    /*
     * 构造函数
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
                'text' => '申请说明'
            ),
            'benefit_detail' => array(
                'name' => 'benefit-detail',
                'text' => '津贴申请'
            ),
            'apply_info' => array(
                'name' => 'apply-info',
                'text' => '申请情况'
            ),
            'budget' => array(
                'name' => 'budget',
                'text' => '立项预算'
            ),
            'exec_info' => array(
                'name' => 'exec-info',
                'text' => '项目执行情况'
            ),
            'contract_admin_confirm' => array(
                'name' => 'contract-admin-confirm',
                'text' => '合同管理员确认'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->title = '业务津贴';
        $this->processTitle = '关于业务津贴的申请';

        //caseID
        $this->caseId = intval($_REQUEST['CASEID']);

        //recordId
        //解决第一次调用数据展现问题（创建工作流）
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('Benefits');

        // 工作流类型
        $this->assign('flowId', $this->flowId);
        $this->assign('recordId', $this->recordId);
        $this->assign('CASEID', $this->caseId);
        $this->assign('title', $this->title);
    }

    /**
     * 展示流程信息
     */
    public function process() {
        //process数据

        //转交下一步（状态）
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);

        //权限判断
        //合同管理员权限
        $permisition_con_dis = $this->haspermission(self::DISCOUNT_AD_AUTHORITY);
        $permisition_con_dis = intval($permisition_con_dis);

        //财务填选是否在额度内权限
        $permisition_in_limit = $this->haspermission(self::WITH_LIMIT_AUTHORITY);
        $permisition_in_limit = intval($permisition_in_limit);

        //业务津贴Model
        $benefits_model =D("Benefits");
        $benefits_id = $this->recordId;

        //获取业务津贴信息
        $search_arr = array("PROJECT_ID","CASE_ID","SCALE_TYPE","DISCOUNT_AD","TYPE","FEE_TYPE","INQUOTA");
        $benefits_info = $benefits_model->get_info_by_id($benefits_id,$search_arr);

        //项目ID
        $prjid = $benefits_info[0]["PROJECT_ID"];
        //业务类型
        $scale_type = $benefits_info[0]["SCALE_TYPE"];
        //业务ID
        $case_id = $benefits_info[0]["CASE_ID"];
        //折后广告费，硬广业务时代表合同折扣
        $discount_ad = $benefits_info[0]["DISCOUNT_AD"] !== null ? $benefits_info[0]["DISCOUNT_AD"] : -1; //折后广告
        //津贴类型
        $benefits_type = $benefits_info[0]["TYPE"];
        //费用类型
        $fee_type = $benefits_info[0]["FEE_TYPE"];
        //是否在额度内，硬广业务时为代表否符合合同要求
        $isquota = $benefits_info[0]["INQUOTA"];

        //根据项目类型获取的sql语句
        $sql = $this->getBenefitSql($benefits_id, $scale_type);
        $arr = M()->query($sql);

        //用户名
        $user = D("Erp_users")->field("NAME")->where("ID=".$arr[0]["AUSER_ID"])->find();
        //合同编号
        $contract_no = $arr[0]["CONTRACT"];
        //项目名称
        $project_name = $arr[0]["PROJECTNAME"];
        //合同客户名称
        $company = $arr[0]["COMPANY"];
        //合同客户名称
        $contract_money = floatval($arr[0]["MONEY"]);
        //本次申请金额
        $apply_amount = $arr[0]["AMOUNT"];
        $auser = $user["NAME"];

        //累计申请金额 (不含本次)
        $sql_amount = "SELECT SUM(AMOUNT) SUMMONEY FROM ERP_BENEFITS A WHERE A.PROJECT_ID=$prjid AND (STATUS = 3 OR STATUS = 2) AND A.TYPE <> 2 AND ID <> " . $this->recordId;
        $arr_amount = M()->query($sql_amount);
        $sum_money = $arr_amount[0]["SUMMONEY"] ? $arr_amount[0]["SUMMONEY"] : 0;

        //立项预算相关费用
        $case_type = D("ProjectCase")->get_info_by_id($case_id,array("SCALETYPE"));
        $case_type = $case_type[0]["SCALETYPE"];

        // 控制右面弹出菜单
        if (in_array($case_type, array(3, 8))) {  // 如果是非我方收筹项目，则修改相应的菜单
            unset($this->menu['apply_info']);
            unset($this->menu['budget']);
        } else {
            unset($this->menu['contract_admin_confirm']);
        }

        //项目收入、付现利润、付现利润率、综合利润率
        //电商
        if(in_array($case_type, array(1, 2)))
        {
            $sql = "SELECT SUMPROFIT,OFFLINE_COST_SUM_PROFIT,OFFLINE_COST_SUM_PROFIT_RATE,ONLINE_COST_RATE "
                . "FROM ERP_PRJBUDGET WHERE CASE_ID = $case_id";
            //预算信息
            $fee_info = M()->query($sql);
            $prj_income_budget = $fee_info[0]["SUMPROFIT"] ? $fee_info[0]["SUMPROFIT"] : 0 ;//项目收入
            $pay_profit_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT"] : 0;//付现利润
            $pay_profit_rate_budget = $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] ? $fee_info[0]["OFFLINE_COST_SUM_PROFIT_RATE"] : 0;//付现利润率
            $Comprehensive_profit_rate_budget = $fee_info[0]["ONLINE_COST_RATE"] ? $fee_info[0]["ONLINE_COST_RATE"] : 0;//综合利润率

            //预估线下费用
            $sql_budget_offline = "SELECT GETBUGVCOST($case_id) BUGVCOST FROM DUAL";
            $prj_budget_vcost = M()->query($sql_budget_offline);
            $prj_budget_vcost = $prj_budget_vcost[0]["BUGVCOST"] ? $prj_budget_vcost[0]["BUGVCOST"] : 0;

            //预估折后广告费
            //$sql_ad_cost = "SELECT GETVADCOST($case_id,$case_type) AD_BUDGET FROM DUAL";
            $sql_ad_cost = "select nvl(sum(B.AMOUNT),0) vadcost from erp_prjbudget a,erp_budgetfee b where a.case_id = $case_id and a.id=B.BUDGETID and b.feeid =98 and b.isvalid=-1";
            $discount_ad_budget = M()->query($sql_ad_cost);
            $discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["VADCOST"] : 0;

            //预估其他费用
            $sql_other_fee = "SELECT GETBUGOTHERCOST($case_id) OTHER_FEE FROM DUAL";
            $other_fee_budget = M()->query($sql_other_fee);
            $other_fee_budget = $other_fee_budget[0]["OTHER_FEE"] ? $other_fee_budget[0]["OTHER_FEE"] : 0;
        }
        //活动
        else if($case_type == 4)
        {
            $act_id = D("Erp_activities")->where("CASE_ID = ".$case_id)->field("ID")->find();
            $activities_id = $act_id["ID"];
            $sql_fee = "SELECT PRINCOME,BUDGET FROM ERP_ACTIVITIES WHERE ID = ".$activities_id;
            $fee_info = M()->query($sql_fee);

            //项目收入
            $prj_income_budget = $fee_info[0]["PRINCOME"];

            //线下费用
            $prj_budget_vcost = $fee_info[0]["BUDGET"];

            //付现利润
            $pay_profit_budget = $fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"];

            //付现利润率
            $pay_profit_rate_budget = round(($fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"]) * 100/$fee_info[0]["PRINCOME"],2);

            //预估其他费用
            $other_fee_budget = 0;

            //预估折后广告费
            //$sql_ad_budget = "SELECT GETVADCOST($activities_id,$case_type) AD_BUDGET FROM DUAL";
            $sql_ad_budget = "select nvl(sum(B.AMOUNT),0) vadcost from erp_actibudgetfee B where B.activities_id = $activities_id  and b.fee_id = 98 and b.isvalid=0";
            $discount_ad_budget = M()->query($sql_ad_budget);
            $discount_ad_budget = $discount_ad_budget ? $discount_ad_budget[0]["VADCOST"] : 0;

            //综合利润率
            $Comprehensive_profit_rate_budget = round(($fee_info[0]["PRINCOME"] - $fee_info[0]["BUDGET"] - $discount_ad_budget) * 100/$fee_info[0]["PRINCOME"],2);
        }
        $project_budget_fee["prj_income"] = $prj_income_budget;
        $project_budget_fee["cost_fee"] = $prj_budget_vcost;
        $project_budget_fee["pay_profit"] = $pay_profit_budget;
        $project_budget_fee["pay_profit_rate"] = $pay_profit_rate_budget;
        $project_budget_fee["discount_ad"] = $discount_ad_budget;
        $project_budget_fee["other_fee"] = $other_fee_budget;
        $project_budget_fee["Comprehensive_profit_rate"] = $Comprehensive_profit_rate_budget;

        //项目执行状况相关费用
        if(in_array($case_type, array(1, 2)))
        {
            //项目收入
            if ($case_type == 1) {
                $prj_income_e = M()->query("SELECT GETPRJDATA($case_id,2,null) PRJ_INCOME_E FROM DUAL");
            }
            else
            {
                $prj_income_e = M()->query("select getCaseInvoiceAndReturned($case_id, 2, 2) PRJ_INCOME_E from dual");
            }
            $prj_income_e = $prj_income_e ? $prj_income_e[0]["PRJ_INCOME_E"] : 0;

            //实际线下费用
            $cost_fee_e = M()->query("SELECT GETOFFLINECOST($case_id) COST_FEE_E FROM DUAL");
            $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;

            //会员签约未付费用
            $case_sign_nopay = M()->query("SELECT CASE_SIGN_NOPAY($case_id,2) CASE_SIGN_NOPAY FROM DUAL");
            $case_sign_nopay = $case_sign_nopay ? $case_sign_nopay[0]["CASE_SIGN_NOPAY"] : 0;

            $cost_fee_e += $case_sign_nopay;

            //付现利润
            $pay_profit_e = floatval($prj_income_e) - floatval($cost_fee_e);

            //付现利润率
            $pay_profit_rate_e = 0;
            if (floatval($prj_income_e) != 0) {
                $pay_profit_rate_e = round($pay_profit_e * 100 / floatval($prj_income_e), 2);
            }
//            $pay_profit_rate_e = M()->query("SELECT GETPRJDATA($case_id,9,null) PAY_PROFIT_RATE_E FROM DUAL");
//            $pay_profit_rate_e = $pay_profit_rate_e ? $pay_profit_rate_e[0]["PAY_PROFIT_RATE_E"] : 0;

            //折后广告
            $discount_ad_e = $discount_ad;

            //执行（实际）其他费用
            $sql_act_other_cost = "SELECT SUM(AMOUNT) OTHER_FEE_E FROM ERP_BENEFITS WHERE CASE_ID = $case_id AND TYPE = 1 AND STATUS=3";

            $other_fee_e = M()->query($sql_act_other_cost);
            $other_fee_e = $other_fee_e[0]["OTHER_FEE_E"] ? $other_fee_e[0]["OTHER_FEE_E"] : 0;

            //执行（实际）综合利润率
            $Comprehensive_profit_rate_e = M()->query("SELECT GETPRJDATA($case_id,10,null) COMPREHENSIVE FROM DUAL");
            $Comprehensive_profit_rate_e = $Comprehensive_profit_rate_e[0]["COMPREHENSIVE"] ? $Comprehensive_profit_rate_e[0]["COMPREHENSIVE"] : 0;

        }
        else if($case_type == 4)
        {
            //实际项目收入
            $sql = "SELECT SUM(INVOICE_MONEY) SUM_INVOICE_MONEY FROM ERP_BILLING_RECORD WHERE CASE_ID = $case_id AND STATUS = 4";
            $prj_income_e = M()->query($sql);
            $prj_income_e = $prj_income_e[0]["SUM_INVOICE_MONEY"] ? $prj_income_e[0]["SUM_INVOICE_MONEY"] : 0;

            //实际线下费用
            $cost_fee_e = M()->query("select GETOFFLINECOST($case_id) COST_FEE_E from dual");
            $cost_fee_e = $cost_fee_e ? $cost_fee_e[0]["COST_FEE_E"] : 0;

            //付现利润
            $pay_profit_e = floatval($prj_income_e - $cost_fee_e);

            //付现利润率
            //$pay_profit_rate_e = round(($prj_income_e - $cost_fee_e)/$prj_income_e*100,2);
            //付现利润率
            $pay_profit_rate_e = M()->query("select getprjdata($case_id,9,null) PAY_PROFIT_RATE_E from dual");
            $pay_profit_rate_e = $pay_profit_rate_e ? $pay_profit_rate_e[0]["PAY_PROFIT_RATE_E"] : 0;

            //折后广告
            $discount_ad_e = $discount_ad;

            //执行（实际）其他费用
            $other_fee_e = 0;

            //综合利润率
            $Comprehensive_profit_rate_e = round(($prj_income_e - $cost_fee_e - 0 )/$prj_income_e*100,2);
        }

        $project_exe_fee["prj_income_e"] = $prj_income_e;
        $project_exe_fee["cost_fee_e"] = $cost_fee_e;
        $project_exe_fee["pay_profit_e"] = $pay_profit_e;
        $project_exe_fee["pay_profit_rate_e"] = $pay_profit_rate_e;
        $project_exe_fee["discount_ad_e"] = $discount_ad_e;
        $project_exe_fee["other_fee_e"] = $other_fee_e;
        $project_exe_fee["Comprehensive_profit_rate_e"] = $Comprehensive_profit_rate_e;


        //硬广项目实际执行相关费用
        //开票金额
        $sql_invoice = "SELECT SUM(INVOICE_MONEY) SUM_INVOICE_MONEY FROM ERP_BILLING_RECORD WHERE CASE_ID = $case_id AND STATUS = 4";
        $invoice_money_ad = M()->query($sql_invoice);
        $invoice_money_ad = $invoice_money_ad[0]["SUM_INVOICE_MONEY"] ? $invoice_money_ad[0]["SUM_INVOICE_MONEY"] : 0;

        //回款金额
        $sql_return_money = "SELECT SUM(T.INCOME) SUM_MONEY FROM ERP_INCOME_LIST T WHERE T.CASE_ID = {$case_id} AND T.STATUS = 4 ";
        $refund_money_ad = M()->query($sql_return_money);
        $refund_money_ad= $refund_money_ad[0]["SUM_MONEY"] ? $refund_money_ad[0]["SUM_MONEY"] : 0;

        //实际线下费用
        $cost_fee_ad = M()->query("SELECT GETOFFLINECOST($case_id) COST_FEE_E FROM DUAL");
        $cost_fee_ad = $cost_fee_ad ? $cost_fee_ad[0]["COST_FEE_E"] : 0;

        //付现利润
        $pay_profit_ad = $refund_money_ad - $cost_fee_ad;

        //付现利润率
        $pay_profit_rate_ad = round(($refund_money_ad - $cost_fee_ad)/$refund_money_ad * 100,2);

        //合同折扣
        $contract_discount_ad = $discount_ad;
        $contract_exe_fee["invoice_money_ad"] = $invoice_money_ad;
        $contract_exe_fee["refund_money_ad"] = $refund_money_ad;
        $contract_exe_fee["cost_fee_ad"] = $cost_fee_ad;
        $contract_exe_fee["pay_profit_ad"] = $pay_profit_ad;
        $contract_exe_fee["pay_profit_rate_ad"] = $pay_profit_rate_ad;
        $contract_exe_fee["contract_discount_ad"] = $contract_discount_ad;

        //控制类参数
        $this->assign('case_id',$case_id);
        $this->assign('benefits_type',$benefits_type);
        $this->assign('scale_type',$scale_type);
        $this->assign('permisition_con_dis',$permisition_con_dis);
        $this->assign('permisition_in_limit',$permisition_in_limit);
        $this->assign('activities_id',$activities_id);
        $this->assign('fee_type',$fee_type);
        $this->assign('isquota',$isquota);

        //津贴信息参数
        $this->assign('apply_amount',$apply_amount);
        $this->assign('contract_no',$contract_no);
        $this->assign('company',$company);
        $this->assign('contract_money',$contract_money);
        $this->assign('project_name',$project_name);
        $this->assign('sum_money',$sum_money);
        $this->assign('auser',$auser);

        //项目详情对比参数
        //项目预算相关数据
        $this->assign("project_budget_fee",$project_budget_fee);
        //项目执行情况相关数据
        $this->assign("project_exe_fee",$project_exe_fee);
        //硬广执行情况相关数据
        $this->assign("contract_exe_fee",$contract_exe_fee);

        $this->assign('discount_ad',$discount_ad);

        //按钮
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //case ID 案例ID
        $this->assign('caseId', $this->caseId);
        //recordId
        $this->assign('recordId', $this->recordId);
        //菜单
        $this->assign('menu', $this->menu);
        $this->display('process');
    }

    /**
     * 更新业务津贴数据
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
                // 0:尚未审核 -1:已审核
                $data["ISVALID"] = 0;
                $data["MARK"] = "折后广告费";

                //根据活动id查看是否已经有记录
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
            $response["msg"] = g2u("亲，数据保存成功！");
        }
        else{
            M()->rollback();
            $response['status'] = false;
            $response["msg"] = g2u("亲，数据保存失败,请联系管理员！");
        }

        die(@json_encode($response));
    }


    /**
     * @param $benefitId  业务津贴ID
     * @param $scaleType  业务类型
     * @return string
     */
    private function getBenefitSql($benefitId, $scaleType) {
        if (empty($benefitId) || empty($scaleType)) {
            return '';
        }

        //电商
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
     * 审批工作流
     */
    public function opinionFlow() {
        $_REQUEST = u2g($_REQUEST);

        $response = array(
            'status'=>false,
            'message'=>'',
            'data'=>null,
            'url'=>U('Flow/flowList','status=1'),
        );

        //权限判断
        if($this->flowId) {
            if (!$this->myTurn) {
                $response['message'] = g2u('对不起，该工作流您没有权限处理');
                die(@json_encode($response));
            }
        }

        //数据验证
        $error_str = '';
        if($_REQUEST['flowNext'] && !$_REQUEST['DEAL_USERID']){
            $error_str .= "亲，请选择下一步转交人！\n";
        }

        if(!trim($_REQUEST['DEAL_INFO'])){
            $error_str .= "亲，请填写审批意见！\n";
        }

        if($error_str){
            $response['message'] = g2u($error_str);
            die(@json_encode($response));
        }

        //案列ID
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