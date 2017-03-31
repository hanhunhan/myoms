<?php

class AdvanceChaoeAction extends FlowAction{

    //详情查询语句
    const ADVANCECHAOE_LIST_SQL  = <<<SQL
          SELECT TYPE,APPLY_TRUENAME,
       to_char(APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME, AMOUNT,
       LOANMONEY
       FROM
  (SELECT a.*, b.LOANMONEY
   FROM ERP_REIMBURSEMENT_LIST a
   LEFT JOIN
     (SELECT APPLICANT,
             sum(UNREPAYMENT) AS LOANMONEY
      FROM ERP_LOANAPPLICATION
      WHERE STATUS IN(2,6) AND CITY_ID = %d GROUP BY APPLICANT) b ON a.APPLY_UID = b.APPLICANT)
  WHERE 1=1 
  AND STATUS != '4' AND ID = %d
SQL;
    const ADVANCECHAOE_DETAIL_XMF_SQL =<<<ADVANCECHAOE_DETAIL_XMF_SQL
    SELECT ID,
       TYPE,
       PROJECTNAME,
       STATUS,
       MONEY,
       ISKF,
       ISFUNDPOOL,
       BUSINESS_ID,
       SUPPLIER,
       TOTAL_NUM,
       TOTAL_WAGES,
       TOTAL_BONUS,
       TOTAL_MONEY,
       FEE_ID,
       MARK
FROM
  (SELECT L.SUPPLIER,
          L.TOTAL_NUM,
          L.TOTAL_WAGES,
          L.TOTAL_BONUS,
          L.TOTAL_MONEY,
          L.MARK,
          L.FILE1,
          L.FILE2,
          D.ID,
          D.BUSINESS_ID,
          D.TYPE,
          D.STATUS,
          D.MONEY,
          D.ISKF,
          D.ISFUNDPOOL,
          D.INPUT_TAX,
          D.LIST_ID,
          P.PROJECTNAME,
          L.REIM_MONEY,
          F.NAME AS FEE_ID
   FROM ERP_REIMBURSEMENT_DETAIL D
   INNER JOIN ERP_CASE C ON C.ID = D.CASE_ID
   INNER JOIN ERP_PROJECT P ON P.ID = C.PROJECT_ID
   INNER JOIN ERP_PURCHASER_BEE_DETAILS L ON D.PURCHASER_BEE_ID = L.ID
   INNER JOIN ERP_FEE F ON F.ID = D.FEE_ID)
WHERE 1=1
  AND LIST_ID = %d
  AND STATUS != '4'
ORDER BY ID DESC
ADVANCECHAOE_DETAIL_XMF_SQL;

    const ADVANCECHAOE_DETAIL_XJ_SQL =<<<ADVANCECHAOE_DETAIL_XJ_SQL
    SELECT ID,
       PRJ_ID,
       to_char(OCCUR_TIME,'YYYY-MM-DD') AS OCCUR_TIME,
       NUM,
       FF_MONEY,
       MONEY,
       ISFUNDPOOL,
       ISKF,
       TYPE,
       STATUS
FROM
  (SELECT D.*,
          G.PRJ_ID,
          G.NUM,
          G.MONEY AS FF_MONEY,
          G.OCCUR_TIME,
          G.ADD_UID
   FROM ERP_REIMBURSEMENT_DETAIL D
   INNER JOIN ERP_LOCALE_GRANTED G ON D.BUSINESS_ID = G.ID
   AND D.CASE_ID = G.CASE_ID
   AND D.CITY_ID = G.CITY_ID)
WHERE 1=1
  AND LIST_ID = %d
  AND STATUS != '4'
ORDER BY ID DESC
ADVANCECHAOE_DETAIL_XJ_SQL;

    const ADVANCECHAOE_DETAIL_SQL = <<<ADVANCECHAOE_DETAIL_SQL
            SELECT D.*,
           C.PRJ_ID,
           C.PRJ_NAME,
           C.REALNAME,
           C.MOBILENO,
           C.SOURCE,
           C.ROOMNO,
          
		  to_char(C.CARDTIME,'yyyy-mm-dd ') as CARDTIME,
           C.CERTIFICATE_TYPE,
           C.CERTIFICATE_NO,
           C.ADD_UID,
           C.PAY_TYPE,
           C.PAID_MONEY,
           C.UNPAID_MONEY,
           C.HOUSETOTAL,
           C.HOUSEAREA,
           C.AGENCY_REWARD,
           C.AGENCY_DEAL_REWARD,
           C.PROPERTY_DEAL_REWARD,
           C.AGENCY_NAME,
           C.OUT_REWARD
FROM ERP_REIMBURSEMENT_DETAIL D
INNER JOIN ERP_CARDMEMBER C ON D.BUSINESS_ID = C.ID
AND D.CASE_ID = C.CASE_ID
AND D.CITY_ID = C.CITY_ID
WHERE 1=1
  AND LIST_ID = %d
  AND D.STATUS != '4'
ADVANCECHAOE_DETAIL_SQL;

    const ADVANCECHAOE_DETAIL_FXQY_SQL = <<<ADVANCECHAOE_DETAIL_FXQY_SQL
       SELECT ID,
       PROJECTNAME,
       REALNAME,
       MOBILENO,
       CERTIFICATE_TYPE,
       TOTAL_PRICE,
       IDCARDNO,
       ROOMNO,
       HOUSEAREA,
       HOUSETOTAL,
       to_char(SIGNTIME,'YYYY-MM-DD') AS SIGNTIME,
       MONEY,
       TYPE,
       ISFUNDPOOL,
       ISKF,
       STATUS,
       INVOICE_STATUS,
       PAYMENT_STATUS,
       STYPE
FROM
  (SELECT D.*,
          P.PROJECTNAME,
          M.PRJ_ID,
          M.REALNAME,
          M.MOBILENO,
          M.HOUSEAREA,
          M.ROOMNO,
          M.HOUSETOTAL,
          M.SIGNTIME,
          PC.INVOICE_STATUS,
          PC.PAYMENT_STATUS,
          M.ADD_UID,
          M.ADD_USERNAME,
          M.CERTIFICATE_TYPE,
          M.CERTIFICATE_NO AS IDCARDNO,
          M.AGENCY_REWARD_AFTER AS AGENCY_REWARD,
          M.AGENCY_DEAL_REWARD,
          M.PROPERTY_REWARD,
          M.PROPERTY_DEAL_REWARD,
          M.OUT_REWARD,
          M.TOTAL_PRICE_AFTER AS TOTAL_PRICE,
          F.STYPE
   FROM ERP_REIMBURSEMENT_DETAIL D
   INNER JOIN ERP_CARDMEMBER M ON D.BUSINESS_ID = M.ID
   LEFT JOIN ERP_POST_COMMISSION PC ON PC.card_member_id = M.ID
   LEFT JOIN ERP_FEESCALE F ON F.CASE_ID = D.CASE_ID
   AND F.AMOUNT = M.TOTAL_PRICE_AFTER
   AND D.CASE_ID = M.CASE_ID
   AND D.CITY_ID = M.CITY_ID
   AND F.MTYPE =1 AND F.ISVALID = -1 AND F.SCALETYPE = 1
   INNER JOIN ERP_CASE C ON D.CASE_ID = C.ID
   INNER JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID)
WHERE 1=1
  AND LIST_ID  = %d
  AND STATUS != 4
ORDER BY ID DESC
ADVANCECHAOE_DETAIL_FXQY_SQL;

    const ADVANCECHAOE_DETAIL_FXHY_SQL = <<<ADVANCECHAOE_DETAIL_FXHY_SQL
    SELECT ID,
       REIM_DETAIL_ID,
       PRJ_NAME,
       MEMBER_ID,
       MOBILENO,
       REALNAME,
       HOUSETOTAL,
       AGENCY_REWARD_AFTER,
       PERCENT,
       AMOUNT,
       STATUS,
       ISKF,
       INVOICE_STATUS,
       ISFUNDPOOL,
       PAYMENT_STATUS
FROM
  (SELECT m.REALNAME,
          m.HOUSETOTAL,
          m.TOTAL_PRICE,
          concatUnit(m.AGENCY_REWARD_AFTER, f1.Stype) AGENCY_REWARD_AFTER,
          m.PRJ_NAME,
          m.ID AS MEMBER_ID,
          m.MOBILENO,
          d.amount,
          d.percent,
          d.status,
          d.reim_list_id,
          d.reim_detail_id,
          d.id,
          c.INVOICE_STATUS,
          c.PAYMENT_STATUS,
          r.isfundpool,
          r.iskf
   FROM erp_commission_reim_detail d
   LEFT JOIN erp_post_commission c ON c.id = d.post_commission_id
   LEFT JOIN erp_reimbursement_detail r ON r.id = d.reim_detail_id
   LEFT JOIN erp_cardmember m ON m.id = c.card_member_id
   LEFT JOIN ERP_FEESCALE f1 ON f1.case_id = m.case_id
   AND f1.amount = m.AGENCY_REWARD_AFTER
   AND f1.SCALETYPE = 2
   AND f1.ISVALID = -1
   AND f1.MTYPE = 1)
WHERE 1=1
  AND REIM_LIST_ID=%d
ORDER BY ID DESC
ADVANCECHAOE_DETAIL_FXHY_SQL;

    const ADVANCECHAOE_DETAIL_CG_SQL =  <<<ADVANCECHAOE_DETAIL_CG_SQL
    SELECT ID,
       BUSINESS_ID,
       PROJECTNAME,
       CONTRACT_ID,
       SUPPLIER_NAME,
       PRODUCT_NAME,
       FEE_NAME,
       BRAND,
       MODEL,
       P_ID,
       PRICE_LIMIT,
       NUM_LIMIT,
       PRICE,
       NUM,
       MONEY,
       TYPE,
       to_char(ADD_TIME,'YYYY-MM-DD hh24:mi:ss') AS ADD_TIME,
       to_char(COST_OCCUR_TIME,'YYYY-MM-DD hh24:mi:ss') AS COST_OCCUR_TIME,
       to_char(PURCHASE_OCCUR_TIME,'YYYY-MM-DD hh24:mi:ss') AS PURCHASE_OCCUR_TIME,
       ISFUNDPOOL,
       ISKF,
       STATUS
FROM
  (SELECT L.BRAND,
          L.MODEL,
          L.NUM,
          L.NUM_LIMIT,
          L.PRICE,
          L.PRICE_LIMIT,
          L.PRODUCT_NAME,
          U.NAME AS P_ID,
          L.ADD_TIME,
          getReimPurchaseContractId(L.CONTRACT_ID, B.ID) AS CONTRACT_ID,
          L.COST_OCCUR_TIME,
          L.PURCHASE_OCCUR_TIME,
          D.*,
          F.NAME AS FEE_NAME，
          B.PROJECTNAME,
          S.NAME SUPPLIER_NAME
   FROM ERP_REIMBURSEMENT_DETAIL D
   LEFT JOIN ERP_CASE A ON D.CASE_ID = A.ID
   LEFT JOIN ERP_PROJECT B ON A.PROJECT_ID = B.ID
    LEFT JOIN ERP_FEE F ON F.ID = D.FEE_ID
   INNER JOIN ERP_PURCHASE_LIST L ON D.BUSINESS_ID = L.ID
   INNER JOIN ERP_PURCHASE_REQUISITION PR ON D.CASE_ID = PR.CASE_ID
   INNER JOIN ERP_SUPPLIER S ON S.ID = L.S_ID
   INNER JOIN ERP_USERS U ON U.ID = L.P_ID

   AND PR.ID = L.PR_ID)
WHERE 1=1
  AND LIST_ID = %d
  AND STATUS != '4'
ORDER BY ID DESC
ADVANCECHAOE_DETAIL_CG_SQL;



    const RELATE_LOAN_SQL = <<<RELATE_LOAN_SQL
        SELECT R.ID AS RLID,
       L.ID,
       L.CITY_ID,
       R.MONEY AS LOANMONEY,
       R.REIMID,
       L.PAYTYPE,
       T.NAME AS CITYNAME,
       P.ID AS PID,
       P.PROJECTNAME,
       P.CONTRACT,
       L.STATUS,
       L.AMOUNT,
       L.REPAY_TIME,
       L.UNREPAYMENT,
       L.RESON,
       L.APPLICANT,
       U.NAME AS USERNAME,
       APPDATE
FROM ERP_LOANAPPLICATION L
LEFT JOIN ERP_CITY T ON L.CITY_ID = T.ID
LEFT JOIN ERP_PROJECT P ON L.PID = P.ID
LEFT JOIN ERP_USERS U ON L.APPLICANT = U.ID
RIGHT JOIN ERP_REIMLOAN R ON L.ID = R.LOANID
WHERE R.REIMID = %d
ORDER BY RLID DESC
RELATE_LOAN_SQL;

    protected $reimSource = array(
        1 => '中介',
        2 => '渠道',
        3 => '数据营销',
        4 => '拓客',
        5 => '线上',
        6 => '自然来客',
        7 => '新来源'
    );

    protected $certificate_type = array(
        '1' => '身份证',
        '2' => '户口簿',
        '3' => '军官证',
        '4' => '士兵证',
        '5' => '警官证',
        '6' => '护照',
        '7' => '台胞证',
        '8' => '回乡证',
        '9' => '身份证（港澳）',
        '10' => '营业执照',
        '11' => '法人代码',
        '12' => '其它',
    );

    //报销明细支付类型
    protected $pay_type = array(
        '1' => 'POS机',
        '2'=> '网银',
        '3'=> '现金',
        '4'=> '综合',
    );

    //关联借款支付方式
    protected  $loan_pay_type = array(
        '1' => '现金',
        '2' => '网银',
        '3' => '支票',
    );

    protected $reim_type = array(
        1 =>    '采购',
        2 =>    '预算外其他费用',
        3 =>    '电商会员中介佣金',
        4 =>    '电商会员中介成交奖励',
        5 =>    '电商会员置业顾问佣金',
        6 =>    '电商会员置业顾问成交奖励',
        7 =>    '现金带看奖',
        8 =>    '带看奖',
        9 =>    '分销会员中介佣金',
        10 =>   '分销会员中介成交奖励',
        11 =>   '分销会员置业顾问佣金',
        12 =>   '分销会员置业顾问成交奖励',
        14 =>   '大宗采购',
        15 =>   '小蜜蜂采购',
        16 =>   '支付第三方费用',
        17 =>   '分销后佣中介佣金',
        21 =>   '电商会员外部成交奖励',
        22 =>   '分销会员中介成交奖励',  // 从佣金管理申请
        23 =>   '分销会员置业顾问成交奖励',  // 从佣金管理申请
        24 =>   '分销外部成交奖励',  // 从佣金管理申请
        25 =>   '分销会员外部成交奖励（前佣）',//(前佣)
    );

    protected  $reim_status = array(
        0 => '未报销',
        1 => '已报销',
        4 =>  '删除',
        3 => '已驳回',
    );

    protected $loan_status = array(
        0 => '未提交',
        1 => '审核中',
        2 =>  '已审核',
        3 => '审核未通过',
        4 => '已关联报销',
        5 => '已删除',
        6 => '部分关联报销'

    );

    protected $invoice_status = array(
        1 => '未开票',
        2 => '部分开票',
        3 => '完成开票'
    );

    protected  $payment_status = array(
        1 => '未回款',
        2 => '部分回款',
        3 => '完全回款'
    );

    public function __construct()
    {
        parent::__construct();

        if (!$this->recordId)
            $this->recordId = intval($_REQUEST['chaoeId']);

        //初始化工作流
        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.AdvanceChaoe');
        $this->workFlow = new Flow('AdvanceChaoe');
        $this->AdvanceChaoe = new AdvanceChaoe();
        $this->assign('flowType', 'AdvanceChaoe');
        $this->assign('recordId', $this->recordId);

        $this->menu = array(
            'advance_list' => array(
                'name' => 'advance_list',
                'text' => '垫资比例超额详情'
            ),
            'advance_detail' => array(
                'name' => 'advance_detail',
                'text' => '报销明细'
            ),
            'loan_name' =>array(
                'name' => 'loan_name',
                'text' => '关联借款'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

    }

    public function  show()
    {
        //转交下一步（状态）
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);
        //来源
        $viewData['SOURCE'] = D('Member')->get_conf_member_source_remark();
        $viewData['PAY_TYPE'] = $this->pay_type;
        $viewData['STATUS'] = $this->reim_status;
        $viewData['TYPE'] = $this->reim_type;
        $viewData['certificate_type'] = $this->certificate_type;
        $viewData['loan_pay_type'] = $this->loan_pay_type;
        $viewData['loan_status'] = $this->loan_status;
        $viewData['invoice_status'] = $this->invoice_status;
        $viewData['payment_status'] = $this->payment_status;
        $viewData['ISEXITS'] = array('0' => '否', '1' => '是');

        $this->assign('viewData',$viewData);


        $this->assignWorkFlows($this->flowId);
        $reimList = $this->getReimList($this->recordId);
        $relateLoan = $this->getRelateLoan($this->recordId);
        $list_type = M("Erp_reimbursement_list")->where("ID=".$this->recordId)->getField('type');
        $reimDetail = $this->getReimDetail($this->recordId,$list_type);
        $this->assign('type',$list_type);
        $this->assign('relateLoan',$relateLoan);
        $this->assign('require',$this->mapRequirement($reimList,$viewData));
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        $this->assign('reimDetail',$reimDetail);
        $this->assign('projectName','关于超额报销申请的审核');
        $this->assignWorkFlows($this->flowId);
        $this->assign('title','超额报销申请流程');
        $this->assign('menu',$this->menu);
        $this->display('show');
    }

    //获取报销详情
    public function getReimList($recordId)
    {
        $city_id = M("Erp_reimbursement_list")->where("ID=".$this->recordId)->getField('CITY_ID');
        if(!empty($recordId)){
            $sql  = sprintf(self::ADVANCECHAOE_LIST_SQL,$city_id,$recordId);
            $dbResult = D()->query($sql);
            return $dbResult['0'];
        }
    }

    //获取报销明细
    public function getReimDetail($recordId,$list_type)
    { 
        if($recordId){
            switch($list_type){
                case 17:
                    $sql = sprintf(self::ADVANCECHAOE_DETAIL_FXHY_SQL, $recordId);
                    $dbResult = D()->query($sql);
                    break;
                case 11:
                case 22:
                case 23:
                case 24:
                    $sql = sprintf(self::ADVANCECHAOE_DETAIL_FXQY_SQL, $recordId);
                    $dbResult = D()->query($sql);
                    break;
                case 1:
                    $sql = sprintf(self::ADVANCECHAOE_DETAIL_CG_SQL, $recordId);
                    $dbResult = D()->query($sql);
                    break;
                case 3:
                case 4:
                case 5:
                case 6:
                case 9:
                case 10:
                case 12:
                case 21:
                case 25:
                    $sql = sprintf(self::ADVANCECHAOE_DETAIL_SQL, $recordId);
                    $dbResult = D()->query($sql);
                    break;
                case 7:
                    $sql = sprintf(self::ADVANCECHAOE_DETAIL_XJ_SQL, $recordId);
                    $dbResult = D()->query($sql);
                    break;
                case 15:
                    $sql = sprintf(self::ADVANCECHAOE_DETAIL_XMF_SQL, $recordId);
                    $dbResult = D()->query($sql);
                    break;
            }
            return $dbResult;
        }
    }

    //获取借款明细
    public function getRelateLoan($recordId)
    {
        if($recordId){
            $sql = sprintf(self::RELATE_LOAN_SQL, $recordId);
            $dbResult = D()->query($sql);
            return $dbResult;
        }
    }

    /**
     * 映射使用方法的数据
     * @param $response
     * @return mixed
     */
    protected function mapRequirement($response,$viewData) {
                $response['APPLY_TRUENAME'] = array(
                    'alias' => '申请人',
                    'val' => $response['APPLY_TRUENAME']
                );
                $response['APPLY_TIME'] = array(
                    'alias' => '申请时间',
                    'val' => $response['APPLY_TIME']
                );
                $response['AMOUNT'] = array(
                    'alias' => '金额',
                    'val' => $response['AMOUNT']
                );
                $response['LOANMONEY'] = array(
                    'alias' => '借款金额',
                    'val' => $response['LOANMONEY']
                );
                $response['TYPE'] = array(
                    'alias' => '报销类型',
                    'val' => $viewData['TYPE'][$response['TYPE']]
                );

        return $response;
    }

    protected function authMyTurn($flowId) {
        if (intval($flowId) > 0) {
            parent::authMyTurn($flowId);
        } else {
            switch($this->flowType) {

                default:
            }

            $this->myTurn = true;
        }
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