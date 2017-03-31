<?php
class PurchasingBeeAction extends FlowAction{
	public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
			 'application' => array(
                'name' => 'application',
                'text' => '����˵��'
            ),
			'purchase_detail' => array(
                'name' => 'purchase-bee',
                'text' => 'С�۷�ɹ�'
            ),
            'purchase_list' => array(
                'name' => 'purchase-detail',
                'text' => 'С�۷�ɹ�������ϸ'
            ),
             
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
             
        );

        // ��ʼ��������
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('PurchasingBee');
        $this->assign('flowType', 'PurchasingBee');
    } 
  
	 
 
	public function show(){//����չʾ
		$this->flowId = $_REQUEST['flowId']; //1741
		if($this->flowId){ 
			$this->workFlow->nextstep($this->flowId);
			$flow = M('Erp_flows')->where("ID=".$this->flowId)->find();
			$beeId = $flow['RECORDID'];//
		}else{
			$beeId = $_REQUEST['beeId'];
		}
		$list_arr = array(1 => '��', 0 => '��');
		$purchase_model = D('PurchaseRequisition');
		$beemodel = D('PurchaseBeeDetails');
        //״̬
        $status = $beemodel->get_bee_detail_status();
        $purchase_type_arr = $purchase_model->get_conf_purchase_type_remark();
		 
		$sql = "SELECT A.*, to_char(B.END_TIME,'yyyy-mm-dd HH24:MI:SS') as END_TIME, P.PROJECTNAME,U.NAME from ERP_PURCHASE_LIST A LEFT JOIN "
            . " ERP_PURCHASE_REQUISITION B on A.PR_ID = B.ID LEFT JOIN ERP_PROJECT P ON P.ID = B.PRJ_ID left join ERP_USERS  U on A.APPLY_USER_ID=U.ID where A.FEE_ID=58 AND B.STATUS = 2 AND A.TYPE = 1 and A.ID=$beeId"; 
		$flist = M()->query($sql); 
		foreach($flist as $key=> $one){
			$flist[$key]['TYPE'] = $purchase_type_arr[$one['TYPE']];
			$flist[$key]['IS_FUNDPOOL'] = $list_arr[$one['IS_FUNDPOOL']];
			$flist[$key]['IS_KF'] = $list_arr[$one['IS_KF']];
			if($_REQUEST['purchaseIds']){
				$sqld = "select T.*,to_char(EXEC_START,'yyyy-mm-dd') as EXEC_START ,to_char(EXEC_END,'yyyy-mm-dd') as EXEC_END from ERP_PURCHASER_BEE_DETAILS T where ID in(".$_REQUEST['purchaseIds'].") or CSTATUS =1 or STATUS=4" ;
			}else{
				$sqld = "select T.*,to_char(EXEC_START,'yyyy-mm-dd') as EXEC_START ,to_char(EXEC_END,'yyyy-mm-dd') as EXEC_END from ERP_PURCHASER_BEE_DETAILS T where (CSTATUS =1 or STATUS=4) and P_ID=".$one['ID'];
			}
			$temp =  M()->query($sqld);
			foreach($temp  as $keyy=>$onee){
				$temp[$keyy]['STATUS'] = $status[$onee['STATUS']];
			}
			$flist[$key]['detail_list'] = $temp ;
			$projectname = $one['PROJECTNAME'];
		}
		if(!$this->recordId){
			$this->assign('recordId', $_REQUEST['beeId']);
		}
		$this->assign('others', $_REQUEST['beeWork']);
		//$this->menu = $temp;
		$this->assignWorkFlows($this->flowId);
		$this->assign('title', 'С�۷䳬������');
        $this->assign('flist', $flist);
		$this->assign('projectname', $projectname);
		$this->assign('ADDTIME', oracle_date_format($flow['ADDTIME'], 'Y-m-d H:i:s'));
		$this->assign('list_arr', $list_arr);
        $this->assign('menu', $this->menu);  // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
		$this->display('show');
	}
	public function opinionFlow(){//����ҵ����
			
		$prjId = $_REQUEST['prjid'] ?$_REQUEST['prjid'] :$_REQUEST['CASEID'];
 
		//$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
		//$type = $_REQUEST['FORMTYPE'] ? $_REQUEST['FORMTYPE']:17;
		 
		$recordId = $_REQUEST['RECORDID'];
			
		 
        
        //������ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        //����������ҵ��ID
        $recordId = !empty($_REQUEST['RECORDID']) ? 
                intval($_REQUEST['RECORDID']) : 0;
        
       $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );
		if ($this->myTurn) {  // �ǵ�ǰ�������Ǹõ�¼�û�
			$_REQUEST = u2g($_REQUEST);
			//Vendor('Oms.Flows.Flow');
			//$flow = new Flow('finalaccounts');
			$result = $this->workFlow->doit($_REQUEST);
			if (is_array($result)) {
				$response = $result;
			} else {
				$response['result'] = $result;
				$response['status'] = $result>0?1:0;
			}
		} else {
            $response['message'] = '�ǵ�ǰ������';
        }

        echo json_encode(g2u($response));  
	}
	/**
     * ����������
     * @param $profit
     * @param $income
     * @return float|void
     */
    private function getProfitRate($profit, $income) {
        if (empty($income) || $income == 0) {
            return;
        }
        return round($profit * 100 / $income, 2);
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
?>