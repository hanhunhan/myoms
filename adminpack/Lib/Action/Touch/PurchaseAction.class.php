<?php

/**
 * �ɹ�������������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/8
 * Time: 10:25
 */
class PurchaseAction extends ExtendAction {
    /**
     * �ɹ������ѯ���
     */
    const PURCHASE_REQUIRE_SQL = <<<SQL
        SELECT A.ID,
               A.PRJ_ID,
               A.USER_ID,
               A.REASON,
               A.TYPE,
               A.CASE_ID,
               P.PROJECTNAME,
               P.CONTRACT AS CONTRACT_NUM,
               to_char(A.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
               to_char(A.END_TIME,'YYYY-MM-DD hh24:mi:ss') AS END_TIME,
               A.STATUS,
               U.NAME AS USER_NAME,
               C.SCALETYPE
        FROM ERP_PURCHASE_REQUISITION A
        LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
        LEFT JOIN ERP_PROJECT P ON P.ID = A.PRJ_ID
        LEFT JOIN ERP_USERS U ON U.ID = A.USER_ID
        WHERE A.ID = %d
SQL;

    /**
     * Ӳ��ɹ������ѯ���
     */
    const YG_PURCHASE_REQUIRE_SQL = <<<SQL
        SELECT A.ID,
               A.PRJ_ID,
               A.USER_ID,
               A.REASON,
               A.TYPE,
               A.CASE_ID,
               P.PROJECTNAME,
               B.CONTRACT_NO AS CONTRACT_NUM
               to_char(A.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
               to_char(A.END_TIME,'YYYY-MM-DD hh24:mi:ss') AS END_TIME,
               A.STATUS,
               U.NAME AS USER_NAME,
               C.SCALETYPE
        FROM ERP_PURCHASE_REQUISITION A
        LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
        LEFT JOIN ERP_INCOME_CONTRACT B ON A.CASE_ID = B.CASE_ID
        LEFT JOIN ERP_PROJECT P ON P.ID = A.PRJ_ID
        LEFT JOIN ERP_USERS U ON U.ID = A.USER_ID
        WHERE A.ID = %D
SQL;

    /**
     * �ɹ���ϸ��ѯ���
     */
    const PURCHASE_LIST_SQL = <<<PURCHASE_LIST_SQL
        SELECT A.ID,
               A.PR_ID,
               A.BRAND,
               A.MODEL,
               A.PRODUCT_NAME,
               A.P_ID,
               U1.NAME AS P_NAME,
               A.APPLY_USER_ID,
               U2.NAME AS APPLY_USER_NAME,
               A.USE_NUM,
               A.USE_TOATL_PRICE,
               A.PRICE_LIMIT,
               A.PRICE,
               A.NUM_LIMIT,
               A.NUM,
               A.PURCHASE_COST,
               A.TOTAL_COST,
               A.FEE_ID,
               F.NAME AS FEE_NAME,
               A.STOCK_NUM,
               A.BACK_STOCK_STATUS,
               A.IS_FUNDPOOL,
               A.IS_KF,
               A.STATUS
        FROM
          (SELECT L.*,
                  R.END_TIME,
                  (PRICE * NUM) AS PURCHASE_COST,
                  ((PRICE * NUM) + USE_TOATL_PRICE) AS TOTAL_COST
           FROM ERP_PURCHASE_LIST L
           LEFT JOIN ERP_PURCHASE_REQUISITION R ON L.PR_ID = R.ID
           WHERE L.FEE_ID!=15) A
        LEFT JOIN ERP_USERS U1 ON U1.ID = A.P_ID
        LEFT JOIN ERP_USERS U2 ON U2.ID = A.APPLY_USER_ID
        LEFT JOIN ERP_FEE F ON F.ID = A.FEE_ID
        WHERE A.PR_ID = %d
        ORDER BY A.ID DESC
PURCHASE_LIST_SQL;

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

    public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'purchase_detail' => array(
                'name' => 'purchase-detail',
                'text' => '�ɹ�����'
            ),
            'purchase_list' => array(
                'name' => 'purchase-list',
                'text' => '�ɹ���ϸ'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );
    }

    /**
     * ��ʼ��������
     */
    private function initWorkFlow($req) {
        Vendor('Oms.Flows.Flow');
        if (intval($req['TYPE']) == 2) {  // ���ڲɹ�����
            $this->workFlow = new Flow('BulkPurchase');
            $this->assign('flowType', 'bulkPurchase');
            $this->assign('flowTypeText', '���ڲɹ�����');
        } else {
            $this->workFlow = new Flow('Purchase');  // ��Ŀ�²ɹ�����
            $this->assign('flowType', 'caigoushenqing');
            $this->assign('flowTypeText', '�ɹ�����');
        }


    }

    /**
     * ��������
     */
    public function process() {
        if (empty($this->flowId)) {
            $this->recordId = $_REQUEST['RECORDID'];
        }
        $purchaseInfo = $this->getPurchaseInfo($this->recordId);
        if (intval($this->flowId) > 0 && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // ��ǰ�������Ѿ����Ĺ��������޸�״̬
        }
        $this->assignPurchaseInfo($purchaseInfo);  // ���ɹ���Ϣ������ͼ
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', '�ɹ�����');
        $this->assign('menu', $this->menu);  // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
        $this->assign('projectName', '���ڲɹ����������'); // ����������
        $this->assign('CASEID', $purchaseInfo['desc']['CASE_ID']);  // �������
        $this->assign('SCALETYPE', $purchaseInfo['desc']['SCALETYPE']);  // ҵ������
        $this->assign('recordId', $this->recordId); // can't comment
        $this->display('index');
    }

    public function detail() {
        $this->display('detail');
    }

    /**
     * ��ת�ɹ�����
     * @param $data
     * @return array
     */
    protected function verticalPurchaseList($data) {
        $purchaseNames = array(
            'BRAND' => 'Ʒ��',
            'MODEL' => '�ɹ��ͺ�',
            'PRODUCT_NAME' => 'Ʒ��',
            'P_NAME' => '�ɹ�������',
            'APPLY_USER_NAME' => 'ָ���ɹ���',
            'USE_NUM' => '���ÿ������',
            'USE_TOATL_PRICE' => '���óɱ�',
            'PRICE_LIMIT' => '��������޼�',
            'PRICE' => '�ɽ���',
            'NUM_LIMIT' => '��������',
            'NUM' => '��������',
            'PURCHASE_COST' => '�ɹ��ɱ�',
            'TOTAL_COST' => '�ϼƽ��',
            'FEE_NAME' => '��������',
            'STOCK_NUM' => '�˿�����',
            'BACK_STOCK_STATUS' => '�˿�״̬',
            'IS_FUNDPOOL' => '�Ƿ��ʽ�ط���',
            'IS_KF' => '�Ƿ�۷�'
        );

        $backStatusValues = array(
            0 => 'δ����',
            1 => '������',
            2 => '������'
        );

        $yesOrNo = array(
            0 => '��',
            1 => '��'
        );

        $rows = array();
        foreach ($data as $k => $v) {
            $index = 0;
            foreach ($v as $k1 => $v1) {
                if (in_array($k1, array_keys($purchaseNames))) {
                    if (empty($rows[$k1][0])) {
                        $rows[$index][0] = $purchaseNames[$k1];
                    }
                    if ($k1 == 'BACK_STOCK_STATUS') {
                        $v1 = $backStatusValues[$v1];
                    } else if (in_array($k1, array('IS_FUNDPOOL', 'IS_KF'))) {
                        $v1 = $yesOrNo[$v1];
                    }
                    $rows[$index] [$k + 1]= $v1;
                    $index++;
                }
            }
        }

        return $rows;
    }

    /**
     * ��ȡ�ɹ�����
     * @param $requireId
     * @param string $caseType
     * @return array
     */
    protected function getPurchaseInfo($requireId, $caseType = '') {
        $purchase = array(
            'result' => false,
            'desc' => array(),
            'list' => array()
        );

        try {
            if ($caseType == 'yg') {
                $sql = sprintf(self::YG_PURCHASE_REQUIRE_SQL, $requireId);
            } else {
                $sql = sprintf(self::PURCHASE_REQUIRE_SQL, $requireId);
            }
            $dbResult = D()->query($sql);
            if (is_array($dbResult) && count($dbResult)) {
                $purchase['result'] = true;
                $purchase['desc'] = $dbResult[0];
                $purchase['list'] = $this->mapPurchaseList($this->getPurchaseList($requireId));
                $this->initWorkFlow($dbResult[0]);
            }
        } catch (Exception $e) {
            $purchase['result'] = false;
        }

        return $purchase;
    }

    /**
     * ӳ��ɹ�����
     * @param $data
     * @return array
     */
    protected function mapPurchaseList($data) {
        $response = array();
        if (notEmptyArray($data)) {
            foreach ($data as $k => $v) {
                $response[$k] = $v;
                if (floatval($v['TOTAL_COST']) <= 0) {
                    $response[$k]['TOTAL_COST'] = floatval($v['PRICE_LIMIT']) * floatval($v['NUM_LIMIT']);
                }
            }
        }
        return $response;
    }

    /**
     * ��ȡ�ɹ��б�
     * @param $requireId
     * @return array
     */
    protected function getPurchaseList($requireId) {
        $response = array();
        if (!empty($requireId)) {
            $response = D()->query(sprintf(self::PURCHASE_LIST_SQL, $requireId));
        }

        return $response;
    }

    /**
     * ��ʽ���ɹ�����
     * @param $data
     * @return array
     */
    protected function mapRequirement($data) {
        empty($data['PROJECTNAME']) or $response['PROJECTNAME'] = array(
            'alias' => '��Ŀ����',
            'val' => $data['PROJECTNAME']
        );
        empty($data['CONTRACT_NUM']) or $response['CONTRACT_NUM'] = array(
            'alias' => '��ͬ���',
            'val' => $data['CONTRACT_NUM']
        );
        empty($data['USER_NAME']) or $response['USER_NAME'] = array(
            'alias' => '������',
            'val' => $data['USER_NAME']
        );
        empty($data['REASON']) or $response['REASON'] = array(
            'alias' => '�ɹ�ԭ��',
            'val' => $data['REASON']
        );
        empty($data['APPLY_TIME']) or $response['APPLY_TIME'] = array(
            'alias' => '����ʱ��',
            'val' => $data['APPLY_TIME']
        );
        empty($data['END_TIME']) or $response['END_TIME'] = array(
            'alias' => '�����ʹ�ʱ��',
            'val' => $data['END_TIME']
        );
        ($data['STATUS'] === null) or $response['STATUS'] = array(
            'alias' => '״̬',
            'val' => $this->requirementDesc[$data['STATUS']]
        );

        return $response;
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

        if ($this->myTurn) {
            $data = u2g($_REQUEST);
            Vendor('Oms.Flows.Flow');
            $this->workFlow = new Flow('Purchase');  // ��Ŀ�²ɹ�����
            $result = $this->workFlow->doit($data);
            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        } else {
            $response['status'] = false;
            $response['message'] = '�ǵ�ǰ������';
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }

        echo json_encode(g2u($response));
    }

    /**
     * ���ڲɹ����봦������
     */
    public function bulk_purchase_opinionFlow() {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        // todo ���� haspermission����
        if ($this->myTurn) {
            $data = u2g($_REQUEST);
            $data['type'] = 'caigoushenqing';
            Vendor('Oms.Flows.Flow');
            $this->workFlow = new Flow('BulkPurchase');  // ���ڲɹ�����
            $result = $this->workFlow->doit($data);
            if (is_array($result)) {
                $response = $result;
            } else {
                $response['status'] = $result;
            }
        }

        if (empty($response['url'])) {
            $response['url'] = U('Flow/flowList', 'status=1');
        }
        echo json_encode(g2u($response));
    }

    /**
     * ���ɹ���Ϣ��������
     * @param $purchaseInfo
     */
    private function assignPurchaseInfo($purchaseInfo) {
        if (is_array($purchaseInfo) && count($purchaseInfo)) {
            $require = $this->mapRequirement($purchaseInfo['desc']);
            if ($_REQUEST['purchaseType'] == 'bulkPurchase') {
                unset($require['PROJECTNAME']);
                unset($require['END_TIME']);
            }
            $this->assign('require', $require);  // ��������
            $this->assign('list', $purchaseInfo['list']);  // �ɹ���Ϣ
            $this->assign('purchaseListJSON', json_encode(g2u($purchaseInfo['list'])));
        }
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
}