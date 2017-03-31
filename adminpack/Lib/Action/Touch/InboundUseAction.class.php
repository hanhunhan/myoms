<?php

/*�û���Ʒ�������ڲ����á������������*/

class InboundUseAction extends ExtendAction
{

    /**
     * �����ѯ���
     */
    const INBOUNDUSE_REQUIRE_SQL = <<<SQL
        SELECT
               A.APPLY_REASON,
               to_char(A.APPLY_TIME,'YYYY-MM-DD hh24:mi:ss') AS APPLY_TIME,
               U.NAME AS USER_NAME,
               A.BUYER
        FROM ERP_DISPLACE_APPLYLIST A
        LEFT JOIN ERP_USERS U ON U.ID = A.APPLY_USER_ID
        WHERE A.ID = %d
SQL;

      const INBOUNDUSE_DETAIL_SQL = <<<INBOUNDUSE_DETAIL_SQL
        SELECT A.ID,L.APPLY_USER_ID,D.NAME AS USERNAME,L.STATUS,L.TYPE,A.LIST_ID,
                I.CONTRACT_NO AS CONTRACT,P.PROJECTNAME,A.AMOUNT,
                RTRIM(to_char(A.MONEY,'fm99999999990.99'),'.') AS MONEY,
                B.CHANGETIME,
                B.BRAND,B.MODEL,B.PRODUCT_NAME,B.SOURCE,
                RTRIM(to_char(B.PRICE,'fm99999999990.99'),'.') AS PRICE,
                (B.NUM + A.AMOUNT) AS NUM
                FROM ERP_DISPLACE_APPLYDETAIL A
                LEFT JOIN ERP_DISPLACE_APPLYLIST L ON A.LIST_ID = L.ID
                LEFT JOIN ERP_DISPLACE_WAREHOUSE B ON A.DID=B.ID
                LEFT JOIN ERP_DISPLACE_REQUISITION R ON R.ID = B.DR_ID
                LEFT JOIN ERP_INCOME_CONTRACT I ON I.ID = R.CONTRACT_ID
                LEFT JOIN ERP_CASE C ON B.CASE_ID=C.ID
                LEFT JOIN ERP_PROJECT P ON C.PROJECT_ID = P.ID
                LEFT JOIN ERP_USERS D ON L.APPLY_USER_ID = D.ID
                WHERE  L.ID = %d
INBOUNDUSE_DETAIL_SQL;

    /**
     * �û�״̬����
     * @var array
     */
    protected $requirementDesc = array(
        0 => 'δ�ύ',
        1 => '�����',
        2 => '���ͨ��',
        3 => '���δͨ��',
        4 => '���'
    );
    /*
     * ���캯��
     */
    public function __construct()
    {
        parent::__construct();

        if (!$this->recordId)
            $this->recordId = intval($_REQUEST['RECORDID']);

        Vendor('Oms.Flows.Flow');
        Vendor('Oms.Flows.InboundUse');
        $this->workFlow = new Flow('InboundUse');
        $this->InboundUse = new InboundUse();

        $this->assign('flowId', $this->flowId);
        $this->assign('recordId', $this->recordId);



        // ��ʼ���˵�
        $this->initWorkFlow();
    }

    /**
     * ��ʼ��������
     */
    private function initWorkFlow() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('InboundUse');
        $flowTypePinYin = trim($_REQUEST['flowTypePinYin']);
        $this->assign('flowType', $flowTypePinYin);
    }


    /**
     * ҳ����Ⱦ
     */
    public function process(){

        $DisplaceMethod = trim($_REQUEST['flowTypePinYin']); //��ȡ����������

        switch($DisplaceMethod){
            case "shoumai":
                $projectName = "�����û���Ʒ����������";
                $title = "�û���Ʒ����";
                $this->menu = array(
                    'application' => array(
                        'name' => 'application',
                        'text' => '����˵��'
                    ),
                    'Inbound_detail' => array(
                        'name' => 'Inbound_detail',
                        'text' => '��������'
                    ),
                    'Inbound_list' => array(
                        'name' => 'Inbound_list',
                        'text' => '������ϸ'
                    ),
                    'opinion' => array(
                        'name' => 'opinion',
                        'text' => '�������'
                    )
                );
                break;
            case "baosun":
                $projectName = "�����û���Ʒ���������";
                $title = "�û���Ʒ����";
                $this->menu = array(
                    'application' => array(
                        'name' => 'application',
                        'text' => '����˵��'
                    ),
                    'Inbound_detail' => array(
                        'name' => 'Inbound_detail',
                        'text' => '��������'
                    ),
                    'Inbound_list' => array(
                        'name' => 'Inbound_list',
                        'text' => '�û���ϸ'
                    ),
                    'opinion' => array(
                        'name' => 'opinion',
                        'text' => '�������'
                    )
                );
                break;
            case "neibulingyong":
                $projectName = "�����û���Ʒ�ڲ����õ�����";
                $title = "�û���Ʒ�ڲ�����";
                $this->menu = array(
                    'application' => array(
                        'name' => 'application',
                        'text' => '����˵��'
                    ),
                    'Inbound_detail' => array(
                        'name' => 'Inbound_detail',
                        'text' => '��������'
                    ),
                    'Inbound_list' => array(
                        'name' => 'Inbound_list',
                        'text' => '������ϸ'
                    ),
                    'opinion' => array(
                        'name' => 'opinion',
                        'text' => '�������'
                    )
                );
                break;
        }

        //ת����һ����״̬��
        if ($this->flowId && $this->myTurn) {
            $this->workFlow->nextstep($this->flowId);
        }
        $this->assignWorkFlows($this->flowId);

        $this->assign("displaceMethod",$DisplaceMethod);
        $this->assign("title",$title);
        $this->assign("projectName",$projectName);

        $inboundUseInfo = $this->getInboundInfo($this->recordId,$DisplaceMethod);
        $this->assigninboundUseInfo($inboundUseInfo,$DisplaceMethod);
        $this->assign('showButtons', $this->availableButtons($this->flowId));
        //�˵�
        $this->assign('menu', $this->menu);
        $this->display('process');
    }

    /**
     * ��ȡʹ��������Ϣ
     * @param $inboundUseInfo
     */
    private function assigninboundUseInfo($inboundUseInfo,$DisplaceMethod) {
        if (is_array($inboundUseInfo) && count($inboundUseInfo)) {
            $require = $this->mapRequirement($inboundUseInfo['desc'],$DisplaceMethod);

            //�ڲ����� + ����  ���������
            if($DisplaceMethod=='neibulingyong' || $DisplaceMethod=='baosun'){
                unset($require['BUYER']);
            }

            $this->assign('require', $require);  //����

            $this->assign('list', $inboundUseInfo['list']);  //��ϸ
            $this->assign('inboundUseInfoListJSON', json_encode(g2u($inboundUseInfo['list'])));
        }
    }

    /**
     * ��ȡ��������������ϸ
     * @param $requireId
     * @return array
     */
    protected function getInboundInfo($requireId,$DisplaceMethod) {
        $inboundUse = array(
            'result' => false,
            'desc' => array(),
            'list' => array()
        );

        try {

            $sql = sprintf(self::INBOUNDUSE_REQUIRE_SQL, $requireId);
            $dbResult = D()->query($sql);
            if (is_array($dbResult) && count($dbResult)) {
                $inboundUse['result'] = true;
                $inboundUse['desc'] = $dbResult[0];
                $inboundUse['list'] = $this->mapInboundUseList($this->getInboundUseDetail($requireId),$DisplaceMethod);
            }
        } catch (Exception $e) {
            $inboundUse['result'] = false;
        }

        //��ȡ�ܽ��
        foreach($inboundUse['list'] as $key=>$val) {
            $inboundUse['desc']['TOTAL_MONEY'] += $val['TOTAL_COST'];
        }

        return $inboundUse;
    }

    /**
     * @param $requireId
     * @return array|mixed
     */
    protected function getInboundUseDetail($requireId) {
        $response = array();
        if (!empty($requireId)) {
            $response = D()->query(sprintf(self::INBOUNDUSE_DETAIL_SQL, $requireId));
        }
        return $response;
    }

    /**
     * ��ȡ�ܼ۸�
     * @param $data
     * @return array
     */
    protected function mapInboundUseList($data,$displaceMethod) {
        $response = array();
        if (notEmptyArray($data)) {
            foreach ($data as $k => $v) {
                $response[$k] = $v;
                if (floatval($v['TOTAL_COST']) <= 0) {
                    switch($displaceMethod){
                        case 'shoumai':
                            $response[$k]['TOTAL_COST'] = floatval($v['AMOUNT']) * floatval($v['MONEY']);
                            break;
                        case 'neibulingyong':
                            $response[$k]['TOTAL_COST'] = floatval($v['PRICE']) * floatval($v['AMOUNT']);
                            break;
                        case 'baosun':
                            $response[$k]['TOTAL_COST'] = floatval($v['PRICE']) * floatval($v['NUM']);
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        return $response;
    }

    /**
     * ӳ��ʹ�÷���������
     * @param $response
     * @return mixed
     */
    protected function mapRequirement($response,$DisplaceMethod) {
        switch($DisplaceMethod) {
            //����
            case "shoumai":
                $response['USER_NAME'] = array(
                    'alias' => '������',
                    'val' => $response['USER_NAME']
                );
                $response['APPLY_REASON'] = array(
                    'alias' => '����ԭ��',
                    'val' => $response['APPLY_REASON']
                );
                $response['BUYER'] = array(
                    'alias' => '���',
                    'val' => $response['BUYER'],
                );
                $response['APPLY_TIME'] = array(
                    'alias' => '����ʱ��',
                    'val' => $response['APPLY_TIME']
                );
                $response['TOTAL_MONEY'] = array(
                    'alias' => '�����ܽ��',
                    'val' => $response['TOTAL_MONEY']
                );
                break;
            //�ڲ�����
            case "neibulingyong":
                $response['USER_NAME'] = array(
                    'alias' => '������',
                    'val' => $response['USER_NAME'],
                );
                $response['APPLY_REASON'] = array(
                    'alias' => '����ԭ��',
                    'val' => $response['APPLY_REASON']
                );
                $response['APPLY_TIME'] = array(
                    'alias' => '����ʱ��',
                    'val' => $response['APPLY_TIME']
                );
                $response['TOTAL_MONEY'] = array(
                    'alias' => '�����ܽ��',
                    'val' => $response['TOTAL_MONEY']
                );
                break;
            //����
            case "baosun":
                $response['USER_NAME'] = array(
                    'alias' => '������',
                    'val' => $response['USER_NAME']
                );
                $response['APPLY_TIME'] = array(
                    'alias' => '����ʱ��',
                    'val' => $response['APPLY_TIME']
                );
                $response['TOTAL_MONEY'] = array(
                    'alias' => '�����ܼ�ֵ',
                    'val' => $response['TOTAL_MONEY']
                );
                $response['APPLY_REASON'] = array(
                    'alias' => '����˵��',
                    'val' => $response['APPLY_REASON']
                );
        }
        return $response;
    }

    /**
     * ��������������
     */
    public function opinionFlow(){
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );

        //���������� 1������ 2���ڲ����� 3������  4���������
        $flowDisplaceType = isset($_REQUEST['flowDisplaceType'])?intval($_REQUEST['flowDisplaceType']):0;

        if ($this->myTurn) {
            $data = u2g($_REQUEST);
            $data['flowDisplaceType'] = $flowDisplaceType; //����type����
            Vendor('Oms.Flows.Flow');
            $this->workFlow = new Flow('InboundUse');
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

        $response['url'] = U('Flow/flowList', 'status=1');
        echo json_encode(g2u($response));
    }

    /**
     * Ȩ���ж�
     * @param $flowId
     */
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