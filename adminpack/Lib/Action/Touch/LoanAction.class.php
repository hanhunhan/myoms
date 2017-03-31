<?php
/**
 * 借款功能控制器
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
        0 => '未提交',
        1 => '提交未审核',
        2 => '已审核',
        3 => '审核未通过',
        4 => '已关联报销',
        5 => '已报销',
        6 => '部分关联报销'
    );

    /**
     * 初始化方法
     */
    private function init() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('Loan');
        $this->assign('flowType', 'jiekuanshenqing');
        $this->assign('flowTypeText', '借款申请');
    }


    public function __construct() {
        parent::__construct();

        // 初始化菜单
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'biz' => array(
                'name' => 'loan-detail',
                'text' => '借款申请详情'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->init();
    }

    public function process() {
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // 先修改目前的状态
        } else {
            $this->recordId = $_REQUEST['RECORDID'];
        }

        $loan = $this->getLoanInfo();
        if (is_array($loan) && count($loan)) {
            $this->assign('projectName', '关于借款申请的审核');  // 项目名称
            $this->assign('loan', $loan);  // 采购信息
        }
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', '借款申请');
        $this->assign('menu', $this->menu);  // 菜单
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // 控制按钮显示
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
                    'ALIAS' => '城市',
                    'INFO' => $data['CITY_NAME']
                ),
                'PROJECTNAME' => array(
                    'ALIAS' => '项目名称',
                    'INFO' => $data['PROJECTNAME']
                ),
                'CONTRACT' => array(
                    'ALIAS' => '合同编号',
                    'INFO' => $data['CONTRACT']
                ),
                'AMOUNT' => array(
                    'ALIAS' => '金额',
                    'INFO' => $data['AMOUNT']
                ),
                'REPAY_TIME' => array(
                    'ALIAS' => '还款时间',
                    'INFO' => $data['REPAY_TIME']
                ),
                'RESON' => array(
                    'ALIAS' => '申请原因',
                    'INFO' => $data['RESON']
                ),
                'USER_NAME' => array(
                    'ALIAS' => '申请人',
                    'INFO' => $data['USER_NAME']
                ),
                'APPDATE' => array(
                    'ALIAS' => '申请时间',
                    'INFO' => $data['APPDATE']
                ),
                'LOAN_STATUS' => array(
                    'ALIAS' => '状态',
                    'INFO' => $data['LOAN_STATUS']
                ),
                'PAYTYPE' => array(
                    'ALIAS' => '支付方式',
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
            $response['message'] = '非当前审批人';
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