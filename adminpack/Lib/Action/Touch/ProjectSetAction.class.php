<?php
class ProjectSetAction extends ExtendAction{
	 
	public function __construct() {
        parent::__construct();

        // 初始化菜单
        $this->menu = array(
            'application' => array(
                'name' => 'application2',
                'text' => '项目详情'
            ),
            'purchase_detail' => array(
                'name' => 'purchase-detail',
                'text' => '立项预算'
            ),
            'purchase_list' => array(
                'name' => 'purchase-list',
                'text' => '目标分解'
            ),
            'opinion' => array(
                'name' => 'opinion',
                'text' => '审批意见'
            )
        );

        // 初始化工作流
        Vendor('Oms.Flows.Flow');
        $this->workFlow = new Flow('projectset');
        $this->assign('flowType', 'projectset');
        $this->flowType = 'projectset'; // todo
    }
 
	public function show(){//数据展示
		if($_REQUEST['flowId']){
			$this->flowId = $_REQUEST['flowId'];
			$this->workFlow->nextstep($this->flowId);
			$flow = M('Erp_flows')->where("ID=".$this->flowId)->find();
			$project_id = $flow['RECORDID'];//caseid字段中存放的是项目id
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
            if (judgeFlowEdit($_REQUEST['flowId'], $_SESSION['uinfo']['uid'])) {//判断是否回到发起人
                $editFlag = 1;//可以编辑状态

            }
        }
		
		 
		if(!$this->recordId){
			$this->assign('recordId', $_REQUEST['prjid']);
		}

		$this->assignWorkFlows($this->flowId);
		$this->assign('bizWebEditable', $editFlag);
		$this->assign('title', '立项申请');
        $this->assign('flist', $flist);
        $this->assign('menu', $this->menu); // 菜单
        $this->assign('showButtons', $this->availableButtons($this->flowId));  // 控制按钮显示
        $this->assign('showCC', true);
		$this->display('show');
	}
	public function opinionFlow(){//流程业务处理
			
		$prjId = $_REQUEST['prjid'] ?$_REQUEST['prjid'] :$_REQUEST['CASEID'];
 
		//$paramUrl = '&prjid='.$prjId.'&CHANGE='.$this->_get('CHANGE');
		//$type = $_REQUEST['FORMTYPE'] ? $_REQUEST['FORMTYPE']:17;
		 
		$recordId = $_REQUEST['RECORDID'];
			
		 
        
        //工作流ID
        $flowId = !empty($_REQUEST['flowId']) ? 
                intval($_REQUEST['flowId']) : 0;
        
        $response = array(
            'status' => false,
            'message' => '',
            'data' => '',
            'url' => U('Flow/flowList', 'status=1')
        );
		if ($this->myTurn) {  // 非当前审批人是该登录用户
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
            $response['message'] = '非当前审批人';
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