<?php
/**
 * ���ʱ�������������
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
        1 => "δ����",
        2 => "�����룬�����",
        3 => "���ͨ��",
        4 => "���δͨ��",
    );

    /**
     * �µĵ��ʱ���
     * @var
     */
    protected $newPayout;


    public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'payout_change_detail' => array(
                'name' => 'payout-change-detail',
                'text' => '���ʱ���������ϸ'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->init();
    }

    /**
     * ��ʼ������
     */
    private function init() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('PayoutChange');
        $this->assign('flowType', 'dianziedu');
        $this->assign('flowTypeText', '���ʱ�������');
    }

    /**
     * ����
     */
    public function process() {
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // ���޸�Ŀǰ��״̬
        } else {
            $this->recordId = $_REQUEST['RECORDID'];

        }
        $payout = $this->getPayoutChangeInfo();

        if (is_array($payout) && count($payout)) {
            $this->assign('projectName', '���ڵ��ʱ���������������');  // ��Ŀ����
            $this->assign('payout', $payout);  // �ɹ���Ϣ
        }
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', '���ʱ�������');
        $this->assign('menu', $this->menu);  // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
        $this->assign('CASEID', $this->CASEID);
        $this->assign('recordId', $this->recordId);
        $this->display('PayoutChange:process');
    }

    /**
     * ��ȡ���ʵ�����Ϣ
     * @return array
     */
    public function getPayoutChangeInfo() {
        $response = array();
        if ($this->recordId) {
            $result = D()->query(sprintf(self::PAYOUT_INFO_SQL, $this->recordId));
            if (is_array($result) && count($result)) {
                $response = $result[0];
                $response['PAID_AMOUNT'] = '';  // �ѵ��ʽ��
                $response['PAID_RATE'] = '';  // �ѵ��ʱ���
                $response['PAYOUT_COST'] = '';  // ���ֳɱ�
                $response['PAYOUT_RATE'] = '';  // ���ֳɱ���

                if ($response['CASE_ID']) {
                    $this->CASEID = $response['CASE_ID'];  // ���°������
                    // �ѵ���
                    $response['PAID_AMOUNT'] = D('ProjectCase')->getLoanMoney($response['CASE_ID'], 0, 1); // ���
                    $response['PAID_RATE'] = D('ProjectCase')->getLoanMoney($response['CASE_ID'],0, 2);  // ����

                    // ����Ԥ�㸶��
                    $payoutCost = null;  // �ɱ�
                    $payoutRate = null;  // �ɱ���
                    D('ProjectCase')->getPreCostPreRate($response['CASE_ID'], $payoutCost, $payoutRate);
                    $response['PAYOUT_COST'] = $payoutCost;  // ���ֳɱ�
                    $response['PAYOUT_RATE'] = $payoutRate;  // ���ֳɱ���
                }
                $response = $this->mapPayoutChangeData($response);
            }

        }

        return $response;
    }

    /**
     * ӳ����ʵ�������
     * @param $data
     * @return array
     */
    protected function mapPayoutChangeData($data) {
        $response = array();
        if (!empty($data)) {
            $this->newPayout = round(floatval($data['NEW_PAY_OUT']), 2);  // ������ʱ���
            $data['NEW_PAY_OUT'] = round(floatval($data['NEW_PAY_OUT']), 2) . self::PERCENT_MARK;
            $data['PAID_RATE'] = round(floatval($data['PAID_RATE']), 2) . self::PERCENT_MARK;
            $data['PAYOUT_RATE'] = round(floatval($data['PAYOUT_RATE']), 2) . self::PERCENT_MARK;
            $data['PAID_AMOUNT'] = floatval($data['PAID_AMOUNT']) . self::MONEY_UNIT;
            $data['PAYOUT_COST'] = floatval($data['PAYOUT_COST']) . self::MONEY_UNIT;
            $data['PAYOUT_STATUS'] = empty($data['PAYOUT_STATUS']) ? '' : $this->payoutStatusValues[intval($data['PAYOUT_STATUS'])];
            $response = ARRAY(
                'CITY_NAME' => ARRAY(
                    'ALIAS' => '����',
                    'INFO' => $data['CITY_NAME']
                ),
                'PROJECTNAME' => ARRAY(
                    'ALIAS' => '��Ŀ����',
                    'INFO' => $data['PROJECTNAME']
                ),
                'YEWU' => ARRAY(
                    'ALIAS' => 'ҵ������',
                    'INFO' => $data['YEWU']
                ),
                'CONTRACT' => ARRAY(
                    'ALIAS' => '��ͬ���',
                    'INFO' => $data['CONTRACT']
                ),
                'NEW_PAY_OUT' => ARRAY(
                    'ALIAS' => '������ʱ���',
                    'INFO' => $data['NEW_PAY_OUT']
                ),
                'PAID_AMOUNT' => ARRAY(
                    'ALIAS' => '�ѵ��ʽ��',
                    'INFO' => $data['PAID_AMOUNT']
                ),
                'PAID_RATE' => ARRAY(
                    'ALIAS' => '�ѵ��ʱ���',
                    'INFO' => $data['PAID_RATE']
                ),
                'PAYOUT_COST' => ARRAY(
                    'ALIAS' => 'Ԥ�㸶�ֳɱ�',
                    'INFO' => $data['PAYOUT_COST']
                ),
                'PAYOUT_RATE' => ARRAY(
                    'ALIAS' => 'Ԥ�㸶�ֳɱ���',
                    'INFO' => $data['PAYOUT_RATE']
                ),
                'REASON' => ARRAY(
                    'ALIAS' => '����ԭ��',
                    'INFO' => $data['REASON']
                ),
                'APPLY_USER' => ARRAY(
                    'ALIAS' => '������',
                    'INFO' => $data['APPLY_USER']
                ),
                'APPLY_DATE' => ARRAY(
                    'ALIAS' => '��������',
                    'INFO' => $data['APPLY_DATE']
                ),
                'PAYOUT_STATUS' => ARRAY(
                    'ALIAS' => '״̬',
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
            $_REQUEST['PAYOUT'] = empty($payout['NEW_PAY_OUT']) ? '' : round(floatval($payout['NEW_PAY_OUT']) * 0.01, 2);  // ���ʱ���
            $_REQUEST['CASE_ID'] = empty($payout['CASE_ID']) ? '' : $payout['CASE_ID'];  // �������

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

}