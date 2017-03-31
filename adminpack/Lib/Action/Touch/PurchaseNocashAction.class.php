<?php

/**
 * �Ǹ���
 * Created by PhpStorm.
 * User: xuke
 * Date: 2016/6/8
 * Time: 10:25
 */
class PurchaseNocashAction extends ExtendAction {
    /**
     * �ɹ������ѯ���
     */
    const PURCHASE_REQUIRE_SQL = <<<SQL
        SELECT A.*,B.PROJECTNAME,B.CONTRACT,F.NAME
        FROM ERP_NONCASHCOST  A
        LEFT JOIN ERP_PROJECT B 
		ON A.PROJECT_ID = B.ID
		LEFT JOIN ERP_FEE F
		ON F.ID = A.FEE_ID
        WHERE A.ID = %d
SQL;

  
    

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
                'text' => ' �Ǹ��ֳɱ�'
            ),
           
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        $this->init();
    }

    /**
     * ��ʼ��������
     */
    private function init() {
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('PurchaseNocash');  // ��Ŀ�²ɹ�����
        $this->assign('flowType', 'PurchaseNocash');
        $this->assign('flowTypeText', '�Ǹ��ֳɱ�����');
    }

    /**
     * ��������
     */
    public function process() {
		if($this->flowId){
            if ($this->myTurn) {
                $this->workFlow->nextstep($this->flowId);  // ���޸�Ŀǰ��״̬
            }
			$purchaseInfo = $this->getPurchaseInfo($this->recordId);
		}else{
			$purchaseInfo = $this->getPurchaseInfo($_REQUEST['noncashcost_id']);
			$this->assign('recordId', $_REQUEST['noncashcost_id']);
		}

		$sql = "select PROJECTNAME from erp_project where contract='{$purchaseInfo['CONTRACT_NO']}'";
        $out_pro =  D()->query($sql);
        $this->assign('outProjectName' , $out_pro[0]);
		$this->assign('purchaseInfo', $purchaseInfo);  // �˵�
        $this->assignWorkFlows($this->flowId);
        $this->assign('title', '�Ǹ��ֳɱ�����');
        $this->assign('menu', $this->menu);  // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
        $this->display('index');
    }

    /**
     * ��ȡ�ɹ�����
     * @param $requireId
     * @param string $caseType
     * @return array
     */
    protected function getPurchaseInfo($requireId, $caseType = '') {
        

        $sql = sprintf(self::PURCHASE_REQUIRE_SQL, $requireId);
		$qResult = D()->query($sql);
		$type_arr = array(1=>'���',2=>'���',3=>'����',4=>'����');
		$status_arr = array('δ�ύ','�����','���ͨ��','���δͨ��','������ȷ��');
        $scaleType_arr = array(1=>'����',2=>'����',3=>'Ӳ��',4=>'�',5=>'��Ʒ',7=>'��Ŀ�',8=>'���ҷ��ճ�');
		
		foreach($qResult as $key=>$one){
			$one['STATUS'] = $one['STATUS'] ? $one['STATUS'] : 0;  
			$qResult[$key]['TYPE'] = $type_arr[$one['TYPE']]; 
			$qResult[$key]['ATTACHMENT'] = $this->getWorkFlowFiles($one['ATTACHMENT']);
			$qResult[$key]['STATUS'] = $status_arr[$one['STATUS']];
            $qResult[$key]['SCALETYPE'] = $scaleType_arr[$one['SCALETYPE']];
		}
 
        return $qResult[0];
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
		if ($this->myTurn) {  // �ǵ�ǰ�������Ǹõ�¼�û�
			$_REQUEST = u2g($_REQUEST);
			// Vendor('Oms.Flows.Flow');
			//$flow = new Flow('PurchaseNocash');
			$result = $this->workFlow->doit($_REQUEST);
			if (is_array($result)) {
				$response = $result;
			} else {
				$response['result']  = $result;
				$response['status'] = $result>0 ? 1:0 ;
			}
		} else {
            $response['message'] = '�ǵ�ǰ������';
        }


        echo json_encode($response);
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