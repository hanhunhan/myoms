<?php
class ProjectSetAction extends ExtendAction{
	 
	public function __construct() {
        parent::__construct();

        // ��ʼ���˵�
        $this->menu = array(
            'application' => array(
                'name' => 'application2',
                'text' => '��Ŀ����'
            ),
            'purchase_detail' => array(
                'name' => 'purchase-detail',
                'text' => '����Ԥ��'
            ),
            'purchase_list' => array(
                'name' => 'purchase-list',
                'text' => 'Ŀ��ֽ�'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '�������'
            )
        );

        // ��ʼ��������
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('projectset');
        $this->assign('flowType', 'projectset');
        $this->flowType = 'projectset'; // todo
    }
 
	public function show(){//����չʾ
		if($_REQUEST['flowId']){
			$this->flowId = $_REQUEST['flowId'];
			$this->workFlow->nextstep($this->flowId);
			$flow = M('Erp_flows')->where("ID=".$this->flowId)->find();
			$project_id = $flow['RECORDID'];//caseid�ֶ��д�ŵ�����Ŀid
		}else $project_id = $_REQUEST['prjid'];
		$isMobile = isMobile();
		$project = M('Erp_house')->where("PROJECT_ID=".$project_id)->find();// var_dump($project);
		if($_REQUEST['showtable'] ){  
			$content = D("House")->get_House_Info_Html($this->flowId,$project_id,'lixiangshenqing',0,$_REQUEST['showtable']);
			$this->assign('content',$content);
			$this->assign('isMobile',$isMobile);
			$this->display('show_talbe');
			exit;
		}
		//$budgetlist = M('Erp_budgetsale')->where("PROJECTT_ID=$project_id")->select(); 
		$budgetlist = M()->query("select A.*,B.NAME from ERP_BUDGETSALE A left join ERP_SALEMETHOD  B on A.SALEMETHODID=B.ID where A.PROJECTT_ID=$project_id");  
		foreach($budgetlist as $one){
			$CUSTOMERS += $one['CUSTOMERS'];
			$SETS += $one['SETS'];
		}
		$this->assign('content',$content);
		$this->assign('isMobile',$isMobile);
		$this->assign('project',$project);
		$this->assign('CUSTOMERS',$CUSTOMERS);
		$this->assign('SETS',$SETS);
		$this->assign('budgetlist',$budgetlist);
		$this->assign('flowId',$this->flowId);
		$this->assign('ADDTIME',oracle_date_format($flow['ADDTIME']));
	 
		if ($_REQUEST['flowId']) {
            if (judgeFlowEdit($_REQUEST['flowId'], $_SESSION['uinfo']['uid'])) {//�ж��Ƿ�ص�������
                $editFlag = 1;//���Ա༭״̬

            }
        }
		
		 
		if(!$this->recordId){
			$this->assign('recordId', $_REQUEST['prjid']);
		}

		$this->assignWorkFlows($this->flowId);
		$this->assign('bizWebEditable', $editFlag);
		$this->assign('title', '��������');
        $this->assign('flist', $flist);
        $this->assign('menu', $this->menu); // �˵�
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // ���ư�ť��ʾ
        $this->assign('showCC', true);
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
        
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );
		if ($this->myTurn) {  // �ǵ�ǰ�������Ǹõ�¼�û�
			$_REQUEST = u2g($_REQUEST);
			Vendor('Oms.Flows.Flow');
			$flow = new Flow('ProjectSet');
			$result = $flow->doit($_REQUEST);
			if (is_array($result)) {
				$response = $result;
			} else {
				$response['status'] = $result;
			}
		} else {
            $response['message'] = '�ǵ�ǰ������';
        }

        echo json_encode(g2u($response)); 
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