<?php
/**
 * 合同开票
 * Created by PhpStorm.
 * User: superkemi
 */

class AdvertAction extends ExtendAction {
    /*
     * 构造函数
     */
    protected $feeScaleType = null;
    private $caseId = 0;
    public $invoiceId = 0;

    public function __construct() {
        parent::__construct();

        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'invoice_apply' => array(
                'name' => 'invoice-apply',
                'text' => '开票申请'
            ),
            'invoice_detail' => array(
                'name' => 'invoice-detail',
                'text' => '开票明细'
            ),
            'members' => array(
                'name' => 'members',
                'text' => '分销会员'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->feeScaleType = array(
            '1'=>'单套收费标准',
            '2'=>'中介佣金',
            '4'=>'中介成交奖',
            '5'=>'置业顾问成交奖',
            '6'=>'带看奖',
        );

        $this->caseId = intval($_REQUEST['CASEID']);

        $this->invoiceId = intval($_REQUEST['invoiceId']);

        //recordId
        //解决第一次调用数据展现问题（创建工作流）
        if(!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        $this->title = '合同开票';
        $this->processTitle = '关于合同开票申请的审核';

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.Advert');
        $this->workFlow = new Flow('Advert');
        $this->advert = new Advert();

        $this->assign('flowId', $this->flowId);
        $this->assign('CASEID', $this->caseId);
        $this->assign('recordId', $this->recordId);
        $this->assign('title', $this->title);
    }

    /**
     * 展示流程信息
     */
    public function process() {

        $case_id = $this->caseId;

        //process数据
        $viewData = array();

        //转交下一步（状态）
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);

        //开票明细
        $totalInfo = $this->getTotalInfo();

        //开票申请信息
        $applyInfo = $this->getApplyInfo($case_id,$totalInfo['invoiceId']);
        if (!empty($applyInfo[0]['FILES'])) {
            $attachment = $this->getWorkFlowFiles($applyInfo[0]['FILES']);  // 获取文件列表
            $this->assign('attachment', $attachment);
        }


        //项目分销会员
        $memberInfo = $this->getMemberInfo($case_id,$totalInfo['invoiceId']);
        if (empty($memberInfo)) {
            unset($this->menu['members']);
        }
        $this->invoiceId = $totalInfo['invoiceId'];

        //赋值
        $viewData['totalInfo'] = $totalInfo;
        $viewData['applyInfo'] = $applyInfo;
        $viewData['memberInfo'] = $memberInfo;

        //发票状态
//        $status_arr = D("Member")->get_conf_all_status_remark();
        $viewData['invoiceStatus'] = array(
            1 => '未开票',
            2 => '申请中',
            3 => '已开票',
        );

        //业务类型
        $viewData['caseType'] = D("ProjectCase")->get_conf_case_type_remark();

        //获取收费标准
        //设置收费标准
        $feescale = D('Project')->get_feescale_by_cid($case_id);


        $fees_arr = array();
        if(is_array($feescale) && !empty($feescale) ) {
            foreach ($feescale as $key => $value) {
                $unit = $value['STYPE'] == 0 ? '元' : '%';
                if (($value['SCALETYPE'] == 2 && $value['MTYPE'] != 1) or ($value['SCALETYPE'] == 1 && $value['MTYPE'] != 1)) {
                    continue;
                }
                $fees_arr[$value['SCALETYPE']][$value['AMOUNT']] = $value['AMOUNT'] . $unit;
            }
        }

        $viewData['feesArr'] = $fees_arr;

        //证件类型
        $certificateType = D("Member")->get_conf_certificate_type();

        //退票信息
        $this->assign('viewData', $viewData);
        $this->assign('certificateType', $certificateType);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        $this->assign('invoiceId', $totalInfo['invoiceId']);
        //菜单
        $this->assign('menu', $this->menu);
        $this->display('process');
    }

    /**
     * 获取合同开票的汇总信息
     * @param $recordId
     */
    private function getTotalInfo(){

        $response = array();

        $billing_model = D("BillingRecord");
        $invoice_info = $billing_model->get_info_by_cond("FLOW_ID=" . $this->flowId, array("ID"));
        $invoiceId = $invoice_info[0]["ID"];

        //如果是创建工作流，那么这个发票ID通过GET方式得到
        if(!$invoiceId)
            $invoiceId = $this->invoiceId;

        $response['invoiceId'] = $invoiceId;

        $response['money'] = M("Erp_income_contract")->where("ID = {$this->recordId}")
            ->field('MONEY')->find();
        $response['money'] = floatval($response['money']['MONEY']);

        $invoice_info = $billing_model->get_info_by_id($invoiceId, array("INVOICE_MONEY","INVOICE_CLASS","INVOICE_BIZ_TYPE", "REMARK"));
        $response['invoice_money'] = $invoice_info[0]["INVOICE_MONEY"];//本次申请金额
        $response['remark'] = $invoice_info[0]["REMARK"];

        $invoiceClassArr = $billing_model->get_invoice_class();
        $response['invoice_class'] = $invoiceClassArr[$invoice_info[0]['INVOICE_CLASS']];

        $invoiceBizTypeArr = $billing_model->get_invoice_biz_type_remark();
        $response['invoice_biz_type'] = $invoiceBizTypeArr[$invoice_info[0]["INVOICE_BIZ_TYPE"]];

        $response['sum_money'] = M("Erp_billing_record")->where("CONTRACT_ID = {$this->recordId} AND STATUS IN(4,6,7)")
            ->field('SUM(INVOICE_MONEY) AS SUM_MONEY')->find();
        $response['sum_money'] = floatval($response['sum_money']['SUM_MONEY']);

        return $response;

    }


    private function getApplyInfo($case_id,$invoiceId){
        $applyInfo = array();

        $sql = <<<APPLY_SQL
            SELECT A.ID,A.APPLY_USER_ID,U.NAME AS APPLY_USER_NAME,TO_CHAR(A.CREATETIME,'YYYY-MM-DD HH24:MI:SS') CREATETIME,
                A.CASE_ID,B.CONTRACT_NO,C.SCALETYPE,D.PROJECTNAME,D.CONTRACT,D.CITY_ID,T.NAME AS CITY_NAME
                , A.FILES
                 FROM ERP_BILLING_RECORD A
                 LEFT JOIN ERP_INCOME_CONTRACT B ON A.CONTRACT_ID=B.ID
                 LEFT JOIN ERP_CASE C ON A.CASE_ID=C.ID
                 LEFT JOIN ERP_PROJECT D ON C.PROJECT_ID=D.ID
                 LEFT JOIN ERP_CITY T ON D.CITY_ID = T.ID
                 LEFT JOIN ERP_USERS U ON U.ID = A.APPLY_USER_ID
                 WHERE A.CASE_ID=%d AND A.ID=%d
APPLY_SQL;

        $sql = sprintf($sql,$case_id,$invoiceId);

        $applyInfo = M()->query($sql);
        return $applyInfo;

    }

    /***
     * @param $id 退票审核单LISTID
     * @return array
     */
    protected function getMemberInfo($case_id,$invoiceId) {
        //当前用户编号
        $uid = intval($_SESSION['uinfo']['uid']);

        //当前城市编号
        $city_id = intval($this->channelid);

//        $sql = <<<MemberInfo_SQL
//        SELECT M.REALNAME,M.MOBILENO,M.SIGNTIME,M.SIGNEDSUITE,M.INVOICE_STATUS,M.INVOICE_NO,M.AGENCY_REWARD,M.AGENCY_DEAL_REWARD,M.PROPERTY_DEAL_REWARD,M.TOTAL_PRICE,M.CERTIFICATE_TYPE,M.IDCARDNO,M.ROOMNO,M.HOUSEAREA,M.HOUSETOTAL
//            FROM ERP_MEMBER_DISTRIBUTION M
//            WHERE M.CASE_ID = %d AND M.RELATE_INVOICE_ID = %d
//MemberInfo_SQL;

        $sql = <<<MEMBER_LIST_SQL
            SELECT m.REALNAME,
                   m.MOBILENO,
                   m.SIGNTIME,
                   m.SIGNEDSUITE,
                   d.INVOICE_STATUS,
                   d.INVOICE_NO,
                   M.AGENCY_REWARD_AFTER,
                   M.AGENCY_DEAL_REWARD,
                   M.PROPERTY_DEAL_REWARD,
                   M.TOTAL_PRICE_AFTER,
                   M.CERTIFICATE_TYPE,
                   m.CERTIFICATE_NO AS IDCARDNO,
                   m.OUT_REWARD,
                   M.ROOMNO,
                   M.HOUSEAREA,
                   M.HOUSETOTAL,
                   d.PERCENT,
                   d.AMOUNT
            FROM erp_commission_invoice_detail d
            LEFT JOIN erp_post_commission c ON c.ID = d.POST_COMMISSION_ID
            LEFT JOIN erp_cardmember m ON m.ID = c.CARD_MEMBER_ID
            WHERE d.BILLING_RECORD_ID = %d
MEMBER_LIST_SQL;


        $sql = sprintf($sql, $invoiceId);

        $feeScaleInfo = M()->query($sql);
        return $feeScaleInfo;
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

        //赋值caseId
        $_REQUEST['caseId'] = $this->caseId;
        $_REQUEST['invoiceId'] = $this->invoiceId;

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