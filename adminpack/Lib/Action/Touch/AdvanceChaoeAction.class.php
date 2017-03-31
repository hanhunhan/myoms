<?php

class AdvanceChaoeAction extends FlowAction{

    //�����ѯ���
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
          F.NAME AS FEE_NAME��
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
        1 => '�н�',
        2 => '����',
        3 => '����Ӫ��',
        4 => '�ؿ�',
        5 => '����',
        6 => '��Ȼ����',
        7 => '����Դ'
    );

    protected $certificate_type = array(
        '1' => '���֤',
        '2' => '���ڲ�',
        '3' => '����֤',
        '4' => 'ʿ��֤',
        '5' => '����֤',
        '6' => '����',
        '7' => '̨��֤',
        '8' => '����֤',
        '9' => '���֤���۰ģ�',
        '10' => 'Ӫҵִ��',
        '11' => '���˴���',
        '12' => '����',
    );

    //������ϸ֧������
    protected $pay_type = array(
        '1' => 'POS��',
        '2'=> '����',
        '3'=> '�ֽ�',
        '4'=> '�ۺ�',
    );

    //�������֧����ʽ
    protected  $loan_pay_type = array(
        '1' => '�ֽ�',
        '2' => '����',
        '3' => '֧Ʊ',
    );

    protected $reim_type = array(
        1 =>    '�ɹ�',
        2 =>    'Ԥ������������',
        3 =>    '���̻�Ա�н�Ӷ��',
        4 =>    '���̻�Ա�н�ɽ�����',
        5 =>    '���̻�Ա��ҵ����Ӷ��',
        6 =>    '���̻�Ա��ҵ���ʳɽ�����',
        7 =>    '�ֽ������',
        8 =>    '������',
        9 =>    '������Ա�н�Ӷ��',
        10 =>   '������Ա�н�ɽ�����',
        11 =>   '������Ա��ҵ����Ӷ��',
        12 =>   '������Ա��ҵ���ʳɽ�����',
        14 =>   '���ڲɹ�',
        15 =>   'С�۷�ɹ�',
        16 =>   '֧������������',
        17 =>   '������Ӷ�н�Ӷ��',
        21 =>   '���̻�Ա�ⲿ�ɽ�����',
        22 =>   '������Ա�н�ɽ�����',  // ��Ӷ���������
        23 =>   '������Ա��ҵ���ʳɽ�����',  // ��Ӷ���������
        24 =>   '�����ⲿ�ɽ�����',  // ��Ӷ���������
        25 =>   '������Ա�ⲿ�ɽ�������ǰӶ��',//(ǰӶ)
    );

    protected  $reim_status = array(
        0 => 'δ����',
        1 => '�ѱ���',
        4 =>  'ɾ��',
        3 => '�Ѳ���',
    );

    protected $loan_status = array(
        0 => 'δ�ύ',
        1 => '�����',
        2 =>  '�����',
        3 => '���δͨ��',
        4 => '�ѹ�������',
        5 => '��ɾ��',
        6 => '���ֹ�������'

    );

    protected $invoice_status = array(
        1 => 'δ��Ʊ',
        2 => '���ֿ�Ʊ',
        3 => '��ɿ�Ʊ'
    );

    protected  $payment_status = array(
        1 => 'δ�ؿ�',
        2 => '���ֻؿ�',
        3 => '��ȫ�ؿ�'
    );

    public function __construct()
    {
        parent::__construct();

        if (!$this->recordId)
            $this->recordId = intval($_REQUEST['chaoeId']);

        //��ʼ��������
        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.AdvanceChaoe');
        $this->workFlow = new Flow('AdvanceChaoe');
        $this->AdvanceChaoe = new AdvanceChaoe();
        $this->assign('flowType', 'AdvanceChaoe');
        $this->assign('recordId', $this->recordId);

        $this->menu = array(
            'advance_list' => array(
                'name' => 'advance_list',
                'text' => '���ʱ�����������'
            ),
            'advance_detail' => array(
                'name' => 'advance_detail',
                'text' => '������ϸ'
            ),
            'loan_name' =>array(
                'name' => 'loan_name',
                'text' => '�������'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

    }

    public function  show()
    {
        //ת����һ����״̬��
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);
        //��Դ
        $viewData['SOURCE'] = D('Member')->get_conf_member_source_remark();
        $viewData['PAY_TYPE'] = $this->pay_type;
        $viewData['STATUS'] = $this->reim_status;
        $viewData['TYPE'] = $this->reim_type;
        $viewData['certificate_type'] = $this->certificate_type;
        $viewData['loan_pay_type'] = $this->loan_pay_type;
        $viewData['loan_status'] = $this->loan_status;
        $viewData['invoice_status'] = $this->invoice_status;
        $viewData['payment_status'] = $this->payment_status;
        $viewData['ISEXITS'] = array('0' => '��', '1' => '��');

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
        $this->assign('projectName','���ڳ������������');
        $this->assignWorkFlows($this->flowId);
        $this->assign('title','�������������');
        $this->assign('menu',$this->menu);
        $this->display('show');
    }

    //��ȡ��������
    public function getReimList($recordId)
    {
        $city_id = M("Erp_reimbursement_list")->where("ID=".$this->recordId)->getField('CITY_ID');
        if(!empty($recordId)){
            $sql  = sprintf(self::ADVANCECHAOE_LIST_SQL,$city_id,$recordId);
            $dbResult = D()->query($sql);
            return $dbResult['0'];
        }
    }

    //��ȡ������ϸ
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

    //��ȡ�����ϸ
    public function getRelateLoan($recordId)
    {
        if($recordId){
            $sql = sprintf(self::RELATE_LOAN_SQL, $recordId);
            $dbResult = D()->query($sql);
            return $dbResult;
        }
    }

    /**
     * ӳ��ʹ�÷���������
     * @param $response
     * @return mixed
     */
    protected function mapRequirement($response,$viewData) {
                $response['APPLY_TRUENAME'] = array(
                    'alias' => '������',
                    'val' => $response['APPLY_TRUENAME']
                );
                $response['APPLY_TIME'] = array(
                    'alias' => '����ʱ��',
                    'val' => $response['APPLY_TIME']
                );
                $response['AMOUNT'] = array(
                    'alias' => '���',
                    'val' => $response['AMOUNT']
                );
                $response['LOANMONEY'] = array(
                    'alias' => '�����',
                    'val' => $response['LOANMONEY']
                );
                $response['TYPE'] = array(
                    'alias' => '��������',
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