<?php

/**
 * �û����빤����������
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/8
 * Time: 10:25
 */
class DisplaceAction extends ExtendAction {
    /**
     * �û������ѯ���
     */
    const DISPLACE_REQUIRE_SQL = <<<SQL
        SELECT A.ID,
               A.PRJ_ID,
               A.USER_ID,
               A.REASON,
               A.CASE_ID,
               P.PROJECTNAME,
               to_char(A.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
               to_char(A.END_TIME,'YYYY-MM-DD hh24:mi:ss') AS END_TIME,
               A.STATUS,
               U.NAME AS USER_NAME,
               C.SCALETYPE,
               IC.CONTRACT_NO,
               H.TOTAL_MONEY
        FROM ERP_DISPLACE_REQUISITION A
        LEFT JOIN ERP_CASE C ON C.ID = A.CASE_ID
        LEFT JOIN ERP_PROJECT P ON P.ID = A.PRJ_ID
        LEFT JOIN ERP_USERS U ON U.ID = A.USER_ID
        LEFT JOIN ERP_INCOME_CONTRACT IC  ON A.CONTRACT_ID = IC.ID
        LEFT JOIN (SELECT DECODE(SUBSTR(SUM(NUM * PRICE),1,1),'.','0'||SUM(NUM * PRICE),SUM(NUM * PRICE)) AS TOTAL_MONEY,DR_ID FROM ERP_DISPLACE_WAREHOUSE GROUP BY DR_ID) H ON A.ID = H.DR_ID
        WHERE A.ID = %d
SQL;

    /**
     * �û���ϸ��ѯ���
     */
    const DISPLACE_DETAIL_SQL = <<<DISPLACE_DETAIL_SQL
        SELECT A.ID,
               A.DR_ID,
               A.BRAND,
               A.MODEL,
               A.PRODUCT_NAME,
               U.NAME AS USER_NAME,
               A.NUM,
               DECODE(SUBSTR(A.PRICE,1,1),'.','0'||A.PRICE,A.PRICE) AS PRICE,
               A.STATUS,
               A.LIVETIME,
               A.ALARMTIME,
               A.SOURCE,
               A.ADD_TIME
        FROM ERP_DISPLACE_WAREHOUSE A
        LEFT JOIN ERP_USERS U ON U.ID = A.ADD_USERID
        WHERE A.DR_ID = %d
        ORDER BY A.ID DESC
DISPLACE_DETAIL_SQL;

    /**
     * �û�����״̬����
     * @var array
     */
    protected $requirementDesc = array(
        0 => 'δ�ύ',
        1 => '�����',
        2 => '���ͨ��',
        3 => '���δͨ��',
        4 => '�û����'
    );

    public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
            'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
            'displace_detail' => array(
                'name' => 'displace-detail',
                'text' => '�û�����'
            ),
            'displace_list' => array(
                'name' => 'displace-list',
                'text' => '�û���ϸ'
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
    private function initWorkFlow() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('Displace');  // ��Ŀ���û�����
        $this->assign('flowType', 'zhihuanshenqing');
        $this->assign('flowTypeText', '�û�����');
    }

    /**
     * ��������
     */
    public function process() {
        if (empty($this->flowId)) {
            $this->recordId = $_REQUEST['RECORDID'];
        }
        //��ȡ�û�����
        $displaceMainInfo = $this->getDisplaceInfo($this->recordId);

        if (intval($this->flowId) > 0 && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);  // ��ǰ�������Ѿ����Ĺ��������޸�״̬
        }
        $this->assigndisplaceInfo($displaceMainInfo);  // ���û���Ϣ������ͼ
        $this->assignWorkFlows($this->flowId);

        //ҵ����������
        $caseTypeArr = D('ProjectCase')->get_conf_case_type();
        $caseTypePinYinArr = array_flip($caseTypeArr);
        $caseTypePinYin = $caseTypePinYinArr[$displaceMainInfo['desc']['SCALETYPE']]; //��ȡƴд

        //���ݱ༭����
        $tabNumber = $caseTypePinYin=='yg'?12:13;
        $this->assign('tabNumber', $tabNumber);  // tabnumber
        $this->assign('caseTypePinYin', $caseTypePinYin);  // caseTypePinYin

        $this->assign('title', '�û�����');
        $this->assign('menu', $this->menu);  // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
        $this->assign('projectName', '�����û����������'); // ����������
        $this->assign('CASEID', $displaceMainInfo['desc']['CASE_ID']);  // �������
        $this->assign('prjid', $displaceMainInfo['desc']['PRJ_ID']);  // �������
        $this->assign('SCALETYPE', $displaceMainInfo['desc']['SCALETYPE']);  // ҵ������
        $this->assign('recordId', $this->recordId); // can't comment
        $this->display('index');
    }

    public function detail() {
        $this->display('detail');
    }

    /**
     * ��ת�û�����
     * @param $data
     * @return array
     */
    protected function verticaldisplaceList($data) {
        $displaceNames = array(
            'BRAND' => 'Ʒ��',
            'MODEL' => '�ͺ�',
            'PRODUCT_NAME' => 'Ʒ��',
            'USER_NAME' => '�û�����',
            'PRICE' => '�û���',
            'SOURCE' => '��Դ',
            'LIVETIME' => '��Ч��',
            'ALARMTIME' => '��Ч�ڱ���ʱ��',
            'ADD_USERID' => '�����',
            'ADD_TIME' => '���ʱ��',
            'TOTAL_COST' => '�ϼƽ��',
        );

        $rows = array();
        foreach ($data as $k => $v) {
            $index = 0;
            foreach ($v as $k1 => $v1) {
                if (in_array($k1, array_keys($displaceNames))) {
                    if (empty($rows[$k1][0])) {
                        $rows[$index][0] = $displaceNames[$k1];
                    }
                    $rows[$index] [$k + 1]= $v1;
                    $index++;
                }
            }
        }

        return $rows;
    }

    /**
     * ��ȡ�û�����
     * @param $requireId
     * @param string $caseType
     * @return array
     */
    protected function getDisplaceInfo($requireId) {
        $displace = array(
            'result' => false,
            'desc' => array(),
            'list' => array()
        );

        try {
            $sql = sprintf(self::DISPLACE_REQUIRE_SQL, $requireId);

            $dbResult = D()->query($sql);
            if (is_array($dbResult) && count($dbResult)) {
                $displace['result'] = true;
                $displace['desc'] = $dbResult[0];
                $displace['list'] = $this->mapDisplaceList($this->getDisplaceDetail($requireId));
                $this->initWorkFlow($dbResult[0]);
            }
        } catch (Exception $e) {
            $displace['result'] = false;
        }

        return $displace;
    }

    /**
     * ӳ���û�����
     * @param $data
     * @return array
     */
    protected function mapDisplaceList($data) {
        $response = array();
        if (notEmptyArray($data)) {
            foreach ($data as $k => $v) {
                $response[$k] = $v;
                if (floatval($v['TOTAL_COST']) <= 0) {
                    $response[$k]['TOTAL_COST'] = floatval($v['PRICE']) * floatval($v['NUM']);
                }
            }
        }
        return $response;
    }

    /**
     * ��ȡ�û��б�
     * @param $requireId
     * @return array
     */
    protected function getDisplaceDetail($requireId) {
        $response = array();
        if (!empty($requireId)) {
            $response = D()->query(sprintf(self::DISPLACE_DETAIL_SQL, $requireId));
        }
        return $response;
    }

    /**
     * ��ʽ���û�����
     * @param $data
     * @return array
     */
    protected function mapRequirement($data) {
        empty($data['PROJECTNAME']) or $response['PROJECTNAME'] = array(
            'alias' => '��Ŀ����',
            'val' => $data['PROJECTNAME']
        );
        empty($data['CONTRACT_NO']) or $response['CONTRACT_NO'] = array(
            'alias' => '��ͬ���',
            'val' => $data['CONTRACT_NO']
        );
        empty($data['USER_NAME']) or $response['USER_NAME'] = array(
            'alias' => '������',
            'val' => $data['USER_NAME']
        );
        empty($data['REASON']) or $response['REASON'] = array(
            'alias' => '�û�ԭ��',
            'val' => $data['REASON']
        );
        empty($data['APPLY_TIME']) or $response['APPLY_TIME'] = array(
            'alias' => '���ʱ��',
            'val' => $data['APPLY_TIME']
        );
        empty($data['END_TIME']) or $response['END_TIME'] = array(
            'alias' => '�����ʹ�ʱ��',
            'val' => $data['END_TIME']
        );
        empty($data['TOTAL_MONEY']) or $response['TOTAL_MONEY'] = array(
            'alias' => '�ϼ��û����',
            'val' => $data['TOTAL_MONEY'] . 'Ԫ'
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
            $this->workFlow = new Flow('Displace');  // ��Ŀ���û�����
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
     * ���û���Ϣ��������
     * @param $displaceInfo
     */
    private function assigndisplaceInfo($displaceInfo) {
        if (is_array($displaceInfo) && count($displaceInfo)) {
            $require = $this->mapRequirement($displaceInfo['desc']);
            if ($_REQUEST['displaceType'] == 'bulkdisplace') {
                unset($require['PROJECTNAME']);
                unset($require['END_TIME']);
            }
            $this->assign('require', $require);  // ��������
            $this->assign('list', $displaceInfo['list']);  // �û���Ϣ
            $this->assign('displaceListJSON', json_encode(g2u($displaceInfo['list'])));
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