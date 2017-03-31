<?php
/**
 * 垫资比例调整控制器
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/16
 * Time: 9:15
 */

class Payout_changeAction extends ExtendAction {

    const PAYOUT_INFO_SQL = <<<PAYOUT_INFO_SQL
        SELECT
               Y.NAME AS CITY_NAME,
               T.APPLY_USER,
               TO_CHAR(T.APPLY_DATE,'YYYY-MM-DD') AS APPLY_DATE,
               T.NEW_PAY_OUT,
               T.STATUS AS PAYOUT_STATUS,
               T.REASON,
               T.CASE_ID,
               P.PROJECTNAME,
               P.CONTRACT,
               B.YEWU
        FROM ERP_PAYOUT T
        LEFT JOIN ERP_CASE C ON C.ID = T.CASE_ID
        LEFT JOIN ERP_PROJECT P ON P.ID = C.PROJECT_ID
        LEFT JOIN ERP_BUSINESSCLASS B ON B.ID = C.SCALETYPE
        LEFT JOIN ERP_CITY Y ON Y.ID = T.CITY_ID
        WHERE T.ID = %d
PAYOUT_INFO_SQL;

    protected $payoutStatusValues = array(
        1 => "未申请",
        2 => "已申请，审核中",
        3 => "审核通过",
        4 => "审核未通过",
    );

    /**
     * 新的垫资比例
     * @var
     */
    protected $newPayout;


    public function __construct() {
        parent::__construct();

        // 初始化菜单
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '申请说明'
            ),
            'payout_change_detail' => array(
                'name' => 'payout-change-detail',
                'text' => '垫资比例调整明细'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        $this->init();
    }

    /**
     * 初始化方法
     */
    private function init() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('PayoutChange');
        $this->assign('flowType', 'dianziedu');
        $this->assign('flowTypeText', '垫资比例调整');
    }

    /**
     * 处理
     */
    public function process() {
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // 先修改目前的状态
        } else {
            $this->recordId = $_REQUEST['RECORDID'];

        }
        $payout = $this->getPayoutChangeInfo();

        if (is_array($payout) && count($payout)) {
            $this->assign('projectName', '关于垫资比例调整申请的审核');  // 项目名称
            $this->assign('payout', $payout);  // 采购信息
        }
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', '垫资比例调整');
        $this->assign('menu', $this->menu);  // 菜单
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // 控制按钮显示
        $this->assign('CASEID', $this->CASEID);
        $this->assign('recordId', $this->recordId);
        $this->display('PayoutChange:process');
    }

    /**
     * 获取垫资调整信息
     * @return array
     */
    public function getPayoutChangeInfo() {
        $response = array();
        if ($this->recordId) {
            $result = D()->query(sprintf(self::PAYOUT_INFO_SQL, $this->recordId));
            if (is_array($result) && count($result)) {
                $response = $result[0];
                $response['PAID_AMOUNT'] = '';  // 已垫资金额
                $response['PAID_RATE'] = '';  // 已垫资比例
                $response['PAYOUT_COST'] = '';  // 付现成本
                $response['PAYOUT_RATE'] = '';  // 付现成本率

                if ($response['CASE_ID']) {
                    $this->CASEID = $response['CASE_ID'];  // 更新案例编号
                    // 已垫资
                    $response['PAID_AMOUNT'] = D('ProjectCase')->getLoanMoney($response['CASE_ID'], 0, 1); // 金额
                    $response['PAID_RATE'] = D('ProjectCase')->getLoanMoney($response['CASE_ID'],0, 2);  // 比例

                    // 立项预算付现
                    $payoutCost = null;  // 成本
                    $payoutRate = null;  // 成本率
                    D('ProjectCase')->getPreCostPreRate($response['CASE_ID'], $payoutCost, $payoutRate);
                    $response['PAYOUT_COST'] = $payoutCost;  // 付现成本
                    $response['PAYOUT_RATE'] = $payoutRate;  // 付现成本率
                }
                $response = $this->mapPayoutChangeData($response);
            }

        }

        return $response;
    }

    /**
     * 映射垫资调整数据
     * @param $data
     * @return array
     */
    protected function mapPayoutChangeData($data) {
        $response = array();
        if (!empty($data)) {
            $this->newPayout = round(floatval($data['NEW_PAY_OUT']), 2);  // 保存垫资比例
            $data['NEW_PAY_OUT'] = round(floatval($data['NEW_PAY_OUT']), 2) . self::PERCENT_MARK;
            $data['PAID_RATE'] = round(floatval($data['PAID_RATE']), 2) . self::PERCENT_MARK;
            $data['PAYOUT_RATE'] = round(floatval($data['PAYOUT_RATE']), 2) . self::PERCENT_MARK;
            $data['PAID_AMOUNT'] = floatval($data['PAID_AMOUNT']) . self::MONEY_UNIT;
            $data['PAYOUT_COST'] = floatval($data['PAYOUT_COST']) . self::MONEY_UNIT;
            $data['PAYOUT_STATUS'] = empty($data['PAYOUT_STATUS']) ? '' : $this->payoutStatusValues[intval($data['PAYOUT_STATUS'])];
            $response = ARRAY(
                'CITY_NAME' => ARRAY(
                    'ALIAS' => '城市',
                    'INFO' => $data['CITY_NAME']
                ),
                'PROJECTNAME' => ARRAY(
                    'ALIAS' => '项目名称',
                    'INFO' => $data['PROJECTNAME']
                ),
                'YEWU' => ARRAY(
                    'ALIAS' => '业务类型',
                    'INFO' => $data['YEWU']
                ),
                'CONTRACT' => ARRAY(
                    'ALIAS' => '合同编号',
                    'INFO' => $data['CONTRACT']
                ),
                'NEW_PAY_OUT' => ARRAY(
                    'ALIAS' => '申请垫资比例',
                    'INFO' => $data['NEW_PAY_OUT']
                ),
                'PAID_AMOUNT' => ARRAY(
                    'ALIAS' => '已垫资金额',
                    'INFO' => $data['PAID_AMOUNT']
                ),
                'PAID_RATE' => ARRAY(
                    'ALIAS' => '已垫资比例',
                    'INFO' => $data['PAID_RATE']
                ),
                'PAYOUT_COST' => ARRAY(
                    'ALIAS' => '预算付现成本',
                    'INFO' => $data['PAYOUT_COST']
                ),
                'PAYOUT_RATE' => ARRAY(
                    'ALIAS' => '预算付现成本率',
                    'INFO' => $data['PAYOUT_RATE']
                ),
                'REASON' => ARRAY(
                    'ALIAS' => '申请原因',
                    'INFO' => $data['REASON']
                ),
                'APPLY_USER' => ARRAY(
                    'ALIAS' => '申请人',
                    'INFO' => $data['APPLY_USER']
                ),
                'APPLY_DATE' => ARRAY(
                    'ALIAS' => '申请日期',
                    'INFO' => $data['APPLY_DATE']
                ),
                'PAYOUT_STATUS' => ARRAY(
                    'ALIAS' => '状态',
                    'INFO' => $data['PAYOUT_STATUS']
                )
            );
        }

        return $response;
    }

    protected function authMyTurn($flowId) {
        if (intval($flowId) > 0) {
            parent::authMyTurn($flowId);
        } else {
            $this->myTurn = true;
        }
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
            $payout = D('erp_payout')->where("ID = {$this->recordId}")->find();
            $_REQUEST['PAYOUT'] = empty($payout['NEW_PAY_OUT']) ? '' : round(floatval($payout['NEW_PAY_OUT']) * 0.01, 2);  // 垫资比例
            $_REQUEST['CASE_ID'] = empty($payout['CASE_ID']) ? '' : $payout['CASE_ID'];  // 案例编号

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

}