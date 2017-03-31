<?php
/**
 * ���ܿ�����
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/16
 * Time: 11:40
 */

class LoanAction extends ExtendAction {
    const LOAN_INFO_SQL = <<<LOAN_INFO_SQL
        SELECT L.ID,
               L.RESON,
               L.APPLICANT,
               to_char(L.APPDATE,'YYYY-MM-DD hh24:mi:ss') AS APPDATE,
               L.AMOUNT,
               L.CITY_ID,
               L.STATUS AS LOAN_STATUS,
               L.PAYTYPE,
               L.PID,
               P.CONTRACT,
               to_char(L.REPAY_TIME,'YYYY-MM-DD hh24:mi:ss') AS REPAY_TIME,
               P.PROJECTNAME,
               U.NAME AS USER_NAME,
               C.NAME AS CITY_NAME
        FROM ERP_LOANAPPLICATION L
        LEFT JOIN ERP_USERS U ON U.ID = L.APPLICANT
        LEFT JOIN ERP_CITY C ON C.ID = L.CITY_ID
        LEFT JOIN ERP_PROJECT P ON L.PID = P.ID
        WHERE L.ID = %d
LOAN_INFO_SQL;

    protected $loanStatusValues = array(
        0 => 'δ�ύ',
        1 => '�ύδ���',
        2 => '�����',
        3 => '���δͨ��',
        4 => '�ѹ�������',
        5 => '�ѱ���',
        6 => '���ֹ�������'
    );

    /**
     * ��ʼ������
     */
    private function init() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('Loan');
        $this->assign('flowType', 'jiekuanshenqing');
        $this->assign('flowTypeText', '�������');
    }


    public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'biz' => array(
                'name' => 'loan-detail',
                'text' => '�����������'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->init();
    }

    public function process() {
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // ���޸�Ŀǰ��״̬
        } else {
            $this->recordId = $_REQUEST['RECORDID'];
        }

        $loan = $this->getLoanInfo();
        if (is_array($loan) && count($loan)) {
            $this->assign('projectName', '���ڽ����������');  // ��Ŀ����
            $this->assign('loan', $loan);  // �ɹ���Ϣ
        }
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', '�������');
        $this->assign('menu', $this->menu);  // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
        $this->assign('recordId', $this->recordId);
        $this->display('Loan:process');
    }

    protected function mapLoanData($data) {
        $response = array();
        if (is_array($data) && count($data)) {
            $data['AMOUNT'] = floatval($data['AMOUNT']) . self::MONEY_UNIT;
            $data['LOAN_STATUS'] = ($data['LOAN_STATUS'] === null) ? '' : $this->loanStatusValues[intval($data['LOAN_STATUS'])];
            $payTypeList = D('Loan')->getPayTypeList();
            $data['PAYTYPE'] = empty($data['PAYTYPE']) ? '' : $payTypeList[intval($data['PAYTYPE'])];
            $response = array(
                'CITY_NAME' => array(
                    'ALIAS' => '����',
                    'INFO' => $data['CITY_NAME']
                ),
                'PROJECTNAME' => array(
                    'ALIAS' => '��Ŀ����',
                    'INFO' => $data['PROJECTNAME']
                ),
                'CONTRACT' => array(
                    'ALIAS' => '��ͬ���',
                    'INFO' => $data['CONTRACT']
                ),
                'AMOUNT' => array(
                    'ALIAS' => '���',
                    'INFO' => $data['AMOUNT']
                ),
                'REPAY_TIME' => array(
                    'ALIAS' => '����ʱ��',
                    'INFO' => $data['REPAY_TIME']
                ),
                'RESON' => array(
                    'ALIAS' => '����ԭ��',
                    'INFO' => $data['RESON']
                ),
                'USER_NAME' => array(
                    'ALIAS' => '������',
                    'INFO' => $data['USER_NAME']
                ),
                'APPDATE' => array(
                    'ALIAS' => '����ʱ��',
                    'INFO' => $data['APPDATE']
                ),
                'LOAN_STATUS' => array(
                    'ALIAS' => '״̬',
                    'INFO' => $data['LOAN_STATUS']
                ),
                'PAYTYPE' => array(
                    'ALIAS' => '֧����ʽ',
                    'INFO' => $data['PAYTYPE']
                )
            );
        }

        return $response;
    }

    protected function getLoanInfo() {
        $response = array();
        if ($this->recordId) {
            $result = D()->query(sprintf(self::LOAN_INFO_SQL, $this->recordId));
            if (is_array($result) && count($result)) {
                $response = $result[0];
                $response = $this->mapLoanData($response);
            }
        }

        return $response;
    }

    public function opinionFlow() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        if ($this->myTurn) {
            $_REQUEST = u2g($_REQUEST);
            $result = $this->workFlow->doit($_REQUEST);
            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        } else {
            $response['message'] = '�ǵ�ǰ������';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }
        echo json_encode(g2u($response));
    }

    protected function authMyTurn($flowId) {
        if (intval($flowId) > 0) {
            parent::authMyTurn($flowId);
        } else {
            $this->myTurn = true;
        }
    }
}